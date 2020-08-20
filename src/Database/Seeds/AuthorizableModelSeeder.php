<?php

namespace Voice\JsonAuthorization\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Voice\JsonAuthorization\App\AuthorizableModel;

class AuthorizableModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // This seeder actually doesn't seed random data
        // but the classes which have trait already

        Cache::forget('authorization_models');
        $models = AuthorizableModel::getCached();
        $data = [];

        foreach ($models as $model) {
            $data[] = ['name' => $model];
        }

        AuthorizableModel::insert($data);
    }
}
