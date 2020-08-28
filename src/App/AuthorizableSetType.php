<?php

namespace Voice\JsonAuthorization\App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Throwable;
use Voice\JsonAuthorization\Exceptions\AuthorizationException;

class AuthorizableSetType extends Model
{
    const CACHE_PREFIX = 'authorizable_set_types';
    const CACHE_TTL = 60 * 60 * 24;

    protected $guarded = ['id'];

    public function rules()
    {
        return $this->hasMany(AuthorizationRule::class, 'authorizable_set_type_id');
    }

    /**
     * @return Collection
     * @throws Throwable
     */
    public static function getCached(): Collection
    {
        if (Cache::has(self::CACHE_PREFIX)) {
            return Cache::get(self::CACHE_PREFIX);
        }

        $authorizableSetTypes = self::all('id', 'name');

        throw_if(!$authorizableSetTypes, new AuthorizationException("No authorizable set types available"));

        Cache::put(self::CACHE_PREFIX, $authorizableSetTypes, self::CACHE_TTL);

        return $authorizableSetTypes;
    }

    /**
     * @return Collection
     * @throws Throwable
     */
    public static function reCache(): Collection
    {
        self::invalidateCache();
        return self::getCached();
    }

    public static function invalidateCache(): void
    {
        Cache::forget(self::CACHE_PREFIX);
    }
}
