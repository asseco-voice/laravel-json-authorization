<?php

namespace Voice\JsonAuthorization\Authorization;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Voice\JsonAuthorization\App\AuthorizationRule;

class RuleResolver
{
    const CACHE_PREFIX = 'authorization_rules_';
    const CACHE_TTL = 60 * 60 * 24;

    /**
     * @param string $authorizableSetType
     * @param string $authorizableSetTypeId
     * @param string $authorizableValue
     * @param string $modelClass
     * @param int $resolvedModelId
     * @param string $right
     * @return array
     */
    public function resolveRules(string $authorizableSetType, string $authorizableSetTypeId, string $authorizableValue, string $modelClass, int $resolvedModelId, string $right): array
    {
        $rules = $this->fetchRules($authorizableSetType, $authorizableSetTypeId, $authorizableValue, $modelClass, $resolvedModelId);

        if (!array_key_exists($right, $rules)) {
            Log::info("[Authorization] No '$right' rights found for $modelClass.");
            return [];
        }

        $wrapped = Arr::wrap($rules[$right]);

        Log::info("[Authorization] Found rules for '$right' right: " . print_r($wrapped, true));
        return $wrapped;
    }


    public function fetchRules(string $authorizableSetType, string $authorizableSetTypeId, string $authorizableValue, string $modelClass, string $modelId): array
    {
        $cacheKey = self::CACHE_PREFIX . "{$authorizableSetType}_{$authorizableValue}_model_{$modelClass}";

        if (Cache::has($cacheKey)) {
            Log::info("[Authorization] Resolving $authorizableValue rights for auth model $modelClass from cache.");
            return Cache::get($cacheKey);
        }

        $resolveFromDb = AuthorizationRule::where([
            'authorizable_set_type_id' => $authorizableSetTypeId,
            'authorizable_set_value'   => $authorizableValue,
            'authorizable_model_id'    => $modelId,
        ])->first('rules');

        if ($resolveFromDb) {
            Log::info("[Authorization] Found $authorizableValue rights for auth model $modelClass. Adding to cache and returning.");
            $decoded = json_decode($resolveFromDb->rules, true);
            Cache::put($cacheKey, $decoded, self::CACHE_TTL);
            return $decoded;
        }

        // We still want to cache if there are no rules imposed to prevent going to DB unnecessarily
        Cache::put($cacheKey, [], self::CACHE_TTL);
        return [];
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
