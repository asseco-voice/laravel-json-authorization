<?php

namespace Voice\JsonAuthorization\App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Log;
use Voice\JsonAuthorization\RightParser;
use Voice\JsonQueryBuilder\JsonQuery;

class AuthorizationScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     * @throws \Voice\JsonQueryBuilder\Exceptions\SearchException
     */
    public function apply(Builder $builder, Model $model)
    {
        $input = RightParser::getAuthValues(get_class($model));

        if (count($input) < 1) {
            Log::info('You have no rights for this action.');
            $builder->whereRaw('1 = 0');
            return;
        }

        if (array_key_exists(0, $input) && $input[0] === '*') {
            Log::info('You have full rights for this action.');
            return;
        }

        $jsonQuery = new JsonQuery($builder, $input);
        $jsonQuery->search();
    }
}
