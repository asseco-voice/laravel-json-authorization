<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization\App\Collections;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Voice\JsonAuthorization\App\AuthorizableSetType;
use Voice\JsonAuthorization\App\AuthorizationRule;

class AuthorizableSetCollection extends Collection
{
    const VIRTUAL_ROLE = 'virtual-role';

    /**
     * Get collection of supported set types (authorizable_set_types table) and filter user set types to return only supported.
     * @param \Illuminate\Support\Collection $authorizableSetTypes
     * @return $this|Collection
     */
    public function filterSupported(\Illuminate\Support\Collection $authorizableSetTypes): Collection
    {
        $keys = $this->keysToDelete($authorizableSetTypes);
        $this->forget($keys);

        return $this;
    }

    /**
     * Append virtual role to already existing user set type collection.
     * It will also create role in the DB (authorizable_set_types table) if it doesn't exist.
     * @param \Illuminate\Support\Collection $authorizableSetTypes
     * @return $this|Collection
     */
    public function appendVirtualRole(\Illuminate\Support\Collection $authorizableSetTypes): Collection
    {
        if (!$authorizableSetTypes->contains(self::VIRTUAL_ROLE)) {
            $this->createVirtualRole();
        }

        $this->put(self::VIRTUAL_ROLE, Arr::wrap(Config::get('asseco-authorization.universal_role')));

        return $this;
    }

    /**
     * Transform output to resemble authorization rules since at this point user authorizable sets are still unresolved
     * but we need a way to store it in the cache in the same format like authorization rules even if it is not found
     * in the DB (to prevent going to DB unnecessarily next time the same set value arrives).
     * @return AuthorizableSetCollection
     */
    public function toAuthorizationRuleFormat(): AuthorizableSetCollection
    {
        $prepared = new AuthorizableSetCollection();

        $authorizableSets = $this->all();
        $setTypes = AuthorizableSetType::cached();

        foreach ($authorizableSets as $authorizableSetType => $authorizableSetValues) {
            $authorizableSetValues = Arr::wrap($authorizableSetValues);
            $authorizableSetTypeId = $setTypes->firstWhere('name', $authorizableSetType)['id'];

            foreach ($authorizableSetValues as $authorizableSetValue) {
                $prepared->push(AuthorizationRule::prepare($authorizableSetTypeId, $authorizableSetValue));
            }
        }

        return $prepared;
    }

    protected function keysToDelete(\Illuminate\Support\Collection $authorizableSetTypes): array
    {
        return $this->reject(function ($value, $key) use ($authorizableSetTypes) {
            return $authorizableSetTypes->contains($key);
        })->keys()->toArray();
    }

    protected function createVirtualRole(): void
    {
        AuthorizableSetType::query()->create([
            'name'        => self::VIRTUAL_ROLE,
            'description' => "Virtual role which doesn't and shouldn't exist in authentication service. Attached automatically to every user.",
        ]);

        AuthorizableSetType::reCache();
    }
}
