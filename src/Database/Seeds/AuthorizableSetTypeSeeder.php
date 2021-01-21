<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\Database\Seeds;

use Asseco\JsonAuthorization\App\Models\AuthorizableSetType;
use Asseco\JsonAuthorization\App\Collections\AuthorizableSetCollection;
use Illuminate\Database\Seeder;

class AuthorizableSetTypeSeeder extends Seeder
{
    public function run(): void
    {
        $basicTypes = [
            [
                'name'        => AuthorizableSetCollection::VIRTUAL_ROLE,
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
