<?php

namespace Asseco\JsonAuthorization\Tests\Unit\Authorization;

use Asseco\JsonAuthorization\App\Contracts\AuthorizableModel;
use Asseco\JsonAuthorization\App\Contracts\AuthorizationRule;
use Asseco\JsonAuthorization\Tests\TestCase;
use Asseco\JsonAuthorization\Tests\TestUser;
use Exception;

class AuthorizableModelTest extends TestCase
{
    protected AuthorizableModel $authorizableModel;
    protected AuthorizationRule $authorizationRule;
    
    public function setUp(): void
    {
        parent::setUp();

        config(['asseco-authorization.models_path' => [
            __DIR__ . '/../../../' => 'Asseco\\JsonAuthorization\\Tests\\',
        ]]);

        $this->authorizableModel = app(AuthorizableModel::class);
        $this->authorizationRule = app(AuthorizationRule::class);
    }

    /** @test */
    public function syncs_authorizable_models_independently_of_what_you_try_to_add()
    {
        // There is only one model in given config location (in setUp method)
        // which has Authorizable trait. Package forces to always have a realistic
        // state of authorizable models, so trying to add models at random will
        // always re-scan models and write to DB only models which actually do have
        // the trait, ignoring others.

        $this->authorizableModel::factory()->create();
        $this->authorizableModel::factory()->create(['name' => 'SomeOther::class']);
        $this->authorizableModel::factory()->count(5)->create();

        $this->assertCount(1, $this->authorizableModel::all());
    }

    /** @test */
    public function fails_to_find_authorizable_model_if_models_path_is_not_configured_correctly()
    {
        config(['asseco-authorization.models_path' => []]);

        $this->authorizableModel::factory()->create([
            'name' => TestUser::class,
        ]);

        $this->assertCount(0, $this->authorizableModel::all());
    }

    /** @test */
    public function has_rules()
    {
        $model = $this->authorizableModel::factory()->create();

        $this->authorizationRule::factory()->count(5)->create([
            'authorizable_model_id' => $model->id,
        ]);

        $this->assertCount(5, $model->rules);
    }

    /** @test */
    public function checks_if_model_is_authorizable()
    {
        $this->assertTrue($this->authorizableModel::isAuthorizable(TestUser::class));
        $this->assertFalse($this->authorizableModel::isAuthorizable('SomeOther::class'));
    }

    /** @test */
    public function returns_id_of_authorizable_models()
    {
        $this->assertEquals(1, $this->authorizableModel::getIdFor(TestUser::class));
    }

    /** @test */
    public function throws_exception_if_fetching_id_of_non_authorizable_model()
    {
        $this->expectException(Exception::class);

        $this->authorizableModel::getIdFor('SomeOther::class');
    }
}
