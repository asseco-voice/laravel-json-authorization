<?php

namespace Voice\JsonAuthorization;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Voice\JsonQueryBuilder\Exceptions\SearchException;
use Voice\JsonQueryBuilder\JsonQuery;

class JsonAuthorizationServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/asseco-authorization.php', 'asseco-authorization');
        $this->loadMigrationsFrom(__DIR__ . '/Database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');

        $this->app->singleton(RightParser::class, function ($app) {
            return new RightParser();
        });
    }

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/Config/asseco-authorization.php' => config_path('asseco-authorization.php'),]);

        /**
         * @var $rightParser RightParser
         */
        $rightParser = App::make(RightParser::class);

        Event::listen($rightParser->eventsToListen, function ($event, $model) use ($rightParser) {

            $eloquentModel = $this->getModel($model);
            $modelClass = get_class($eloquentModel);

            if (!$rightParser->isModelAuthorizable($modelClass)) {
                // Given model is not authorizable, therefore you are allowed every action on it
                return true;
            }

            Log::info("[JSONAuth] Triggered Eloquent event: $event");

            $eventName = $this->parseEventName($event);

            $rightParser->checkEventMapping($eventName);

            $input = $rightParser->getAuthValues($modelClass, $rightParser->eventRightMapping[$eventName]);

            if (count($input) < 1) {
                Log::info("[JSONAuth] You have no rights for this action.");
                return false;
            }

            if (array_key_exists(0, $input) && $input[0] === '*') {
                Log::info("[JSONAuth] You have full rights for this action.");
                return true;
            }

            $fetched = $this->executeQuery($modelClass, $input, $eloquentModel);

            // Compare primary keys only ... what if there are none?
            return in_array($eloquentModel->getKey(), $fetched);
        });
    }

    /**
     * @param $event
     * @return mixed|string
     * @throws Exception
     */
    protected function parseEventName($event): string
    {
        $parsed = explode(':', $event);

        if (count($parsed) != 2) {
            throw new Exception();
        }

        return $parsed[0];
    }

    /**
     * @param $model
     * @return Model
     * @throws Exception
     */
    protected function getModel(array $model): Model
    {
        if (count($model) < 1) {
            throw new Exception();
        }

        return $model[0];
    }

    /**
     * @param Model $modelClass
     * @param array $input
     * @param Model $eloquentModel
     * @return mixed
     * @throws SearchException
     */
    protected function executeQuery(string $modelClass, array $input, Model $eloquentModel)
    {
        // Create the query
        $builder = (new $modelClass)->newModelQuery();
        $jsonQuery = new JsonQuery($builder, $input);
        $jsonQuery->search();

        $keyName = $eloquentModel->getKeyName();
        $builder->select($keyName);

        // Execute the query
        $fetched = $builder->get()->pluck($keyName)->toArray();
        return $fetched;
    }
}
