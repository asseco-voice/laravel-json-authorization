<?php

namespace Voice\JsonAuthorization\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Voice\JsonAuthorization\App\Traits\FindsTraits;

class AuthorizableModel extends Model
{
    use FindsTraits;

    const CACHE_PREFIX = 'authorizable_models';
    const CACHE_TTL = 60 * 60 * 24;

    protected $guarded = ['id'];

    public function rules()
    {
        return $this->hasMany(AuthorizationRule::class, 'authorization_rule_id');
    }

    public static function isAuthorizable(string $model): bool
    {
        return in_array($model, self::getCached());
    }

    public static function getCached(): array
    {
        if (Cache::has(self::CACHE_PREFIX)) {
            return Cache::get(self::CACHE_PREFIX);
        }

        $authorizableTraitPath = Config::get('asseco-authorization.trait_path');

        $models = self::getModelsWithTrait($authorizableTraitPath);

        Cache::put(self::CACHE_PREFIX, $models, self::CACHE_TTL);
        return $models;
    }

    public static function getCachedId(string $model): int
    {
        $cacheKey = self::CACHE_PREFIX . "_$model";

        if (Cache::has($cacheKey)) {
            Log::info("[Authorization] Resolving $model from cache.");
            return Cache::get($cacheKey);
        }

        $resolveFromDb = self::where('name', $model)->pluck('id')->first();

        if ($resolveFromDb) {
            Log::info("[Authorization] Resolved $model from DB. Adding to cache and returning.");
            Cache::put($cacheKey, $resolveFromDb, self::CACHE_TTL);
            return $resolveFromDb;
        }

        Log::info("[Authorization] Model $model is authorizable, but doesn't exist in DB yet. Creating...");
        $newModel = self::create(['name' => $model]);

        Cache::put($cacheKey, $newModel->id, self::CACHE_TTL);
        return $newModel->id;
    }
}
