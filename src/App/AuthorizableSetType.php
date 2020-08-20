<?php

namespace Voice\JsonAuthorization\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AuthorizableSetType extends Model
{
    const CACHE_PREFIX = 'authorizable_set_types';
    const CACHE_TTL = 60 * 60 * 24;

    protected $guarded = ['id'];

    public function rules()
    {
        return $this->hasMany(AuthorizationRule::class, 'authorization_rule_id');
    }

    public static function getCached(): array
    {
        if (Cache::has(self::CACHE_PREFIX)) {
            return Cache::get(self::CACHE_PREFIX);
        }

        $authorizableSetTypes = self::pluck('id', 'name')->toArray();

        Cache::put(self::CACHE_PREFIX, $authorizableSetTypes, self::CACHE_TTL);
        return $authorizableSetTypes;
    }
}
