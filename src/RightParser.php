<?php

namespace Voice\JsonAuthorization;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Voice\JsonAuthorization\App\Authorization;
use Voice\JsonAuthorization\App\AuthorizationModel;

class RightParser
{
    const CACHE_PREFIX = 'authorization_';
    const CACHE_TTL = 60 * 60 * 24;

    const CREATE_RIGHT = 'create';
    const READ_RIGHT = 'read';
    const UPDATE_RIGHT = 'update';
    const DELETE_RIGHT = 'delete';

    public array $eventsToListen = [
        // 'eloquent.retrieved*', // Covered with scopes
        'eloquent.creating*',
        'eloquent.updating*',
        'eloquent.deleting*',
    ];

    public array $eventRightMapping = [
        'eloquent.creating' => self::CREATE_RIGHT,
        'eloquent.updating' => self::UPDATE_RIGHT,
        'eloquent.deleting' => self::DELETE_RIGHT,
    ];

    protected array $authorizableModels = [];

    public function __construct()
    {
        if (!Auth::check()) {
            throw new \Exception();
        }

        $user = Auth::user();

        // TODO: baciti ovu logiku sa interfaceom u config
        // $roles = $user->findRole('resource_access.account.roles');


        $this->authorizableModels = $this->getModelsWithAuthorizesWithJsonTrait();
    }

    protected function getModelsWithAuthorizesWithJsonTrait()
    {
        if (Cache::has(self::CACHE_PREFIX . 'models')) {
            return Cache::get(self::CACHE_PREFIX . 'models');
        }

        $paths = Config::get('asseco-voice.authorization.models_path');

        // TODO: šta sa ovim? ako skenira, valjda dohvati i to?

        $namespace = Config::get('asseco-voice.authorization.model_namespace');
        $models = [];

        foreach ($paths as $path) {

            $results = scandir($path);

            foreach ($results as $result) {
                if ($result === '.' or $result === '..') continue;

                $filename = $path . '/' . $result;

                if (is_dir($filename)) continue;

                $result = substr($result, 0, -4);

                if ($this->hasAuthorizesWithJsonTrait($namespace . $result)) {
                    $models[] = $result;
                }
            }
        }

        Cache::put(self::CACHE_PREFIX . 'models', $models, self::CACHE_TTL);
        return $models;
    }

    protected function hasAuthorizesWithJsonTrait($class)
    {
        $traits = class_uses($class);
        $authorizationTrait = Config::get('asseco-voice.authorization.trait_path');

        return in_array($authorizationTrait, $traits);
    }

    public function isModelAuthorizable(string $model)
    {
        return in_array($model, $this->authorizableModels);
    }

    /**
     * @param string $eventName
     * @throws \Exception
     */
    public function checkEventMapping(string $eventName)
    {
        $eventMapped = array_key_exists($eventName, $this->eventRightMapping);

        if (!$eventMapped) {
            throw new \Exception();
        }
    }

    public function getAuthValues(string $modelClass, string $right = self::READ_RIGHT): array
    {
        // foreachaj tipove (role, grupe, useri), pa unutar svakog foreachaj ako ih je više
        // smisli neki resolution strategy
        // baci u config


        $role = 'agent';

        // ako je sys-admin preskoči sve

        $resolvedModel = $this->resolveModel($modelClass);

        $rules = $this->resolveRules($role, $modelClass, $resolvedModel->id);

        if (!array_key_exists($right, $rules)) {
            Log::info("[JSONAuth] No '$right' rights found for $modelClass.");
            return [];
        }

        $wrapped = Arr::wrap($rules[$right]);

        Log::info("[JSONAuth] Found rules for '$right' right: " . print_r($wrapped, true));

        return $wrapped;

    }

    protected function resolveModel($model): Model
    {
        $cacheKey = self::CACHE_PREFIX . $model;

        if (Cache::has($cacheKey)) {
            Log::info("[JSONAuth] Resolving $model from cache.");
            return Cache::get($cacheKey);
        }

        $resolveFromDb = AuthorizationModel::where('name', $model)->first();

        if ($resolveFromDb) {
            Log::info("[JSONAuth] Resolved $model from DB. Adding to cache and returning.");
            Cache::put($cacheKey, $resolveFromDb, self::CACHE_TTL);
            return $resolveFromDb;
        }

        Log::info("[JSONAuth] Model $model is authorizable, but doesn't exist in DB yet. Creating...");
        $newModel = AuthorizationModel::create(['name' => $model]);

        Cache::put($cacheKey, $newModel, self::CACHE_TTL);
        return $newModel;
    }

    protected function resolveRules(string $role, string $modelClass, string $modelId): array
    {
        $cacheKey = self::CACHE_PREFIX . "role_{$role}_model_{$modelClass}";

        if (Cache::has($cacheKey)) {
            Log::info("[JSONAuth] Resolving $role rights for auth model $modelClass from cache.");
            return Cache::get($cacheKey);
        }

        $resolveFromDb = Authorization::where([
            'role'                   => $role,
            'authorization_model_id' => $modelId,
        ])->first();

        if ($resolveFromDb) {
            Log::info("[JSONAuth] Found $role rights for auth model $modelClass. Adding to cache and returning.");
            $decoded = json_decode($resolveFromDb->rules, true);
            Cache::put($cacheKey, $decoded);
            return $decoded;
        }

        // We still want to cache if there are no rules imposed to prevent going to DB unnecessarily
        Cache::put($cacheKey, []);
        return [];
    }
}
