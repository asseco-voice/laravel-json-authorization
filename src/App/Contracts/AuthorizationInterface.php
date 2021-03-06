<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\App\Contracts;

interface AuthorizationInterface
{
    /**
     * List of things to authorize by. Check readme for more details.
     *
     * @return array
     */
    public function getAuthorizableSets(): array;
}
