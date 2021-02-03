<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\Authorization;

use Asseco\JsonAuthorization\App\Collections\AuthorizableSetCollection;
use Asseco\JsonAuthorization\App\Contracts\AuthorizationInterface;
use Asseco\JsonAuthorization\Exceptions\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserAuthorizableSet
{
    /**
     * Take user authorizable sets, keep only ones whose types exists in DB (authorizable_set_types table),
     * append virtual role to it and format in such a way that each set value (concrete role/group)
     * is separate, but at this point we are still not going to DB to see whether those rules
     * actually exist. This is preparing data for assembling a query down the line.
     *
     * @return AuthorizableSetCollection
     * @throws AuthorizationException
     */
    public static function prepare(): AuthorizableSetCollection
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
