<?php

namespace Asseco\JsonAuthorization\Tests\Unit\Authorization;

use Asseco\JsonAuthorization\Tests\TestCase;

class AbsoluteRightsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config(['asseco-authorization.absolute_rights' => [
            'roles' => [
                'asseco-voice-admin',
            ],
        ]]);
    }

    /** @test */
    public function test()
    {
        $this->assertTrue(true);
    }
}
