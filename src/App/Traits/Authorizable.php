<?php

namespace Voice\JsonAuthorization\App\Traits;

use Voice\JsonAuthorization\App\Scopes\AuthorizationScope;

trait Authorizable
{
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new AuthorizationScope());
    }
}
