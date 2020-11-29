<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\App;

use Asseco\JsonAuthorization\App\Traits\Cacheable;
use Asseco\JsonAuthorization\App\Traits\FindsTraits;
use Asseco\JsonAuthorization\Exceptions\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Throwable;

class AuthorizableModel extends Model
{
    use FindsTraits, Cacheable;

    protected $fillable = ['name'];

    public function rules(): HasMany
    {
        return $this->hasMany(AuthorizationRule::class, 'authorization_rule_id');
    }

    protected static function cacheKey(): string
    {
        return 'authorizable_models';
    }

    protected static function cacheAlternative(): array
    {
        $modelsInDb = self::all(['id', 'name']);
        $modelsWithTrait = self::withTrait();

        return self::sync($modelsInDb, $modelsWithTrait)->toArray();
    }

    /**
     * Find models which implement Authorizable trait.
     * @return Collection
     */
    protected static function withTrait(): Collection
    {
        $authorizableTraitPath = config('asseco-authorization.trait_path');

        return new Collection(static::getModelsWithTrait($authorizableTraitPath));
    }

    /**
     * Remove models from DB which no longer implement the trait and add those which implement it but don't yet exist in DB.
     * @param Collection $modelsInDb
     * @param Collection $modelsWithTrait
     * @return Collection
     */
    protected static function sync(Collection $modelsInDb, Collection $modelsWithTrait): Collection
    {
        $dbNames = $modelsInDb->pluck('name')->toArray();
        $traitNames = $modelsWithTrait->toArray();

        self::deleteModelsWithoutTrait(array_diff($dbNames, $traitNames));
        self::insertModelsWithTrait(array_diff($traitNames, $dbNames));

        return self::all(['id', 'name']);
    }

    protected static function deleteModelsWithoutTrait(array $deleteDiff): void
    {
        if ($deleteDiff) {
            AuthorizableModel::query()->whereIn('name', $deleteDiff)->delete();
        }
    }

    protected static function insertModelsWithTrait(array $insertDiff): void
    {
        $insertData = array_map(function ($model) {
            return ['name' => $model];
        }, $insertDiff);

        if ($insertData) {
            AuthorizableModel::query()->insert(array_values($insertData));
        }
    }

    public static function isAuthorizable(string $model): bool
    {
        return self::cached()->pluck('name')->contains($model);
    }

    /**
     * @param string $model
     * @return int
     * @throws Throwable
     */
    public static function getIdFor(string $model): int
    {
        $cachedId = self::cached()->where('name', $model)->pluck('id')->first();

        throw_if(!$cachedId, new AuthorizationException("Model '$model' is not authorizable, but this should never be triggered anyways..."));

        return $cachedId;
    }
}
