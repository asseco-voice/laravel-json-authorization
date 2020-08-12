<?php

namespace Voice\JsonAuthorization\App\Contracts;

interface AuthorizesUsers
{
    /**
     * List of things to authorize by. Keys given here MUST
     * resemble names from authorization_manage_types table.
     *
     * Examples:
     *
     *   - a single role (will load permissions for a given role).
     *   Example: 'role' => 'admin'
     *
     *   - array of roles (will load merge of permissions for given roles).
     *   Example: 'role' => ['role1', 'role2']
     *
     *   - multi-dimensional array of authorizable properties (will load merge of permissions for given roles).
     *   Example:
     *   [
     *      'role' => [...],
     *      'group' => [...],
     *      'id' => ...,
     *   ]
     *
     * @return array
     */
    public function getAuthorizableSets(): array;
}
