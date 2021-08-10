<?php

namespace Asseco\JsonAuthorization\Tests\Unit\Authorization;

use Asseco\JsonAuthorization\App\Contracts\AuthorizableModel;
use Asseco\JsonAuthorization\App\Contracts\AuthorizableSetType;
use Asseco\JsonAuthorization\App\Contracts\AuthorizationRule;
use Asseco\JsonAuthorization\Tests\TestCase;
use Asseco\JsonAuthorization\Tests\TestUser;
use Exception;
use Illuminate\Support\Arr;

class AuthorizationRuleTest extends TestCase
{
    protected AuthorizableModel $authorizableModel;
    protected AuthorizableSetType $authorizableSetType;
    protected AuthorizationRule $authorizationRule;
    
    public function setUp(): void
    {
        parent::setUp();

        config(['asseco-authorization.models_path' => [
            __DIR__ . '/../../../' => 'Asseco\\JsonAuthorization\\Tests\\',
        ]]);

        $this->authorizableModel = app(AuthorizableModel::class);
        $this->authorizableSetType = app(AuthorizableSetType::class);
        $this->authorizationRule = app(AuthorizationRule::class);
    }

    /** @test */
    public function has_authorizable_set_type_relation()
    {
        $setType = $this->authorizableSetType::factory()->create();

        $rule = $this->authorizationRule::factory()->create([
            'authorizable_set_type_id' => $setType->id,
        ]);

        $this->assertEquals($setType->name, $rule->authorizableSetType->name);
    }

    /** @test */
    public function has_authorizable_model_relation()
    {
        $model = $this->authorizableModel::factory()->create([
            'name' => TestUser::class,
        ]);

        $rule = $this->authorizationRule::factory()->create([
            'authorizable_model_id' => $model->id,
        ]);

        $this->assertEquals($model->name, $rule->authorizableModel->name);
    }

    /** @test */
    public function fails_retrieving_from_cache_for_non_authorizable_model()
    {
        $this->expectException(Exception::class);

        $this->authorizationRule::resolveRulesFor('SomeOther::class');
    }

    /** @test */
    public function resolves_existing_rule_if_it_matches_user_defined_roles()
    {
        $this->actingAs(new TestUser());

        $roleType = $this->authorizableSetType::factory()->create(['name' => 'roles']);

        $model = $this->authorizableModel::factory()->create(['name' => TestUser::class]);

        $this->authorizationRule::factory()->create([
            'authorizable_set_type_id' => $roleType->id,
            'authorizable_set_value'   => 'role1',
            'authorizable_model_id'    => $model->id,
            'rules'                    => json_encode([
                'read' => '*',
            ]),
        ]);

        $resolvedRules = $this->authorizationRule::resolveRulesFor(TestUser::class);

        $role1Rules = $resolvedRules->where('authorizable_set_value', 'role1')->first();
        $role2Rules = $resolvedRules->where('authorizable_set_value', 'role2')->first();

        $this->assertArrayHasKey('read', Arr::get($role1Rules, 'rules'));
        $this->assertArrayNotHasKey('read', Arr::get($role2Rules, 'rules'));
    }

    /** @test */
    public function resolves_multiple_existing_rules_if_it_matches_user_defined_roles()
    {
        $this->actingAs(new TestUser());

        $roleType = $this->authorizableSetType::factory()->create(['name' => 'roles']);

        $model = $this->authorizableModel::factory()->create(['name' => TestUser::class]);

        $this->authorizationRule::factory()->create([
            'authorizable_set_type_id' => $roleType->id,
            'authorizable_set_value'   => 'role1',
            'authorizable_model_id'    => $model->id,
            'rules'                    => json_encode([
                'read' => '*',
            ]),
        ]);

        $this->authorizationRule::factory()->create([
            'authorizable_set_type_id' => $roleType->id,
            'authorizable_set_value'   => 'role2',
            'authorizable_model_id'    => $model->id,
            'rules'                    => json_encode([
                'write' => '*',
            ]),
        ]);

        $resolvedRules = $this->authorizationRule::resolveRulesFor(TestUser::class);

        $role1Rules = $resolvedRules->where('authorizable_set_value', 'role1')->first();
        $role2Rules = $resolvedRules->where('authorizable_set_value', 'role2')->first();

        $this->assertArrayHasKey('read', Arr::get($role1Rules, 'rules'));
        $this->assertArrayHasKey('write', Arr::get($role2Rules, 'rules'));
    }

    /** @test */
    public function formats_data_for_writing_to_cache()
    {
        $expected = [
            'authorizable_set_type_id' => 1,
            'authorizable_set_value'   => 'role1',
            'rules'                    => [
                'test' => 'test',
            ],
        ];

        $actual = $this->authorizationRule::format(1, 'role1', ['test' => 'test']);

        $this->assertEquals($expected, $actual);
    }
}
