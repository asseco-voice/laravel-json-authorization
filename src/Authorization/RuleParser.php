<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\Authorization;

use Asseco\JsonAuthorization\App\Contracts\AuthorizableModel;
use Asseco\JsonAuthorization\App\Contracts\AuthorizationRule;
use Asseco\JsonAuthorization\App\Traits\Authorizable;
use Asseco\JsonAuthorization\Exceptions\AuthorizationException;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

class RuleParser
{
    public const ABSOLUTE_RIGHTS = '*';

    public const CREATE_RIGHT = 'create';
    public const READ_RIGHT = 'read';
    public const UPDATE_RIGHT = 'update';
    public const DELETE_RIGHT = 'delete';

    public const SEARCH = 'search';
    public const OR = '||';

    /**
     * These should reflect the same events from events to listen but without the '*' wildcard.
     */
    public array $eventRightMapping = [
        'eloquent.creating' => self::CREATE_RIGHT,
        'eloquent.updating' => self::UPDATE_RIGHT,
        'eloquent.deleting' => self::DELETE_RIGHT,
    ];

    /**
     * @param  string  $modelClass
     * @param  string  $right
     * @return array|string[]
     *
     * @throws Exception
     * @throws Throwable
     */
    public function getRules(string $modelClass, string $right = self::READ_RIGHT): array
    {
        /** @var AuthorizableModel $authorizableModel */
        $authorizableModel = app(AuthorizableModel::class);

        if (!$authorizableModel::isAuthorizable($modelClass)) {
            Log::info("[Authorization] Model '$modelClass' does not implement " . Authorizable::class . 'trait (or you forgot to flush the cache). Skipping authorization...');

            return [self::ABSOLUTE_RIGHTS];
        }

        /** @var AuthorizationRule $authorizationRule */
        $authorizationRule = app(AuthorizationRule::class);

        $authorizationRules = $authorizationRule::resolveRulesFor($modelClass);

        if (AbsoluteRights::hasRole($authorizationRules)) {
            return [self::ABSOLUTE_RIGHTS];
        }

        return $this->getMergedRules($authorizationRules, $modelClass, $right);
    }

    /**
     * @param  Collection  $authorizationRules
     * @param  string  $modelClass
     * @param  string  $right
     * @return array
     */
    protected function getMergedRules(Collection $authorizationRules, string $modelClass, string $right): array
    {
        $mergedRules = [];

        foreach ($authorizationRules as $authorizationRule) {
            $rules = $authorizationRule['rules'];
            if (!array_key_exists($right, $rules)) {
                Log::info("[Authorization] No '$right' rights found for $modelClass.");
                continue;
            }

            $wrapped = Arr::wrap($rules[$right]);

            if (array_key_exists(0, $wrapped) && $wrapped[0] === self::ABSOLUTE_RIGHTS) {
                return [self::ABSOLUTE_RIGHTS];
            }

            Log::info("[Authorization] Found rules for '$right' right: " . print_r($wrapped, true));

            $mergedRules = $this->mergeRules($mergedRules, $rules[$right]);
        }

        Log::info('[Authorization] Merged rules: ' . print_r($mergedRules, true));

        return $mergedRules;
    }

    /**
     * Events mapped within this class should reflect events registered within EloquentEvents class
     * (without the wildcard character).
     *
     * @param  string  $eventName
     *
     * @throws AuthorizationException
     */
    public function checkEventMapping(string $eventName): void
    {
        $eventMapped = array_key_exists($eventName, $this->eventRightMapping);

        if (!$eventMapped) {
            throw new AuthorizationException("Event '$eventName' is not mapped correctly.");
        }
    }

    public function mergeRules(array $mergedRules, array $rules): array
    {
        if ($this->rulesMalformed(self::SEARCH, $rules)) {
            return $mergedRules;
        }

        $mergedRules = $this->initMergedRulesArrayKeys(self::SEARCH, $mergedRules, self::OR);
        $mergedRules[self::SEARCH][self::OR][] = $rules[self::SEARCH];

        return $mergedRules;
    }

    /**
     * @param  string  $search
     * @param  array  $rules
     * @return bool
     */
    protected function rulesMalformed(string $search, array $rules): bool
    {
        return !array_key_exists($search, $rules);
    }

    /**
     * @param  string  $search
     * @param  array  $mergedRules
     * @param  string  $or
     * @return array
     */
    protected function initMergedRulesArrayKeys(string $search, array $mergedRules, string $or): array
    {
        if (!array_key_exists($search, $mergedRules)) {
            $mergedRules[$search] = [];
        }

        if (!array_key_exists($or, $mergedRules[$search])) {
            $mergedRules[$search][$or] = [];
        }

        return $mergedRules;
    }
}
