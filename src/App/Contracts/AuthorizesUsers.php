<?php

namespace Voice\JsonAuthorization\App\Contracts;

interface AuthorizesUsers
{
    /**
     * List of things to authorize by. Check readme for more details
     *
     * @return array
     */
    public function getAuthorizableSets(): array;
}
