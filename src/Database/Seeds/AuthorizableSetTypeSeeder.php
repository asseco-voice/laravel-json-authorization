<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization\Database\Seeds;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Voice\JsonAuthorization\App\AuthorizableSetType;

class AuthorizableSetTypeSeeder extends Seeder
{
    public function run(): void
    {
        $basicTypes = [
            [
                'name'        => 'virtual-role',
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

        $now = Carbon::now();

        foreach ($basicTypes as $basicType) {
            AuthorizableSetType::query()->updateOrCreate(
                ['name' => $basicType['name']],
                [
                    'description' => $basicType['description'],
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
        }

    }
}
