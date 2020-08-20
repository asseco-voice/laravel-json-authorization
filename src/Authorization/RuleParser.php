<?php

namespace Voice\JsonAuthorization\Authorization;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Voice\JsonAuthorization\Exceptions\AuthorizationException;

class RuleParser
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
    protected RuleResolver       $ruleResolver;

    /**
     * RightParser constructor.
     * @param AuthenticatedUser $authenticatedUser
     * @param AuthorizableModels $authorizableModels
     * @param RuleResolver $ruleResolver
     */
    public function __construct(AuthenticatedUser $authenticatedUser, AuthorizableModels $authorizableModels, RuleResolver $ruleResolver)
    {
        $this->authorizableModels = $authorizableModels;
        $this->ruleResolver = $ruleResolver;
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
            Log::info("[Authorization] Model '$modelClass' does not implement AuthorizesWithJson trait (or you forgot to flush the cache). Skipping authorization...");
            return [self::ABSOLUTE_RIGHTS];
        }

        if (!$this->authenticatedUser->isLoggedIn()) {
            Log::info("[Authorization] You are logged out.");
            return [];
        }

        return $this->getMergedRules($modelClass, $right);
    }

    /**
     * @param string $modelClass
     * @param string $right
     * @return array
     * @throws AuthorizationException
     */
    protected function getMergedRules(string $modelClass, string $right): array
    {
        $authorizableSets = Arr::wrap($this->authenticatedUser->user->getAuthorizableSets());
        $authorizableSetTypes = AuthorizableSetTypes::getAuthorizableSetTypes();
        $authorizableModelId = $this->authorizableModels->resolveAuthorizableModelId($modelClass);

        $mergedRules = $this->mergeRules($authorizableSets, $authorizableSetTypes, $authorizableModelId, $modelClass, $right);

        Log::info("[Authorization] Merged rules: " . print_r($mergedRules, true));

        return $mergedRules;
    }

    /**
     * @param $authorizableSetTypes
     * @param array $authorizableSets
     * @param string $modelClass
     * @param int $authorizableModelId
     * @param string $right
     * @return array
     * @throws AuthorizationException
     */
    protected function mergeRules(array $authorizableSets, array $authorizableSetTypes, int $authorizableModelId, string $modelClass, string $right): array
    {
        $mergedRules = [];

        foreach ($authorizableSetTypes as $authorizableSetType => $authorizableSetTypeId) {
            if (!array_key_exists($authorizableSetType, $authorizableSets)) {
                Log::info("[Authorization] Type '{$authorizableSetType}' is missing within your User::getAuthorizableSets() method");
                continue;
            }

            $authorizableSet = Arr::wrap($authorizableSets[$authorizableSetType]);

            if ($this->hasAbsoluteRights($authorizableSetType, $authorizableSet)) {
                return [self::ABSOLUTE_RIGHTS];
            }

            foreach ($authorizableSet as $authorizableValue) {
                Log::info("[Authorization] Processing: {$authorizableSetType} - $authorizableValue");

                $rules = $this->ruleResolver->resolveRules($authorizableSetType, $authorizableSetTypeId, $authorizableValue, $modelClass, $authorizableModelId, $right);

                if (array_key_exists(0, $rules) && $rules[0] === self::ABSOLUTE_RIGHTS) {
                    return [self::ABSOLUTE_RIGHTS];
                }

                $mergedRules = $this->ruleResolver->mergeRules($mergedRules, $rules);
            }
        }

        return $mergedRules;
    }

    /**
     * @param string $authorizableSetType
     * @param array $authorizableSets
     * @return bool
     * @throws AuthorizationException
     */
    protected function hasAbsoluteRights(string $authorizableSetType, array $authorizableSets): bool
    {
        $authorizableSetsWithAbsoluteRights = $this->getAuthorizableSetsWithAbsoluteRights();

        if (!array_key_exists($authorizableSetType, $authorizableSetsWithAbsoluteRights)) {
            return false;
        }

        foreach ($authorizableSets as $authorizableSet) {
            if (in_array($authorizableSet, $authorizableSetsWithAbsoluteRights[$authorizableSetType])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     * @throws AuthorizationException
     */
    protected function getAuthorizableSetsWithAbsoluteRights(): array
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
