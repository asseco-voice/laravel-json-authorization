<?php

namespace Asseco\JsonAuthorization\Tests;

use Asseco\JsonAuthorization\App\Contracts\AuthorizationInterface;
use Illuminate\Foundation\Auth\User as Authenticatable;

class TestUser extends Authenticatable implements AuthorizationInterface
{
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
