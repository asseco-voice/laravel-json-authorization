<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\Database\Seeders;

use Asseco\JsonAuthorization\App\Collections\AuthorizableSetCollection;
use Asseco\JsonAuthorization\App\Contracts\AuthorizableSetType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AuthorizableSetTypeSeeder extends Seeder
{
    public function run(): void
    {
        /** @var AuthorizableSetType $authorizableSetType */
        $authorizableSetType = app(AuthorizableSetType::class);

        $basicTypes = [
            [
                'name'        => AuthorizableSetCollection::VIRTUAL_SET_TYPE,
                'description' => 'Virtual set type attached automatically to every user.',
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

        if(config('asseco-authorization.migrations.uuid')){
            foreach ($basicTypes as &$basicType) {
                $basicType['id'] = Str::uuid();
            }
        }

        $authorizableSetType::query()->upsert($basicTypes, 'name');
    }
}
