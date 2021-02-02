<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\App\Collections;

use Asseco\JsonAuthorization\App\Models\AuthorizableSetType;
use Asseco\JsonAuthorization\App\Models\AuthorizationRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class AuthorizableSetCollection extends Collection
{
    const VIRTUAL_SET_TYPE = 'virtual-set-type';

    /**
     * Filter collection based on existing authorizable set types
     *
     * Having collection:
     * 'roles' => [...],
     * 'groups' => [...]
     *
     * And set types: ['roles']
     *
     * Resulting collection will be:
     * 'roles' => [...]
     *
     * @return $this|Collection
     */
    public function filterByExistingTypes(): Collection
    {
        $authorizableSetTypes = AuthorizableSetType::cached()->pluck('name');

        $keys = $this->keysToDelete($authorizableSetTypes);
        $this->forget($keys);

        return $this;
    }

    /**
     * Create a virtual set type in DB if it doesn't already exist in authorizable set types.
     *
     * @return $this|Collection
     */
    public function createVirtualRole(): Collection
    {
        $authorizableSetTypes = AuthorizableSetType::cached()->pluck('name');

        if ($authorizableSetTypes->contains(self::VIRTUAL_SET_TYPE)) {
            return $this;
        }

        AuthorizableSetType::query()->updateOrCreate(['name' => self::VIRTUAL_SET_TYPE],
            [
                'description' => 'Virtual set type attached automatically to every user.',
            ]);

        AuthorizableSetType::reCache();

        return $this;
    }

    /**
     * Append virtual role to a collection
     *
     * @return $this|Collection
     */
    public function appendVirtualRole(): Collection
    {
        $this->put(self::VIRTUAL_SET_TYPE, Arr::wrap(config('asseco-authorization.virtual_role')));

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
        $formatted = new AuthorizableSetCollection();

        $authorizableSets = $this->all();
        $setTypes = AuthorizableSetType::cached();

        foreach ($authorizableSets as $setType => $setValues) {

            $setTypeId = Arr::get($setTypes->firstWhere('name', $setType), 'id');

            if (!$setTypeId) {
                continue;
            }

            foreach (Arr::wrap($setValues) as $setValue) {
                $formatted->push(AuthorizationRule::prepare($setTypeId, $setValue));
            }
        }

        return $formatted;
    }

    protected function keysToDelete(\Illuminate\Support\Collection $authorizableSetTypes): array
    {
        return $this->reject(function ($value, $key) use ($authorizableSetTypes) {
            return $authorizableSetTypes->contains($key);
        })->keys()->toArray();
    }
}
