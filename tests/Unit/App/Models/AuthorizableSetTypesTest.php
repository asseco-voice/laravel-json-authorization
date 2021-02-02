<?php

namespace Asseco\JsonAuthorization\Tests\Unit\Authorization;

use Asseco\JsonAuthorization\App\Models\AuthorizableSetType;
use Asseco\JsonAuthorization\App\Models\AuthorizationRule;
use Asseco\JsonAuthorization\Tests\TestCase;

class AuthorizableSetTypesTest extends TestCase
{
    /** @test */
    public function has_rules()
    {
        $setType = AuthorizableSetType::factory()->create();

        AuthorizationRule::factory()->count(5)->create([
            'authorizable_set_type_id' => $setType->id,
        ]);

        $this->assertCount(5, $setType->rules);
    }
}
