<?php

namespace Voice\JsonAuthorization\Authorization;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Voice\JsonAuthorization\App\AuthorizationManageType;
use Voice\JsonAuthorization\Exceptions\AuthorizationException;

class RightParser
{
    const CACHE_PREFIX = 'right_parser_';
    const CACHE_TTL = 60 * 60 * 24;

    const ABSOLUTE_RIGHTS = '*';

    const CREATE_RIGHT = 'create';
    const READ_RIGHT = 'read';
    const UPDATE_RIGHT = 'update';
    const DELETE_RIGHT = 'delete';

    /**
     * These should reflect the same events from events to listen
     * but without the wildcard.
     */
    public array $eventRightMapping = [
        'eloquent.creating' => self::CREATE_RIGHT,
        'eloquent.updating' => self::UPDATE_RIGHT,
        'eloquent.deleting' => self::DELETE_RIGHT,
    ];

    protected AuthenticatedUser  $authenticatedUser;
    protected AuthorizableModels $authorizableModels;
    protected RulesResolver      $rulesResolver;

    /**
     * RightParser constructor.
     * @param AuthenticatedUser $authenticatedUser
     * @param AuthorizableModels $authorizableModels
     * @param RulesResolver $rulesResolver
     */
    public function __construct(AuthenticatedUser $authenticatedUser, AuthorizableModels $authorizableModels, RulesResolver $rulesResolver)
    {
        $this->authorizableModels = $authorizableModels;
        $this->rulesResolver = $rulesResolver;
        $this->authenticatedUser = $authenticatedUser;
    }

    /**
     * @param string $modelClass
     * @param string $right
     * @return array|string[]
     * @throws \Exception
     */
    public function getAuthValues(string $modelClass, string $right = self::READ_RIGHT): array
    {
        if (!$this->authorizableModels->isModelAuthorizable($modelClass)) {
            Log::info("[Authorization] Model '$modelClass' does not implement AuthorizesWithJson trait. Skipping authorization...");
            return [self::ABSOLUTE_RIGHTS];
        }

        if (!$this->authenticatedUser->isLoggedIn()) {
            Log::info("[Authorization] You are logged out.");
            return [];
        }

        return $this->getMergedRules($modelClass, $right);
    }

    public function getManageTypes()
    {
        if (Cache::has(self::CACHE_PREFIX . 'manage_types')) {
            return Cache::get(self::CACHE_PREFIX . 'manage_types');
        }

        $manageTypes = AuthorizationManageType::all();

        Cache::put(self::CACHE_PREFIX . 'manage_types', $manageTypes, self::CACHE_TTL);
        return $manageTypes;
    }

    /**
     * @param string $manageType
     * @param array $authorizableSets
     * @return bool
     * @throws AuthorizationException
     */
    protected function hasAbsoluteRights(string $manageType, array $authorizableSets): bool
    {
        $rolesWithAbsoluteRights = $this->getRolesWithAbsoluteRights();

        if(!array_key_exists($manageType, $rolesWithAbsoluteRights)){
            return false;
        }

        foreach ($authorizableSets as $authorizableSet) {
            if (in_array($authorizableSet, $rolesWithAbsoluteRights[$manageType])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     * @throws AuthorizationException
     */
    protected function getRolesWithAbsoluteRights(): array
    {
        $absoluteRights = Config::get('asseco-authorization.absolute_rights');

        if (!is_array($absoluteRights)) {
            throw new AuthorizationException("Absolute rights are not configured correctly, this should be an array of values.");
        }

        return $absoluteRights;
    }

    /**
     * Events mapped within this class should reflect events registered within EloquentEvents class
     * (without the wildcard character)
     *
     * @param string $eventName
     * @throws AuthorizationException
     */
    public function checkEventMapping(string $eventName)
    {
        $eventMapped = array_key_exists($eventName, $this->eventRightMapping);

        if (!$eventMapped) {
            throw new AuthorizationException("Event '$eventName' is not mapped correctly.");
        }
    }

    /**
     * @param string $modelClass
     * @param string $right
     * @return array
     * @throws AuthorizationException
     */
    protected function getMergedRules(string $modelClass, string $right): array
    {
        /*
         * 'role' => ['role1', 'role2' ...],
         * 'group' => ['group1', 'group2' ...],
         * 'id' => 123,
         */
        $authorizableSets = Arr::wrap($this->authenticatedUser->user->getAuthorizableSets());

        /*
         * [
         *    [id => 1, name => 'role'],
         *    [id => 2, name => 'group'],
         *    [id => 3, name => 'id'],
         * ]
         */
        $manageTypes = $this->getManageTypes();

        /*
         * App\Workspace
         * ...
         */
        $resolvedModel = $this->authorizableModels->resolveAuthorizationModel($modelClass);

        $mergedRules = [];

        foreach ($manageTypes as $manageType) {
            if (!array_key_exists($manageType->name, $authorizableSets)) {
                Log::info("[Authorization] Type '{$manageType->name}' is missing within your User::getAuthorizableSets() method");
                continue;
            }

            /*
             * 'role' => ['role1', 'role2' ...],
             */
            $authorizableSet = Arr::wrap($authorizableSets[$manageType->name]);

            if ($this->hasAbsoluteRights($manageType->name, $authorizableSet)) {
                return [self::ABSOLUTE_RIGHTS];
            }

            /*
             * Dolazi iz Keycloaka
             * ['role1', 'role2' ...]
             */
            foreach ($authorizableSet as $role) {
                Log::info("[Authorization] Processing: {$manageType->name} - $role");

                $rules = $this->rulesResolver->resolveRules($manageType->id, $role, $modelClass, $resolvedModel, $right);

                if(array_key_exists(0, $rules) && $rules[0] === self::ABSOLUTE_RIGHTS){
                    return [self::ABSOLUTE_RIGHTS];
                }

                $mergedRules = $this->rulesResolver->mergeRules($mergedRules, $rules);
            }
        }

        Log::info("[Authorization] Merged rules: " . print_r($mergedRules, true));

        return $mergedRules;
    }
}
