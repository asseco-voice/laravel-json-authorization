<?php

namespace Voice\JsonAuthorization\App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Voice\JsonAuthorization\Authorization\RuleParser;
use Voice\JsonQueryBuilder\JsonQuery;

class AuthorizationScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     * @throws \Voice\JsonQueryBuilder\Exceptions\JsonQueryBuilderException
     * @throws \Exception
     */
    public function apply(Builder $builder, Model $model)
    {
        $override = Config::get('asseco-authorization.override_authorization');

        if (App::runningInConsole() || $override) {
            return;
        }

        /**
         * @var $ruleParser RuleParser
         */
        $ruleParser = App::make(RuleParser::class);
        $rules = $ruleParser->getRules(get_class($model));

        if (count($rules) < 1) {
            Log::info("[Authorization] You have no rights for this action.");
            $builder->whereRaw('1 = 0');
            return;
        }

        if (array_key_exists(0, $rules) && $rules[0] === $ruleParser::ABSOLUTE_RIGHTS) {
            Log::info("[Authorization] You have full rights for this action.");
            return;
        }

        $jsonQuery = new JsonQuery($builder, $rules);
        $jsonQuery->search();
    }
}
