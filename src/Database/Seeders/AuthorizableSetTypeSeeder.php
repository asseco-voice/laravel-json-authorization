<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\Database\Seeders;

use Asseco\JsonAuthorization\App\Collections\AuthorizableSetCollection;
use Asseco\JsonAuthorization\App\Models\AuthorizableSetType;
use Illuminate\Database\Seeder;

class AuthorizableSetTypeSeeder extends Seeder
{
    public function run(): void
    {
        $basicTypes = [
            [
                'name'        => AuthorizableSetCollection::VIRTUAL_SET_TYPE,
                'description' => 'Virtual set type attached automatically to every user.',
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
