<?php

namespace Asseco\JsonAuthorization\Tests\Unit\Authorization;

use Asseco\JsonAuthorization\App\Models\AuthorizableModel;
use Asseco\JsonAuthorization\App\Models\AuthorizableSetType;
use Asseco\JsonAuthorization\App\Models\AuthorizationRule;
use Asseco\JsonAuthorization\Authorization\AbsoluteRights;
use Asseco\JsonAuthorization\Tests\TestCase;
use Asseco\JsonAuthorization\Tests\TestUser;

class AbsoluteRightsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config(['asseco-authorization.models_path' => [
            __DIR__ . '/../../' => 'Asseco\\JsonAuthorization\\Tests\\',
        ]]);
    }

    /** @test */
    public function has_no_role_with_absolute_right()
    {
        $this->actingAs(new TestUser());

        AuthorizableModel::factory()->create();
        AuthorizableSetType::factory()->create(['name' => 'roles']);

        $authorizationRules = AuthorizationRule::resolveRulesFor(TestUser::class);

        $this->assertFalse(AbsoluteRights::hasRole($authorizationRules));
    }

    /** @test */
    public function has_absolute_right_role()
    {
        config([
            'asseco-authorization.absolute_rights' => [
                'roles' => [
                    'role1',
                ],
            ],
        ]);

        $this->actingAs(new TestUser());

        AuthorizableModel::factory()->create();
        AuthorizableSetType::factory()->create(['name' => 'roles']);

        $authorizationRules = AuthorizationRule::resolveRulesFor(TestUser::class);

        $this->assertTrue(AbsoluteRights::hasRole($authorizationRules));
    }
}
