<?php

namespace Asseco\JsonAuthorization\Tests\Unit\Authorization;

use Asseco\JsonAuthorization\App\Models\AuthorizableSetType;
use Asseco\JsonAuthorization\Authorization\UserAuthorizableSet;
use Asseco\JsonAuthorization\Tests\TestCase;
use Asseco\JsonAuthorization\Tests\TestUser;
use Exception;
use Illuminate\Foundation\Auth\User;

class UserAuthorizableSetTest extends TestCase
{
    /** @test */
    public function returns_empty_set_if_not_logged_in()
    {
        $authorizableSets = UserAuthorizableSet::formatted();

        $this->assertCount(0, $authorizableSets);
    }

    /** @test */
    public function throws_if_user_does_not_implement_interface()
    {
        $this->expectException(Exception::class);

        $this->actingAs(new User());

        UserAuthorizableSet::formatted();
    }

    /** @test */
    public function returns_unresolved_rules()
    {
        $this->actingAs(new TestUser());

        $roleType = AuthorizableSetType::factory()->create(['name' => 'roles']);
        $groupType = AuthorizableSetType::factory()->create(['name' => 'groups']);

        $expected = [
            [
                'authorizable_set_type_id' => $roleType->id,
                'authorizable_set_value'   => 'role1',
                'rules'                    => [],
            ],
            [
                'authorizable_set_type_id' => $roleType->id,
                'authorizable_set_value'   => 'role2',
                'rules'                    => [],
            ],
            [
                'authorizable_set_type_id' => $groupType->id,
                'authorizable_set_value'   => 'group1',
                'rules'                    => [],
            ],
            [
                'authorizable_set_type_id' => $groupType->id,
                'authorizable_set_value'   => 'group2',
                'rules'                    => [],
            ],
            [
                'authorizable_set_type_id' => 3,
                'authorizable_set_value'   => 'voice-all-mighty',
                'rules'                    => [],
            ],
        ];

        $this->assertEquals($expected, UserAuthorizableSet::formatted()->toArray());
    }
}
