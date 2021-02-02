<?php

namespace Asseco\JsonAuthorization\Tests\Unit\Authorization;

use Asseco\JsonAuthorization\App\Models\AuthorizableModel;
use Asseco\JsonAuthorization\App\Models\AuthorizableSetType;
use Asseco\JsonAuthorization\App\Models\AuthorizationRule;
use Asseco\JsonAuthorization\Tests\TestCase;
use Asseco\JsonAuthorization\Tests\TestUser;

class AuthorizationRuleTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config(['asseco-authorization.models_path' => [
            __DIR__ . '/../../../' => 'Asseco\\JsonAuthorization\\Tests\\',
        ]]);
    }

    /** @test */
    public function has_authorizable_set_type_relation()
    {
        $setType = AuthorizableSetType::factory()->create();

        $rule = AuthorizationRule::factory()->create([
            'authorizable_set_type_id' => $setType->id,
        ]);

        $this->assertEquals($setType->name, $rule->authorizableSetType->name);
    }

    /** @test */
    public function has_authorizable_model_relation()
    {
        $model = AuthorizableModel::factory()->create([
            'name' => TestUser::class,
        ]);

        $rule = AuthorizationRule::factory()->create([
            'authorizable_model_id' => $model->id,
        ]);

        $this->assertEquals($model->name, $rule->authorizableModel->name);
    }
}
