<?php

declare(strict_types=1);

namespace Rawilk\Settings\Concerns\Settings;

use BackedEnum;
use Illuminate\Contracts\Cache\Repository as Cache;

/**
 * @mixin \Rawilk\Settings\Settings
 */
trait InteractsWithCache
{
    protected ?Cache $cache = null;

    protected bool $cacheEnabled = false;

    // Allows us to disable cache usage for a single call easily.
    protected bool $temporarilyDisableCache = false;

    protected string $cacheKeyPrefix = '';

    public function cacheDefaultValue(bool $cacheDefaultValue = true): self
    {
        $this->cacheDefaultValue = $cacheDefaultValue;

        return $this;
    }

    public function disableCache(): self
    {
        $this->cacheEnabled = false;

        return $this;
    }

    public function enableCache(): self
    {
        $this->cacheEnabled = true;

        return $this;
    }

    public function temporarilyDisableCache(): self
    {
        $this->temporarilyDisableCache = true;

        return $this;
    }

    public function setCache(Cache $cache): self
    {
        $this->cache = $cache;

        return $this;
    }

    public function useCacheKeyPrefix(string $prefix): self
    {
        $this->cacheKeyPrefix = $prefix;

        return $this;
    }

    /**
     * Generate the key to use for caching a specific setting.
     * This is meant for external usage.
     */
    public function cacheKeyForSetting(string|BackedEnum $key): string
    {
        $storageKey = $this->getKeyForStorage(
            $this->normalizeKey($key),
        );

        $cacheKey = $this->getCacheKey($storageKey);

        if ($this->resetContext) {
            $this->context();
        }

        $this->resetContext = true;

        return $cacheKey;
    }

    protected function getCacheKey(string $key): string
    {
        $cacheKey = $this->cacheKeyPrefix . $key;

        $teamId = $this->teamIdForCall();
        if ($teamId !== false) {
            $teamId = is_null($teamId) ? 'null' : $teamId;

            $cacheKey .= "::team:{$teamId}";
        }

        return $cacheKey;
    }

    protected function cacheIsEnabled(): bool
    {
        if ($this->temporarilyDisableCache) {
            return false;
        }

        return $this->cacheEnabled && $this->cache !== null;
    }
}
