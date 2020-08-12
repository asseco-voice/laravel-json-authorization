<?php

namespace Voice\JsonAuthorization\Database\Seeds;

use Illuminate\Database\Seeder;
use Voice\JsonAuthorization\App\AuthorizationManageType;

class AuthorizationManageTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['name' => 'roles'],
            ['name' => 'groups'],
            ['name' => 'id'],
        ];

        AuthorizationManageType::insert($data);
    }
}
