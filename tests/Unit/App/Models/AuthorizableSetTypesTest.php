<?php

namespace Asseco\JsonAuthorization\Tests\Unit\Authorization;

use Asseco\JsonAuthorization\App\Contracts\AuthorizableSetType;
use Asseco\JsonAuthorization\App\Contracts\AuthorizationRule;
use Asseco\JsonAuthorization\Tests\TestCase;

class AuthorizableSetTypesTest extends TestCase
{
    protected AuthorizableSetType $authorizableSetType;
    protected AuthorizationRule $authorizationRule;

    public function setUp(): void
    {
        parent::setUp();

        $this->authorizableSetType = app(AuthorizableSetType::class);
        $this->authorizationRule = app(AuthorizationRule::class);
    }

    /** @test */
    public function has_rules()
    {
        $setType = $this->authorizableSetType::factory()->create();

        $this->authorizationRule::factory()->count(5)->create([
            'authorizable_set_type_id' => $setType->id,
        ]);

        $this->assertCount(5, $setType->rules);
    }
}
