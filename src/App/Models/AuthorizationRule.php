<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\App\Models;

use Asseco\JsonAuthorization\App\Contracts\AuthorizableModel;
use Asseco\JsonAuthorization\App\Contracts\AuthorizableSetType;
use Asseco\JsonAuthorization\App\Traits\Cacheable;
use Asseco\JsonAuthorization\Authorization\UserAuthorizableSet;
use Asseco\JsonAuthorization\Database\Factories\AuthorizationRuleFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use JsonException;
use Throwable;

class AuthorizationRule extends Model implements \Asseco\JsonAuthorization\App\Contracts\AuthorizationRule
{
    use Cacheable, HasFactory;

    // DB attributes
    public const MODEL_ID = 'authorizable_model_id';
    public const SET_TYPE_ID = 'authorizable_set_type_id';
    public const SET_VALUE = 'authorizable_set_value';
    public const RULES = 'rules';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected static function newFactory()
    {
        return AuthorizationRuleFactory::new();
    }

    /**
     * Don't ever rename this to just 'model', it will conflict with Laravel.
     *
     * @return BelongsTo
     */
    public function authorizableModel(): BelongsTo
    {
        return $this->belongsTo(get_class(app(AuthorizableModel::class)));
    }

    public function authorizableSetType(): BelongsTo
    {
        return $this->belongsTo(get_class(app(AuthorizableSetType::class)));
    }

    protected static function cacheKey(): string
    {
        return 'authorization_rules';
    }

    protected static function cacheAlternative(): array
    {
        // We don't want to cache anything from the start, we want rules to be appended to cache when used
        return [];
    }

    /**
     * Retrieve what you can from the cache, go to the DB for rest.
     * Cache and return everything asked for (independently of whether search was a hit or not)
     * to prevent additional trips to the DB.
     *
     * @param  string  $modelClass
     * @return Collection
     *
     * @throws Throwable
     */
    public static function resolveRulesFor(string $modelClass): Collection
    {
        $formattedSets = UserAuthorizableSet::prepare();

        /** @var AuthorizableModel $authorizableModel */
        $authorizableModel = app(AuthorizableModel::class);

        $modelId = $authorizableModel::getIdFor($modelClass);

        // Authorizable sets get reduced each iteration
        $cached = self::getCached($formattedSets, $modelId);
        $stored = self::getStored($formattedSets, $modelId);

        $merged = array_merge_recursive($cached, $stored, $formattedSets->toArray());

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
     * @param  Collection  $authorizableSets
     * @param  int  $modelId
     * @return array
     *
     * @throws JsonException
     */
    protected static function getStored(Collection $authorizableSets, int $modelId): array
    {
        if ($authorizableSets->isEmpty()) {
            return [];
        }

        $rules = self::getRulesForGivenModel($modelId, $authorizableSets);

        self::cleanup($authorizableSets, $rules);

        return self::decodeRules($rules->toArray());
    }

    protected static function getRulesForGivenModel(int $modelId, Collection $authorizableSets)
    {
        return self::query()
            ->where(self::MODEL_ID, $modelId)
            ->where(function ($builder) use ($authorizableSets) {
                foreach ($authorizableSets as $authorizableSet) {
                    // orWhere because authorizable set values are not unique.
                    // It is valid to have 'role xy' together with 'group xy'.
                    $builder->orWhere(function ($builder) use ($authorizableSet) {
                        // Pair up ID's with values to get the unique pairs back
                        $builder
                            ->where(self::SET_TYPE_ID, Arr::get($authorizableSet, self::SET_TYPE_ID))
                            ->where(self::SET_VALUE, Arr::get($authorizableSet, self::SET_VALUE));
                    });
                }
            })->get([self::SET_TYPE_ID, self::SET_VALUE, self::RULES]);
    }

    /**
     * Decode to array to prepare for cache insertion. We don't want to decode every time the rule is returned.
     *
     * @param  array  $rules
     * @return array
     *
     * @throws JsonException
     */
    protected static function decodeRules(array $rules): array
    {
        foreach ($rules as &$rule) {
            $rule['rules'] = json_decode($rule['rules'], true, 512, JSON_THROW_ON_ERROR);
        }

        return $rules;
    }

    /**
     * Remove records which were found to prevent searching for same record at more than one place.
     *
     * @param  Collection  $authorizableSets
     * @param  Collection  $collection
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
     * Check if the collection contains a given authorizable set.
     *
     * @param  array  $authorizableSet
     * @param  Collection  $collection
     * @return bool
     */
    protected static function existsInTheCollection(array $authorizableSet, Collection $collection): bool
    {
        return $collection->where(self::SET_TYPE_ID, $authorizableSet[self::SET_TYPE_ID])
            ->where(self::SET_VALUE, $authorizableSet[self::SET_VALUE])->isNotEmpty();
    }

    /**
     * Format input for pushing to collection which will ultimately end up in the cache in this format.
     *
     * @param  mixed  $authorizableSetTypeId
     * @param  string  $authorizableSetValue
     * @param  array  $rules
     * @return array
     */
    public static function format($authorizableSetTypeId, string $authorizableSetValue, array $rules = []): array
    {
        return [
            self::SET_TYPE_ID => $authorizableSetTypeId,
            self::SET_VALUE   => $authorizableSetValue,
            self::RULES       => $rules,
        ];
    }
}
