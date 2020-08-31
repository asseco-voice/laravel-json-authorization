<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization\App\Traits;

use Voice\JsonAuthorization\App\Scopes\AuthorizationScope;

trait Authorizable
{
    protected static function booted(): void
    {
        static::addGlobalScope(new AuthorizationScope());
    }
}
