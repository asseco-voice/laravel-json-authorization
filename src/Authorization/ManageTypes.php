<?php

namespace Voice\JsonAuthorization\Authorization;

use Illuminate\Support\Facades\Cache;
use Voice\JsonAuthorization\App\AuthorizableSetType;

class ManageTypes
{
    const CACHE_PREFIX = 'manage_types';
    const CACHE_TTL = 60 * 60 * 24;

    public static function getManageTypes()
    {
        if (Cache::has(self::CACHE_PREFIX)) {
            return Cache::get(self::CACHE_PREFIX);
        }

        $manageTypes = AuthorizableSetType::all();

        Cache::put(self::CACHE_PREFIX, $manageTypes, self::CACHE_TTL);
        return $manageTypes;
    }
}
