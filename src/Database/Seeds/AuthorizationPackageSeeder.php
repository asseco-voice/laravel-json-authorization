<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization\Database\Seeds;

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
