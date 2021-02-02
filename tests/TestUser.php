<?php

namespace Asseco\JsonAuthorization\Tests;

use Asseco\JsonAuthorization\App\Contracts\AuthorizationInterface;
use Asseco\JsonAuthorization\App\Traits\Authorizable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class TestUser extends Authenticatable implements AuthorizationInterface
{
    use Authorizable;

    public function getAuthorizableSets(): array
    {
        return [
            'roles'  => [
                'role1',
                'role2',
            ],
            'groups' => [
                'group1',
                'group2',
            ],
        ];
    }
}
