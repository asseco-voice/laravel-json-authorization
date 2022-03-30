<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\App\Scopes;

use Asseco\JsonAuthorization\Authorization\RuleParser;
use Asseco\JsonQueryBuilder\Exceptions\JsonQueryBuilderException;
use Asseco\JsonQueryBuilder\JsonQuery;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuthorizationScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  Builder  $builder
     * @param  Model  $model
     * @return void
     *
     * @throws JsonQueryBuilderException
     * @throws Exception
     * @throws Throwable
     */
    public function apply(Builder $builder, Model $model): void
    {
        $override = config('asseco-authorization.override_authorization');

        if (app()->runningInConsole() || $override) {
            return;
        }

        /**
         * @var $ruleParser RuleParser
         */
        $ruleParser = app()->make(RuleParser::class);
        $modelClass = get_class($model);
        $rules = $ruleParser->getRules($modelClass);

        if (count($rules) < 1) {
            Log::info('[Authorization] You have no rights for this action.');
            $builder->whereRaw('1 = 0');

            return;
        }

        if (array_key_exists(0, $rules) && $rules[0] === $ruleParser::ABSOLUTE_RIGHTS) {
            Log::info("[Authorization] You have full 'read' rights for '$modelClass' model.");

            return;
        }

        $jsonQuery = new JsonQuery($builder, $rules);
        $jsonQuery->search();
    }
}
