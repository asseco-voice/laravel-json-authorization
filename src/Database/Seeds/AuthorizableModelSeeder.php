<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\Database\Seeds;

use Illuminate\Database\Seeder;
use Asseco\JsonAuthorization\App\AuthorizableModel;

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
