<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\Authorization;

use Asseco\JsonAuthorization\App\Collections\AuthorizableSetCollection;
use Asseco\JsonAuthorization\App\Contracts\AuthorizationInterface;
use Asseco\JsonAuthorization\Exceptions\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthorizableSet
{
    /**
     * @return AuthorizableSetCollection
     * @throws AuthorizationException
     */
    public static function unresolvedRules(): AuthorizableSetCollection
    {
        $user = Auth::user();

        if (!$user) {
            Log::info('[Authorization] You are logged out.');

            return new AuthorizableSetCollection();
        }

        if (!$user instanceof AuthorizationInterface) {
            throw new AuthorizationException('User model must implement ' . AuthorizationInterface::class);
        }

        return self::getUserAuthorizableSets($user)
            ->filterByExistingTypes()
            ->createVirtualRole()
            ->appendVirtualRole()
            ->toAuthorizationRuleFormat();
    }

    protected static function getUserAuthorizableSets(AuthorizationInterface $user): AuthorizableSetCollection
    {
        return new AuthorizableSetCollection($user->getAuthorizableSets());
    }
}
