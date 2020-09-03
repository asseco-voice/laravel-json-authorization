<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Voice\JsonAuthorization\App\AuthorizableModel;

class AuthorizableModelSeeder extends Seeder
{
    public function run(): void
    {
        // This seeder actually doesn't seed random data
        // but the classes which have trait already

        Cache::forget('authorization_models');
        AuthorizableModel::cached();
    }
}
