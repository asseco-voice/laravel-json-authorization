<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\App\Traits;

use Asseco\JsonAuthorization\App\Scopes\AuthorizationScope;

trait Authorizable
{
    protected static function bootAuthorizable(): void
    {
        static::addGlobalScope(new AuthorizationScope());
    }
}
