<?php

namespace Asseco\JsonAuthorization\Tests\Unit\Authorization;

use Asseco\JsonAuthorization\App\Collections\AuthorizableSetCollection;
use Asseco\JsonAuthorization\App\Models\AuthorizableSetType;
use Asseco\JsonAuthorization\Tests\TestCase;
use Asseco\JsonAuthorization\Tests\TestUser;

class AuthorizableSetCollectionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function filters_supported_set_types()
    {
        AuthorizableSetType::factory()->create(['name' => 'roles']);

        $userSet = new AuthorizableSetCollection((new TestUser())->getAuthorizableSets());

        $expected = [
            'roles' => [
                'role1',
                'role2',
            ],
        ];

        $filtered = $userSet->filterByExistingTypes();
        $this->assertEquals($expected, $filtered->toArray());
    }

    /** @test */
    public function filtering_returns_empty_when_no_set_types_exist()
    {
        $userSet = new AuthorizableSetCollection((new TestUser())->getAuthorizableSets());

        $filtered = $userSet->filterByExistingTypes();
        $this->assertEquals([], $filtered->toArray());
    }

    /** @test */
    public function creates_virtual_role_in_db()
    {
        $set = new AuthorizableSetCollection();

        $set->createVirtualRole();

        $this->assertEquals(AuthorizableSetCollection::VIRTUAL_SET_TYPE, AuthorizableSetType::first()->name);
    }

    /** @test */
    public function skips_creating_virtual_role_if_it_exists()
    {
        AuthorizableSetType::factory()->create(['name' => AuthorizableSetCollection::VIRTUAL_SET_TYPE]);

        $set = new AuthorizableSetCollection();

        $set->createVirtualRole();

        $this->assertEquals(AuthorizableSetCollection::VIRTUAL_SET_TYPE, AuthorizableSetType::first()->name);
        $this->assertCount(1, AuthorizableSetType::all());
    }

    /** @test */
    public function appends_virtual_role()
    {
        $set = new AuthorizableSetCollection();

        $set->appendVirtualRole();

        $expected = [
            'virtual-set-type' => [
                'voice-all-mighty',
            ],
        ];

        $this->assertEquals($expected, $set->toArray());
    }

    /** @test */
    public function transforms()
    {
        $roleType = AuthorizableSetType::factory()->create(['name' => 'roles']);
        $groupType = AuthorizableSetType::factory()->create(['name' => 'groups']);

        $userSet = new AuthorizableSetCollection((new TestUser())->getAuthorizableSets());

        $formatted = $userSet->toAuthorizationRuleFormat();

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
        ];

        $this->assertEquals($expected, $formatted->toArray());
    }
}
