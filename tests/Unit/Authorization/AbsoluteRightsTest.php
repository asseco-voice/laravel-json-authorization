<?php

namespace Asseco\JsonAuthorization\Tests\Unit\Authorization;

use Asseco\JsonAuthorization\App\Contracts\AuthorizableModel;
use Asseco\JsonAuthorization\App\Contracts\AuthorizableSetType;
use Asseco\JsonAuthorization\App\Contracts\AuthorizationRule;
use Asseco\JsonAuthorization\Authorization\AbsoluteRights;
use Asseco\JsonAuthorization\Tests\TestCase;
use Asseco\JsonAuthorization\Tests\TestUser;

class AbsoluteRightsTest extends TestCase
{
    protected AuthorizableModel $authorizableModel;
    protected AuthorizableSetType $authorizableSetType;
    protected AuthorizationRule $authorizationRule;

    public function setUp(): void
    {
        parent::setUp();

        config(['asseco-authorization.models_path' => [
            __DIR__ . '/../../' => 'Asseco\\JsonAuthorization\\Tests\\',
        ]]);

        $this->authorizableModel = app(AuthorizableModel::class);
        $this->authorizableSetType = app(AuthorizableSetType::class);
        $this->authorizationRule = app(AuthorizationRule::class);
    }

    /** @test */
    public function has_no_role_with_absolute_right()
    {
        $this->actingAs(new TestUser());

        $this->authorizableModel::factory()->create();
        $this->authorizableSetType::factory()->create(['name' => 'roles']);

        $authorizationRules = $this->authorizationRule::resolveRulesFor(TestUser::class);

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

        $this->authorizableModel::factory()->create();
        $this->authorizableSetType::factory()->create(['name' => 'roles']);

        $authorizationRules = $this->authorizationRule::resolveRulesFor(TestUser::class);

        $this->assertTrue(AbsoluteRights::hasRole($authorizationRules));
    }
}
