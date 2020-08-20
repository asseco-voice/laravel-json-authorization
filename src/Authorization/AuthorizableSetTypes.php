<?php

namespace Voice\JsonAuthorization\Authorization;

use Illuminate\Support\Facades\Cache;
use Voice\JsonAuthorization\App\AuthorizableSetType;

class AuthorizableSetTypes
{
    const CACHE_PREFIX = 'authorizable_set_types';
    const CACHE_TTL = 60 * 60 * 24;

    public static function getAuthorizableSetTypes(): array
    {
        if (Cache::has(self::CACHE_PREFIX)) {
            return Cache::get(self::CACHE_PREFIX);
        }

        $authorizableSetTypes = AuthorizableSetType::pluck('id', 'name')->toArray();

        Cache::put(self::CACHE_PREFIX, $authorizableSetTypes, self::CACHE_TTL);
        return $authorizableSetTypes;
    }
}
