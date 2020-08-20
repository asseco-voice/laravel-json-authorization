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
         * @var $rightParser RuleParser
         */
        $rightParser = App::make(RuleParser::class);
        $authValues = $rightParser->getAuthValues(get_class($model));

        if (count($authValues) < 1) {
            Log::info("[Authorization] You have no rights for this action.");
            $builder->whereRaw('1 = 0');
            return;
        }

        if (array_key_exists(0, $authValues) && $authValues[0] === $rightParser::ABSOLUTE_RIGHTS) {
            Log::info("[Authorization] You have full rights for this action.");
            return;
        }

        $jsonQuery = new JsonQuery($builder, $authValues);
        $jsonQuery->search();
    }
}
