<?php

namespace Voice\JsonAuthorization\Authorization;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Voice\JsonAuthorization\Exceptions\AuthorizationException;
use Voice\JsonQueryBuilder\Exceptions\JsonQueryBuilderException;
use Voice\JsonQueryBuilder\JsonQuery;

class EloquentEvents
{
    public array $eventsToListen = [
        // 'eloquent.retrieved*', // Covered with scopes
        'eloquent.creating*',
        'eloquent.updating*',
        'eloquent.deleting*',
    ];

    public function attachEloquentListener(): void
    {
        Event::listen($this->eventsToListen, function ($event, $model) {

            $eloquentModel = $this->getModel($model);
            $modelClass = get_class($eloquentModel);

            Log::info("[Authorization] Triggered Eloquent event: $event");

            /**
             * @var $rightParser RightParser
             */
            $rightParser = App::make(RightParser::class);
            $eventName = $this->parseEventName($event, $rightParser);
            $input = $rightParser->getAuthValues($modelClass, $rightParser->eventRightMapping[$eventName]);

            if (count($input) < 1) {
                Log::info("[Authorization] You have no '$eventName' rights for '$modelClass' model.");
                return false;
            }

            if (array_key_exists(0, $input) && $input[0] === $rightParser::ABSOLUTE_RIGHTS) {
                Log::info("[Authorization] You have full '$eventName' rights for '$modelClass' model.");
                return true;
            }

            $fetched = $this->executeQuery($modelClass, $input, $eloquentModel);

            // Compare primary keys only ... what if there are none?
            return in_array($eloquentModel->getKey(), $fetched);
        });
    }

    /**
     * @param $event
     * @param RightParser $rightParser
     * @return mixed|string
     * @throws AuthorizationException
     * @throws \Exception
     */
    protected function parseEventName(string $event, RightParser $rightParser): string
    {
        $parsed = explode(':', $event);

        if (count($parsed) != 2) {
            throw new AuthorizationException("Something went wrong parsing the '$event' event.");
        }

        $eventName = $parsed[0];

        $rightParser->checkEventMapping($eventName);

        return $eventName;
    }

    /**
     * @param $model
     * @return Model
     * @throws AuthorizationException
     */
    protected function getModel(array $model): Model
    {
        if (count($model) < 1) {
            throw new AuthorizationException("Something went wrong parsing the model from event.");
        }

        return $model[0];
    }

    /**
     * @param string $modelClass
     * @param array $input
     * @param Model $eloquentModel
     * @return mixed
     * @throws JsonQueryBuilderException
     */
    protected function executeQuery(string $modelClass, array $input, Model $eloquentModel)
    {
        /**
         * @var Model $model
         * @var Builder $builder
         */
        $model = new $modelClass;
        $builder = $model->newModelQuery();
        $jsonQuery = new JsonQuery($builder, $input);
        $jsonQuery->search();

        $keyName = $eloquentModel->getKeyName();
        $builder->select($keyName);

        return $builder->get()->pluck($keyName)->toArray();
    }
}
