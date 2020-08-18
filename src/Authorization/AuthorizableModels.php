<?php

namespace Voice\JsonAuthorization\Authorization;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Voice\JsonAuthorization\App\AuthorizableModel;

class AuthorizableModels
{
    const CACHE_PREFIX = 'authorization_models';
    const CACHE_TTL = 60 * 60 * 24;

    public array $models;

    public function __construct()
    {
        $this->models = $this->getAuthorizableModels();
    }

    public function getAuthorizableModels(): array
    {
        if (Cache::has(self::CACHE_PREFIX)) {
            return Cache::get(self::CACHE_PREFIX);
        }

        $paths = Config::get('asseco-authorization.models_path');
        $models = [];

        foreach ($paths as $path => $namespace) {
            $models = $this->traversePath($path, $namespace, $models);
        }

        Cache::put(self::CACHE_PREFIX, $models, self::CACHE_TTL);
        return $models;
    }

    protected function traversePath(string $path, string $namespace, array $models): array
    {
        $results = scandir($path);

        foreach ($results as $result) {
            if ($result === '.' || $result === '..' || stripos($result, '.php') === false) {
                continue;
            }

            $filename = "{$path}/{$result}";

            if (is_dir($filename)) {
                continue;
            }

            $result = substr($result, 0, -4);

            $model = $namespace . $result;
            if ($this->hasAuthorizesWithJsonTrait($model)) {
                $models[] = $model;
            }
        }

        return $models;
    }

    protected function hasAuthorizesWithJsonTrait(string $class): bool
    {
        $traits = class_uses($class);
        $authorizationTrait = Config::get('asseco-authorization.trait_path');

        return in_array($authorizationTrait, $traits);
    }

    public function isModelAuthorizable(string $model): bool
    {
        return in_array($model, $this->models);
    }

    public function resolveAuthorizationModel($model): Model
    {
        $cacheKey = self::CACHE_PREFIX . "_$model";

        if (Cache::has($cacheKey)) {
            Log::info("[Authorization] Resolving $model from cache.");
            return Cache::get($cacheKey);
        }

        $resolveFromDb = AuthorizableModel::where('name', $model)->first();

        if ($resolveFromDb) {
            Log::info("[Authorization] Resolved $model from DB. Adding to cache and returning.");
            Cache::put($cacheKey, $resolveFromDb, self::CACHE_TTL);
            return $resolveFromDb;
        }

        Log::info("[Authorization] Model $model is authorizable, but doesn't exist in DB yet. Creating...");
        $newModel = AuthorizableModel::create(['name' => $model]);

        Cache::put($cacheKey, $newModel, self::CACHE_TTL);
        return $newModel;
    }
}
