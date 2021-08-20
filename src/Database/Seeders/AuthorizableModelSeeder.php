<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\Database\Seeders;

use Asseco\JsonAuthorization\App\Contracts\AuthorizableModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AuthorizableModelSeeder extends Seeder
{
    public function run(): void
    {
        // This seeder actually doesn't seed random data
        // but the classes which have trait already

        /** @var AuthorizableModel $authorizableModel */
        $authorizableModel = app(AuthorizableModel::class);

        $authorizableModel::invalidateCache();
        $modelsWithTrait = $authorizableModel::cached();

        $models = [];
        foreach ($modelsWithTrait as $model) {
            $models[] = ['name' => $model['name']];
        }

        if (config('asseco-authorization.migrations.uuid')) {
            foreach ($models as &$model) {
                $model['id'] = Str::uuid();
            }
        }

        $authorizableModel::query()->upsert($models, 'name');
    }
}
