<?php

namespace Voice\JsonAuthorization\Authorization;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Throwable;
use Voice\JsonAuthorization\App\AuthorizableSetType;
use Voice\JsonAuthorization\Exceptions\AuthorizationException;

class AuthorizableSets
{
    const VIRTUAL_ROLE = 'virtual-role';

    protected AuthenticatedUser $authenticatedUser;
    protected Collection $authorizableSetTypes;

    /**
     * AuthorizableSet constructor.
     * @param AuthenticatedUser $authenticatedUser
     * @param Collection $authorizableSetTypes
     */
    public function __construct(AuthenticatedUser $authenticatedUser, Collection $authorizableSetTypes)
    {
        $this->authenticatedUser = $authenticatedUser;
        $this->authorizableSetTypes = $authorizableSetTypes;
    }

    /**
     * @return array
     * @throws Throwable
     */
    public function get(): Collection
    {
        $authorizableSets = $this->authenticatedUser->getAuthorizableSets();

        $this->filterSupported($authorizableSets);
        $this->attachVirtualRole($authorizableSets);

        return $this->initAuthorizableSetCollection($authorizableSets);
    }

    /**
     * From given user set types, filter only those which are supported (present within authorizable_set_types table).
     * @param array $authorizableSets
     * @throws Throwable
     */
    protected function filterSupported(array &$authorizableSets): void
    {
        foreach ($authorizableSets as $authorizableSetType => $authorizableSetValues) {

            $typeSupported = $this->authorizableSetTypes->pluck('name')->contains($authorizableSetType);

            if (!$typeSupported) {
                Log::info("[Authorization] Authorizable set type '{$authorizableSetType}' is used in your User model, but is missing in 'authorizable_set_types' table (or you forgot to flush the cache).");
                unset($authorizableSets[$authorizableSetType]);
            }
        }
    }

    /**
     * Support for virtual role which will be attached to every user. Check readme for more details.
     * @param array $authorizableSets
     * @throws Throwable
     */
    protected function attachVirtualRole(array &$authorizableSets): void
    {
        if (!$this->authorizableSetTypes->pluck('name')->contains(self::VIRTUAL_ROLE)) {
            AuthorizableSetType::create([
                'name'        => self::VIRTUAL_ROLE,
                'description' => "Virtual role which doesn't and shouldn't exist in authentication service. Attached automatically to every user."
            ]);
            $this->authorizableSetTypes = AuthorizableSetType::reCache();
        }

        $authorizableSets[self::VIRTUAL_ROLE] = Config::get('asseco-authorization.universal_role');
    }

    /**
     * @param array $authorizableSets
     * @return Collection
     * @throws Throwable
     */
    protected function initAuthorizableSetCollection(array $authorizableSets): Collection
    {
        $authorizableSetCollection = new Collection();
        $authorizableSetTypes = $this->authorizableSetTypes->pluck('id', 'name')->toArray();

        foreach ($authorizableSets as $type => $values) {

            if (!array_key_exists($type, $authorizableSetTypes)) {
                throw new AuthorizationException("Something went wrong...");
            }

            $id = $authorizableSetTypes[$type];
            $authorizableSetCollection->add(new AuthorizableSet($id, $type, Arr::wrap($values)));
        }

        return $authorizableSetCollection;
    }
}
