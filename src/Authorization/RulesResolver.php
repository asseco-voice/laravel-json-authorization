<?php

namespace Voice\JsonAuthorization\Authorization;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Voice\JsonAuthorization\App\Authorization;

class RulesResolver
{
    const CACHE_PREFIX = 'authorization_';
    const CACHE_TTL = 60 * 60 * 24;

    /**
     * @param $role
     * @param string $modelClass
     * @param Model $resolvedModel
     * @param string $right
     * @return array
     */
    public function resolveRules($role, string $modelClass, Model $resolvedModel, string $right): array
    {
        $rules = $this->fetchRules($role, $modelClass, $resolvedModel->id);

        if (!array_key_exists($right, $rules)) {
            Log::info("[Authorization] No '$right' rights found for $modelClass.");
            return [];
        }

        $wrapped = Arr::wrap($rules[$right]);

        Log::info("[Authorization] Found rules for '$right' right: " . print_r($wrapped, true));
        return $wrapped;
    }


    public function fetchRules(string $role, string $modelClass, string $modelId): array
    {
        $cacheKey = self::CACHE_PREFIX . "role_{$role}_model_{$modelClass}";

        if (Cache::has($cacheKey)) {
            Log::info("[Authorization] Resolving $role rights for auth model $modelClass from cache.");
            return Cache::get($cacheKey);
        }

        $resolveFromDb = Authorization::where([
            'role'                   => $role,
            'authorization_model_id' => $modelId,
        ])->first();

        if ($resolveFromDb) {
            Log::info("[Authorization] Found $role rights for auth model $modelClass. Adding to cache and returning.");
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
        return [];
    }
}
