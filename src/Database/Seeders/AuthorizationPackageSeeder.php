<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\Database\Seeders;

use Illuminate\Database\Seeder;

class AuthorizationPackageSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AuthorizableModelSeeder::class,
            AuthorizableSetTypeSeeder::class,
            AuthorizationRuleSeeder::class,
        ]);
    }
}
