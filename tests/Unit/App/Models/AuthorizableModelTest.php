<?php

namespace Asseco\JsonAuthorization\Tests\Unit\Authorization;

use Asseco\JsonAuthorization\App\Models\AuthorizableModel;
use Asseco\JsonAuthorization\App\Models\AuthorizationRule;
use Asseco\JsonAuthorization\Tests\TestCase;
use Asseco\JsonAuthorization\Tests\TestUser;
use Exception;

class AuthorizableModelTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config(['asseco-authorization.models_path' => [
            __DIR__ . '/../../../' => 'Asseco\\JsonAuthorization\\Tests\\',
        ]]);
    }

    /** @test */
    public function has_rules()
    {
        $model = AuthorizableModel::factory()->create();

        AuthorizationRule::factory()->count(5)->create([
            'authorizable_model_id' => $model->id,
        ]);

        $this->assertCount(5, $model->rules);
    }

    /** @test */
    public function checks_if_model_is_authorizable()
    {
        $this->assertTrue(AuthorizableModel::isAuthorizable(TestUser::class));
        $this->assertFalse(AuthorizableModel::isAuthorizable('SomeOther::class'));
    }

    /** @test */
    public function returns_id_of_authorizable_models()
    {
        $this->assertEquals(1, AuthorizableModel::getIdFor(TestUser::class));
    }

    /** @test */
    public function throws_exception_if_fetching_id_of_non_authorizable_model()
    {
        $this->expectException(Exception::class);

        AuthorizableModel::getIdFor('SomeOther::class');
    }
}
