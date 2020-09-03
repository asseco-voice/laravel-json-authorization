<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization\App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Throwable;
use Voice\JsonAuthorization\App\Traits\Cacheable;

class AuthorizationRule extends Model
{
    use Cacheable;

    // DB attributes
    const MODEL_ID = 'authorizable_model_id';
    const SET_TYPE_ID = 'authorizable_set_type_id';
    const SET_VALUE = 'authorizable_set_value';
    const RULES = 'rules';

    protected static function cacheKey(): string
    {
        return 'authorization_rules';
    }

    protected static function cacheAlternative(): array
    {
        // We don't want to cache anything from the start, we want rules to be appended to cache when used
        return [];
    }

    protected $guarded = ['id'];

    public function model(): BelongsTo
    {
        return $this->belongsTo(AuthorizableModel::class, self::MODEL_ID);
    }

    public function authorizableSetType(): BelongsTo
    {
        return $this->belongsTo(AuthorizableSetType::class);
    }

    /**
     * Retrieve what you can from the cache, go to the DB for rest.
     * Cache and return everything asked for (independently of whether search was a hit or not)
     * to prevent additional trips to the DB.
     * @param Collection $authorizableSets
     * @param string $modelClass
     * @return Collection
     * @throws Throwable
     */
    public static function cachedBy(Collection $authorizableSets, string $modelClass): Collection
    {
        $modelId = AuthorizableModel::getIdFor($modelClass);

        // Authorizable sets get reduced each iteration
        $cached = self::getCached($authorizableSets, $modelId);
        $stored = self::getStored($authorizableSets, $modelId);

        $merged = array_merge_recursive($cached, $stored, $authorizableSets->toArray());

        self::appendToCache($merged);

        return new Collection($merged);
    }

    protected static function getCached(Collection $authorizableSets, int $modelId): array
    {
        $rules = self::cached()->where(self::MODEL_ID, $modelId);
        self::cleanup($authorizableSets, $rules);


        return $rules->toArray();
    }

    /**
     * @param Collection $authorizableSets
     * @param int $modelId
     * @return array
     */
    protected static function getStored(Collection $authorizableSets, int $modelId): array
    {
        if ($authorizableSets->isEmpty()) {
            return [];
        }

        $rules = AuthorizationRule::where(self::MODEL_ID, $modelId)->where(function ($builder) use ($authorizableSets) {
            foreach ($authorizableSets as $authorizableSet) {
                // orWhere because authorizable set values are not unique. It is valid to have 'role xy' together with 'group xy'.
                $builder
                    ->orWhere(function ($builder) use ($authorizableSet) {
                        // pair up ID's with values to get the unique pairs back
                        // TODO: ovdje bi mi dobro došlo da su ipak grupirani...flatten (prepare) ili ne u onom collectionu?
                        $builder
                            ->where(self::SET_TYPE_ID, $authorizableSet[self::SET_TYPE_ID])
                            ->where(self::SET_VALUE, $authorizableSet[self::SET_VALUE]);
                    });
            }
        })->get([self::SET_TYPE_ID, self::SET_VALUE, self::RULES]);

        self::cleanup($authorizableSets, $rules);

        return self::decodeRules($rules->toArray());
    }

    /**
     * Decode to array to prepare for cache insertion. We don't want to decode every time the rule is returned
     * @param array $rules
     * @return array
     */
    protected static function decodeRules(array $rules): array
    {
        foreach ($rules as &$rule) {
            $rule['rules'] = json_decode($rule['rules'], true);
        }

        return $rules;
    }

    /**
     * Remove records which were found to prevent searching for same record at more than one place.
     * @param Collection $authorizableSets
     * @param Collection $collection
     */
    protected static function cleanup(Collection $authorizableSets, Collection $collection): void
    {
        if ($collection->isEmpty()) {
            return;
        }

        foreach ($authorizableSets as $key => $authorizableSet) {
            if (self::existsInTheCollection($authorizableSet, $collection)) {
                $authorizableSets->forget($key);
            }
        }
    }

    /**
     * Check if the collection contains a given authorizable set
     * @param array $authorizableSet
     * @param Collection $collection
     * @return Collection
     */
    protected static function existsInTheCollection(array $authorizableSet, Collection $collection): bool
    {
        return $collection->where(self::SET_TYPE_ID, $authorizableSet[self::SET_TYPE_ID])
            ->where(self::SET_VALUE, $authorizableSet[self::SET_VALUE])->isNotEmpty();
    }

    /**
     * Data preparation for pushing to collection which will ultimately end up in the cache in this format
     * @param string $authorizableSetTypeId
     * @param string $authorizableSetValue
     * @param array $rules
     * @return array
     */
    public static function prepare(string $authorizableSetTypeId, string $authorizableSetValue, array $rules = [])
    {
        return [
            self::SET_TYPE_ID => $authorizableSetTypeId,
            self::SET_VALUE   => $authorizableSetValue,
            self::RULES       => $rules,
        ];
    }
}
