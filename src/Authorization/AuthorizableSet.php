<?php

namespace Voice\JsonAuthorization\Authorization;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Voice\JsonAuthorization\App\AuthorizableSetType;
use Voice\JsonAuthorization\App\Collections\AuthorizableSetCollection;
use Voice\JsonAuthorization\App\Contracts\AuthorizationInterface;
use Voice\JsonAuthorization\Exceptions\AuthorizationException;

class AuthorizableSet
{
    public static function unresolvedRules(): AuthorizableSetCollection
    {
        $user = Auth::user();

        if (!$user) {
            Log::info("[Authorization] You are logged out.");
            return new AuthorizableSetCollection();
        }

        if (!$user instanceof AuthorizationInterface) {
            throw new AuthorizationException("User model must implement AuthorizesUsers interface.");
        }

        $authorizableSetTypes = AuthorizableSetType::cached()->pluck('name');

        return self::getUserAuthorizableSets($user)
            ->filterSupported($authorizableSetTypes)
            ->appendVirtualRole($authorizableSetTypes)
            ->toAuthorizationRuleFormat();
    }

    protected static function getUserAuthorizableSets(AuthorizationInterface $user): AuthorizableSetCollection
    {
        return new AuthorizableSetCollection($user->getAuthorizableSets());
    }
}
