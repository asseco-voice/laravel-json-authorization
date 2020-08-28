<?php

namespace Voice\JsonAuthorization\Authorization;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Throwable;
use Voice\JsonAuthorization\App\AuthorizationRule;
use Voice\JsonAuthorization\App\CachedModels\CachedAuthorizableModel;
use Voice\JsonAuthorization\Exceptions\AuthorizationException;

class RuleParser
{
    const ABSOLUTE_RIGHTS = '*';

    const CREATE_RIGHT = 'create';
    const READ_RIGHT = 'read';
    const UPDATE_RIGHT = 'update';
    const DELETE_RIGHT = 'delete';

    /**
     * These should reflect the same events from events to listen but without the '*' wildcard.
     */
    public array $eventRightMapping = [
        'eloquent.creating' => self::CREATE_RIGHT,
        'eloquent.updating' => self::UPDATE_RIGHT,
        'eloquent.deleting' => self::DELETE_RIGHT,
    ];

    protected AbsoluteRights    $absoluteRights;

    /**
     * RightParser constructor.
     * @param AbsoluteRights $absoluteRights
     */
    public function __construct(AbsoluteRights $absoluteRights)
    {
        $this->absoluteRights = $absoluteRights;
    }

    /**
     * @param string $modelClass
     * @param string $right
     * @return array|string[]
     * @throws Exception
     * @throws Throwable
     */
    public function getRules(string $modelClass, string $right = self::READ_RIGHT): array
    {
        if (!$this->isAuthorizable($modelClass)) {
            Log::info("[Authorization] Model '$modelClass' does not implement 'Voice\JsonAuthorization\App\Traits\Authorizable' trait (or you forgot to flush the cache). Skipping authorization...");
            return [self::ABSOLUTE_RIGHTS];
        }

        $authorizationRules = AuthorizationRule::getCached($modelClass);

        if ($this->absoluteRights->check($authorizationRules)) {
            return [self::ABSOLUTE_RIGHTS];
        }

        return $this->getMergedRules($authorizationRules, $modelClass, $right);
    }

    protected function isAuthorizable(string $modelClass): bool
    {
        /**
         * @var $authorizableModel CachedAuthorizableModel
         */
        $authorizableModel = App::make(CachedAuthorizableModel::class);
        return $authorizableModel->isAuthorizable($modelClass);
    }

    /**
     * @param CachedRuleCollection $authorizationRules
     * @param string $modelClass
     * @param string $right
     * @return array
     */
    protected function getMergedRules(CachedRuleCollection $authorizationRules, string $modelClass, string $right): array
    {
        $mergedRules = [];

        /**
         * @var $authorizationRule CachedRule
         */
        foreach ($authorizationRules as $authorizationRule) {

            if (!array_key_exists($right, $authorizationRule->rules)) {
                Log::info("[Authorization] No '$right' rights found for $modelClass.");
                continue;
            }

            $wrapped = Arr::wrap($authorizationRule->rules[$right]);

            if (array_key_exists(0, $wrapped) && $wrapped[0] === self::ABSOLUTE_RIGHTS) {
                return [self::ABSOLUTE_RIGHTS];
            }

            Log::info("[Authorization] Found rules for '$right' right: " . print_r($wrapped, true));

            $mergedRules = $this->mergeRules($mergedRules, $authorizationRule->rules[$right]);
        }

        Log::info("[Authorization] Merged rules: " . print_r($mergedRules, true));

        return $mergedRules;
    }

    /**
     * Events mapped within this class should reflect events registered within EloquentEvents class
     * (without the wildcard character)
     *
     * @param string $eventName
     * @throws AuthorizationException
     */
    public function checkEventMapping(string $eventName)
    {
        $eventMapped = array_key_exists($eventName, $this->eventRightMapping);

        if (!$eventMapped) {
            throw new AuthorizationException("Event '$eventName' is not mapped correctly.");
        }
    }

    public function mergeRules(array $mergedRules, array $rules): array
    {
        $search = 'search';
        $or = '||';

        if ($this->rulesMalformed($search, $rules)) {
            return $mergedRules;
        }

        $mergedRules = $this->initMergedRulesArrayKeys($search, $mergedRules, $or);
        $mergedRules[$search][$or][] = $rules[$search];

        return $mergedRules;
    }

    /**
     * @param string $search
     * @param array $rules
     * @return bool
     */
    protected function rulesMalformed(string $search, array $rules): bool
    {
        return !array_key_exists($search, $rules);
    }

    /**
     * @param string $search
     * @param array $mergedRules
     * @param string $or
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
