<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization\Database\Seeds;

use Illuminate\Database\Seeder;

class AuthorizationSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            AuthorizableModelSeeder::class,
            AuthorizableSetTypeSeeder::class,
            AuthorizationRuleSeeder::class,
        ]);
    }
}
