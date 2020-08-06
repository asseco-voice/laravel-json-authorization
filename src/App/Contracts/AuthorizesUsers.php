<?php

namespace Voice\JsonAuthorization\App\Contracts;

interface AuthorizesUsers
{
    /**
     * List of things to authorize by. This can be
     * an array of roles, or array of arrays where each
     * inner array would be a set of authorizable claims.
     *
     * This list MUST come from the DB or some other external
     * source. It should not be hardcoded.
     *
     * I.e. single array:
     * [ 'role1', 'role2' ... ]
     *
     * I.e. nested array:
     * [
     *    [ 'role1', 'role2' ...],
     *    [ 'group1, 'group2' ...],
     *    [ 'something_else1, 'something_else2' ...],
     * ]
     *
     * @return array
     */
    public function getAuthorizableSets(): array;
}
