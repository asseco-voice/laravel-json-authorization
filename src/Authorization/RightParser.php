<?php

namespace Voice\JsonAuthorization\Authorization;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Voice\JsonAuthorization\Exceptions\AuthorizationException;

class RightParser
{
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

        $authorizableSets = $this->authenticatedUser->user->getAuthorizableSets();

        if ($this->hasAbsoluteRights($authorizableSets)) {
            return [self::ABSOLUTE_RIGHTS];
        }

        $resolvedModel = $this->authorizableModels->resolveAuthorizationModel($modelClass);

        $mergedRules = [];
        foreach ($authorizableSets as $role) {
            $rules = $this->rulesResolver->resolveRules($role, $modelClass, $resolvedModel, $right);

            $mergedRules = $this->rulesResolver->mergeRules($mergedRules, $rules);
        }

        return $mergedRules;
    }

    /**
     * @param array $authorizableSets
     * @return bool
     * @throws AuthorizationException
     */
    protected function hasAbsoluteRights(array $authorizableSets): bool
    {
        $rolesWithAbsoluteRights = $this->getRolesWithAbsoluteRights();

        foreach ($authorizableSets as $authorizableSet) {
            if (in_array($authorizableSet, $rolesWithAbsoluteRights)) {
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
}
