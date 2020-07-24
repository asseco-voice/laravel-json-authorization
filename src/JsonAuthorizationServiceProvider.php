<?php

namespace Voice\JsonAuthorization;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Voice\JsonQueryBuilder\JsonQuery;

class JsonAuthorizationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        Event::listen(RightParser::$eventsToListen, function ($event, $model) {
            Log::info($event);

            /**
             * @var Model $eloquentModel
             */
            $eloquentModel = $model[0];

            /**
             * @var Model $class
             */
            $class = get_class($eloquentModel);
            $keyName = $eloquentModel->getKeyName();

            $eventName = $this->getEventName($event);

            if (!$this->eventRegistered($eventName)) {
                Log::info("Event $eventName is not registered. Assuming you have rights then...");
                return true;
            }

            $input = RightParser::getAuthValues($class, RightParser::$eventRightMapping[$eventName]);

            if (count($input) < 1) {
                Log::info('You have no rights for this action.');
                return false;
            }

            if (array_key_exists(0, $input) && $input[0] === '*') {
                Log::info('You have full rights for this action.');
                return true;
            }

            // Create the query
            $builder = (new $class)->newModelQuery();
            $jsonQuery = new JsonQuery($builder, $input);
            $jsonQuery->search();
            $builder->select($keyName);

            // Execute the query
            $fetched = $builder->get()->pluck($keyName)->toArray();

            // Compare primary keys only
            return in_array($eloquentModel->getKey(), $fetched);
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
    }

    /**
     * @param $event
     * @return mixed|string
     * @throws \Exception
     */
    protected function getEventName($event)
    {
        $parsed = explode(':', $event);

        if (count($parsed) != 2) {
            throw new \Exception();
        }

        return $parsed[0];
    }

    /**
     * @param string $eventName
     * @return bool
     */
    protected function eventRegistered(string $eventName): bool
    {
        return array_key_exists($eventName, RightParser::$eventRightMapping);
    }
}
