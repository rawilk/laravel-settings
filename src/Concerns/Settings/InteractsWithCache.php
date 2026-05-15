<?php

declare(strict_types=1);

namespace Rawilk\Settings\Concerns\Settings;

use Closure;
use DateInterval;
use DateTimeInterface;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Rawilk\Settings\Support\CacheStatus;
use UnitEnum;

/**
 * @mixin \Rawilk\Settings\Settings
 */
trait InteractsWithCache
{
    protected CacheStatus $cacheStatus;

    protected string $cacheKeyPrefix = '';

    protected null|string|UnitEnum $cacheStore = null;

    /**
     * Indicates if missing settings should have the default value cached when retrieved.
     */
    protected bool $cacheDefaultValues = false;

    public function enableCache(): static
    {
        $this->cacheStatus->enable();

        return $this;
    }

    public function disableCache(): static
    {
        $this->cacheStatus->disable();

        return $this;
    }

    public function withoutCache(Closure $callback): mixed
    {
        if ($this->cacheStatus->disabled()) {
            return $callback();
        }

        $this->cacheStatus->disable();

        try {
            return $callback();
        } finally {
            $this->cacheStatus->enable();
        }
    }

    public function cacheItemsFor(null|int|array|Closure|DateTimeInterface|DateInterval $ttl): static
    {
        $this->cacheStatus->cacheFor($ttl);

        return $this;
    }

    public function cacheDefaultValue(bool $condition = true): static
    {
        $this->cacheDefaultValues = $condition;

        return $this;
    }

    public function prefixCacheWith(string $prefix): static
    {
        $this->cacheKeyPrefix = $prefix;

        return $this;
    }

    public function getCachePrefix(): string
    {
        return $this->cacheKeyPrefix;
    }

    public function setCacheStatus(CacheStatus $cacheStatus): static
    {
        $this->cacheStatus = $cacheStatus;

        return $this;
    }

    public function useCacheStore(null|string|UnitEnum $store = null): static
    {
        $this->cacheStore = $store;

        return $this;
    }

    public function cache(): Repository
    {
        return Cache::store($this->cacheStore);
    }

    /**
     * Generate the key to use for caching a specific setting.
     * This is meant for external usage.
     */
    public function cacheKeyForSetting(string|UnitEnum $key): string
    {
        $storageKey = $this->getKeyForStorage(
            $this->normalizeKey($key),
        );

        return $this->getCacheKey($storageKey);
    }

    protected function getCacheKey(string $key): string
    {
        $cacheKey = $this->getCachePrefix() . $key;

        $teamId = $this->getTeamId();
        if ($teamId !== false) {
            $teamCacheId = is_null($teamId) ? 'null' : $teamId;

            $cacheKey .= "::team:{$teamCacheId}";
        }

        return $cacheKey;
    }

    protected function cacheValue(
        string $key,
        mixed $value,
        null|int|Closure|DateTimeInterface|DateInterval|array $cacheTtl = null,
    ): void {
        if ($this->cacheStatus->disabled()) {
            return;
        }

        $ttl = $cacheTtl ?? $this->cacheStatus->getTtl();
        if (is_array($ttl)) {
            $ttl = $ttl[1] ?? $ttl[0];
        }

        if ($ttl instanceof Closure) {
            $ttl = $ttl();
        }

        $this->cache()->put(
            $this->getCacheKey($key),
            $value,
            $ttl,
        );
    }
}
