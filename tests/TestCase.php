<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\Tests;

use Asseco\JsonAuthorization\JsonAuthorizationServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [JsonAuthorizationServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }
}
