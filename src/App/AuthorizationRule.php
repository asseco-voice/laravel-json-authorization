<?php

namespace Voice\JsonAuthorization\App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;
use Voice\JsonAuthorization\App\CachedModels\CachedAuthorizableModel;
use Voice\JsonAuthorization\Authorization\AuthorizableSet;
use Voice\JsonAuthorization\Authorization\AuthorizableSets;
use Voice\JsonAuthorization\Authorization\CachedRule;
use Voice\JsonAuthorization\Authorization\CachedRuleCollection;

class AuthorizationRule extends Model
{
    const CACHE_PREFIX = 'authorization_rules_';
    const CACHE_TTL = 60 * 60 * 24;

    protected $guarded = ['id'];

    public function model()
    {
        return $this->belongsTo(AuthorizableModel::class, 'authorization_model_id');
    }

    public function authorizableSetType()
    {
        return $this->belongsTo(AuthorizableSetType::class);
    }

    /**
     * @param string $modelClass
     * @return CachedRuleCollection
     * @throws Throwable
     */
    public static function getCached(string $modelClass): CachedRuleCollection
    {
        $authorizableSets = (new AuthorizableSets())->get();

        $cachedRules = self::resolveFromCache($authorizableSets, $modelClass);
        $dbRules = self::resolveFromDb($authorizableSets, $modelClass);
        $unresolvedResults = self::prepareResultsForCaching($authorizableSets);

        $toCache = $dbRules->mergeRecursive($unresolvedResults);
        self::cache($toCache, $modelClass);

        return $cachedRules->mergeRecursive($dbRules);
    }

    /**
     * Resolves keys from cache if they exist.
     * Resolved keys are removed from $authorizableSets so that DB knows what to skip resolving
     * @param Collection $authorizableSets
     * @param string $modelClass
     * @return CachedRuleCollection
     * @throws Throwable
     */
    protected static function resolveFromCache(Collection $authorizableSets, string $modelClass): CachedRuleCollection
    {
        $ruleCollection = new CachedRuleCollection();

        Log::info("[Authorization] Resolving cached rights for '$modelClass' model.");

        /**
         * @var $authorizableSet AuthorizableSet
         */
        foreach ($authorizableSets as $authorizableSet) {
            foreach ($authorizableSet->values as $key => $authorizableSetValue) {

                $cacheKey = self::CACHE_PREFIX . "{$authorizableSet->type}_{$authorizableSetValue}_model_{$modelClass}";

                if (!Cache::has($cacheKey)) {
                    continue;
                }

                $cachedRule = Cache::get($cacheKey);
                $ruleCollection->add(new CachedRule($cachedRule['typeId'], $cachedRule['type'], $cachedRule['value'], $cachedRule['rules']));

                $authorizableSet->removeValueByKey($key);
            }
        }

        self::authorizableSetCleanup($authorizableSets);

        return $ruleCollection;
    }

    protected static function authorizableSetCleanup(Collection $authorizableSets)
    {
        foreach ($authorizableSets as $key => $authorizableSet) {
            if (empty($authorizableSet->values)) {
                $authorizableSets->forget($key);
            }
        }
    }

    /**
     * Query builder for fetching required records at once to avoid multiple queries
     * @param Collection $authorizableSets
     * @param string $modelClass
     * @return CachedRuleCollection
     * @throws Throwable
     */
    protected static function resolveFromDb(Collection $authorizableSets, string $modelClass): CachedRuleCollection
    {
        if ($authorizableSets->isEmpty()) {
            return new CachedRuleCollection();
        }

        Log::info("[Authorization] Resolving non-cached rights from DB for '$modelClass' model.");

        $modelId = self::getAuthorizableModelId($modelClass);

        $typesWithRules = App::make('cached-authorizable-set-types')
            ->load(['rules' => function ($builder) use ($modelId, $authorizableSets) {
                $builder
                    ->select('authorizable_set_type_id', 'authorizable_set_value', 'rules')
                    ->where('authorizable_model_id', $modelId)
                    ->where(function ($builder) use ($authorizableSets) { // AND wrapper. Otherwise it will attach OR and mess up the results
                        /**
                         * @var AuthorizableSet $authorizableSet
                         */
                        foreach ($authorizableSets as $authorizableSet) {
                            // orWhere because authorizable set values are not unique. It is valid to have 'role xy' together with 'group xy'.
                            $builder
                                ->orWhere(function ($builder) use ($authorizableSet) {
                                    // pair up ID's with values to get the unique pairs back
                                    $builder
                                        ->where('authorizable_set_type_id', $authorizableSet->id)
                                        ->whereIn('authorizable_set_value', $authorizableSet->values);
                                });
                        }
                    });
            }]);

        return self::prepareDbData($typesWithRules, $authorizableSets);
    }

    protected static function getAuthorizableModelId(string $modelClass): int
    {
        /**
         * @var $cachedAuthorizableModel CachedAuthorizableModel
         */
        $cachedAuthorizableModel = App::make(CachedAuthorizableModel::class);
        return $cachedAuthorizableModel->getCachedId($modelClass);
    }

    /**
     * @param Collection $typesWithRules
     * @param Collection $authorizableSets
     * @return CachedRuleCollection
     * @throws Throwable
     */
    protected static function prepareDbData(Collection $typesWithRules, Collection $authorizableSets): CachedRuleCollection
    {
        $ruleCollection = new CachedRuleCollection();

        foreach ($typesWithRules as $type) {

            $rules = $type->rules;

            foreach ($rules as $rule) {
                $ruleCollection->add(new CachedRule($type->id, $type->name, $rule->authorizable_set_value, json_decode($rule->rules, true)));

                /**
                 * @var $authorizableSet AuthorizableSet
                 */
                $authorizableSet = $authorizableSets->where('type', $type->name)->first();

                if ($authorizableSet) {
                    $authorizableSet->removeValue($rule->authorizable_set_value);
                }
            }
        }

        return $ruleCollection;
    }

    /**
     * @param Collection $unresolvedAuthorizableSets
     * @return CachedRuleCollection
     * @throws Throwable
     */
    protected static function prepareResultsForCaching(Collection $unresolvedAuthorizableSets): CachedRuleCollection
    {
        $ruleCollection = new CachedRuleCollection();

        foreach ($unresolvedAuthorizableSets as $authorizableSet) {
            foreach ($authorizableSet->values as $authorizableSetValue) {
                $ruleCollection->add(new CachedRule($authorizableSet->id, $authorizableSet->type, $authorizableSetValue, []));
            }
        }

        return $ruleCollection;
    }

    /**
     * @param Collection $authorizableSets
     * @param string $modelClass
     */
    protected static function cache(Collection $authorizableSets, string $modelClass): void
    {
        /**
         * @var $authorizableSet CachedRule
         */
        foreach ($authorizableSets as $authorizableSet) {
            $cacheKey = self::CACHE_PREFIX . "{$authorizableSet->type}_{$authorizableSet->value}_model_{$modelClass}";
            Cache::put($cacheKey, $authorizableSet->prepare(), self::CACHE_TTL);
        }
    }

}
