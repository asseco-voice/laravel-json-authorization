<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\Database\Seeds;

use Asseco\JsonAuthorization\App\Models\AuthorizableModel;
use Illuminate\Database\Seeder;

class AuthorizableModelSeeder extends Seeder
{
    public function run(): void
    {
        // This seeder actually doesn't seed random data
        // but the classes which have trait already

        AuthorizableModel::invalidateCache();
        $modelsWithTrait = AuthorizableModel::cached();

        $models = [];
        foreach ($modelsWithTrait as $model) {
            $models[] = ['name' => $model['name']];
        }

        AuthorizableModel::query()->upsert($models, 'name');
    }
}
