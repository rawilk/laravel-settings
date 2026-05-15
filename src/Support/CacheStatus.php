<?php

declare(strict_types=1);

namespace Rawilk\Settings\Support;

use Closure;
use DateInterval;
use DateTimeInterface;

class CacheStatus
{
    protected bool $enabled = true;

    /**
     * @var int|array{ 0: DateTimeInterface|DateInterval|int, 1: DateTimeInterface|DateInterval|int }|Closure|DateTimeInterface|DateInterval|null
     */
    protected null|int|array|Closure|DateTimeInterface|DateInterval $ttl = null;

    public function __construct()
    {
        $this->enabled = SettingsConfig::shouldCache();
        $this->ttl = SettingsConfig::getCacheTtl();
    }

    public function cacheFor(null|int|array|Closure|DateTimeInterface|DateInterval $ttl): void
    {
        $this->ttl = $ttl;
    }

    public function getTtl(): int|array|Closure|DateTimeInterface|DateInterval|null
    {
        return $this->ttl;
    }

    public function enable(): bool
    {
        return $this->enabled = true;
    }

    public function disable(): bool
    {
        return $this->enabled = false;
    }

    public function disabled(): bool
    {
        return $this->enabled === false;
    }
}
