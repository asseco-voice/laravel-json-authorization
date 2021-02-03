<?php

namespace Asseco\JsonAuthorization\Tests\Unit\Authorization;

use Asseco\JsonAuthorization\App\Models\AuthorizableModel;
use Asseco\JsonAuthorization\App\Models\AuthorizableSetType;
use Asseco\JsonAuthorization\App\Models\AuthorizationRule;
use Asseco\JsonAuthorization\Tests\TestCase;
use Asseco\JsonAuthorization\Tests\TestUser;
use Exception;
use Illuminate\Support\Arr;

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

    /** @test */
    public function fails_retrieving_from_cache_for_non_authorizable_model()
    {
        $this->expectException(Exception::class);

        AuthorizationRule::resolveRulesFor('SomeOther::class');
    }

    /** @test */
    public function resolves_existing_rule_if_it_matches_user_defined_roles()
    {
        $this->actingAs(new TestUser());

        $roleType = AuthorizableSetType::factory()->create(['name' => 'roles']);

        $model = AuthorizableModel::factory()->create(['name' => TestUser::class]);

        AuthorizationRule::factory()->create([
            'authorizable_set_type_id' => $roleType->id,
            'authorizable_set_value'   => 'role1',
            'authorizable_model_id'    => $model->id,
            'rules'                    => json_encode([
                'read' => '*',
            ]),
        ]);

        $resolvedRules = AuthorizationRule::resolveRulesFor(TestUser::class);

        $role1Rules = $resolvedRules->where('authorizable_set_value', 'role1')->first();
        $role2Rules = $resolvedRules->where('authorizable_set_value', 'role2')->first();

        $this->assertArrayHasKey('read', Arr::get($role1Rules, 'rules'));
        $this->assertArrayNotHasKey('read', Arr::get($role2Rules, 'rules'));
    }

    /** @test */
    public function resolves_multiple_existing_rules_if_it_matches_user_defined_roles()
    {
        $this->actingAs(new TestUser());

        $roleType = AuthorizableSetType::factory()->create(['name' => 'roles']);

        $model = AuthorizableModel::factory()->create(['name' => TestUser::class]);

        AuthorizationRule::factory()->create([
            'authorizable_set_type_id' => $roleType->id,
            'authorizable_set_value'   => 'role1',
            'authorizable_model_id'    => $model->id,
            'rules'                    => json_encode([
                'read' => '*',
            ]),
        ]);

        AuthorizationRule::factory()->create([
            'authorizable_set_type_id' => $roleType->id,
            'authorizable_set_value'   => 'role2',
            'authorizable_model_id'    => $model->id,
            'rules'                    => json_encode([
                'write' => '*',
            ]),
        ]);

        $resolvedRules = AuthorizationRule::resolveRulesFor(TestUser::class);

        $role1Rules = $resolvedRules->where('authorizable_set_value', 'role1')->first();
        $role2Rules = $resolvedRules->where('authorizable_set_value', 'role2')->first();

        $this->assertArrayHasKey('read', Arr::get($role1Rules, 'rules'));
        $this->assertArrayHasKey('write', Arr::get($role2Rules, 'rules'));
    }


}
