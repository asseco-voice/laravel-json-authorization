<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization\App\Traits;

use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

trait Cacheable
{
    protected static function bootCacheable()
    {
        static::created(self::reCacheClosure());

        static::updated(self::reCacheClosure());

        static::deleted(self::reCacheClosure());
    }

    protected static function reCacheClosure(): Closure
    {
        return function ($model) {
            if (!$model instanceof self) {
                return;
            }

            $model::reCache();
        };
    }

    /**
     * Key to find cache by.
     */
    abstract protected static function cacheKey(): string;

    /**
     * Collection to be returned if cache doesn't find anything.
     */
    abstract protected static function cacheAlternative(): array;

    protected static function getCacheTtl(): int
    {
        return 60 * 60 * 24;
    }

    public static function cached(): Collection
    {
        if (Cache::has(static::cacheKey())) {
            return new Collection(Cache::get(static::cacheKey()));
        }

        $alternative = static::cacheAlternative();

        self::cache($alternative);

        return new Collection($alternative);
    }

    public static function reCache(array $values = null): void
    {
        static::invalidateCache();
        static::cache($values ?: static::cacheAlternative());
    }

    public static function invalidateCache(): void
    {
        Cache::forget(static::cacheKey());
    }

    public static function cache(array $values): void
    {
        Cache::put(static::cacheKey(), $values, self::getCacheTtl());
    }

    public static function appendToCache(array $values): void
    {
        $current = Cache::get(static::cacheKey()) ?: [];

        $current[] = $values;

        Cache::put(static::cacheKey(), $current, self::getCacheTtl());
    }
}
