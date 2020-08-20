<?php

namespace Voice\JsonAuthorization\Authorization;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Voice\JsonAuthorization\App\AuthorizableModel;

class AuthorizableModels
{
    const CACHE_PREFIX = 'authorizable_models';
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
        $files = scandir($path);

        foreach ($files as $file) {
            if (stripos($file, '.php') === false) {
                continue;
            }

            $modelName = substr($file, 0, -4);
            $model = $namespace . $modelName;

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

    public function resolveAuthorizableModelId(string $model): int
    {
        $cacheKey = self::CACHE_PREFIX . "_$model";

        if (Cache::has($cacheKey)) {
            Log::info("[Authorization] Resolving $model from cache.");
            return Cache::get($cacheKey);
        }

        $resolveFromDb = AuthorizableModel::where('name', $model)->pluck('id')->first();

        if ($resolveFromDb) {
            Log::info("[Authorization] Resolved $model from DB. Adding to cache and returning.");
            Cache::put($cacheKey, $resolveFromDb, self::CACHE_TTL);
            return $resolveFromDb;
        }

        Log::info("[Authorization] Model $model is authorizable, but doesn't exist in DB yet. Creating...");
        $newModel = AuthorizableModel::create(['name' => $model]);

        Cache::put($cacheKey, $newModel->id, self::CACHE_TTL);
        return $newModel->id;
    }
}
