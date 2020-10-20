<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization\App\Scopes;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Throwable;
use Voice\JsonAuthorization\Authorization\RuleParser;
use Voice\JsonQueryBuilder\Exceptions\JsonQueryBuilderException;
use Voice\JsonQueryBuilder\JsonQuery;

class AuthorizationScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Builder $builder
     * @param Model $model
     * @return void
     * @throws JsonQueryBuilderException
     * @throws Exception
     * @throws Throwable
     */
    public function apply(Builder $builder, Model $model): void
    {
        $override = Config::get('asseco-authorization.override_authorization');

        if (App::runningInConsole() || $override) {
            return;
        }

        /**
         * @var $ruleParser RuleParser
         */
        $ruleParser = App::make(RuleParser::class);
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
