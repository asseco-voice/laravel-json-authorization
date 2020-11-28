<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\Database\Seeds;

use Asseco\JsonAuthorization\App\AuthorizableSetType;
use Illuminate\Database\Seeder;

class AuthorizableSetTypeSeeder extends Seeder
{
    public function run(): void
    {
        $basicTypes = [
            [
                'name'        => config('asseco-authorization.universal_role'),
                'description' => "Virtual role which doesn't and shouldn't exist in authentication service. Attached automatically to every user.",
            ],
            [
                'name'        => 'roles',
                'description' => 'Authentication service roles',
            ],
            [
                'name'        => 'groups',
                'description' => 'Authentication service groups',
            ],
            [
                'name'        => 'id',
                'description' => 'Authentication service ID',
            ],
        ];

        AuthorizableSetType::query()->upsert($basicTypes, 'name');
    }
}
