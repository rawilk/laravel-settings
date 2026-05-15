<?php

declare(strict_types=1);

namespace Rawilk\Settings;

use Closure;
use DateInterval;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Rawilk\Settings\Drivers\Factory;
use Rawilk\Settings\Events\SettingsFlushed;
use Rawilk\Settings\Events\SettingWasDeleted;
use Rawilk\Settings\Events\SettingWasStored;
use Rawilk\Settings\Exceptions\InvalidBulkValueResult;
use Rawilk\Settings\Exceptions\InvalidKeyGenerator;
use Rawilk\Settings\Support\KeyGenerators\HashKeyGenerator;
use Rawilk\Settings\Support\KeyGenerators\Md5KeyGenerator;
use Rawilk\Settings\Support\SettingsConfig;
use Rawilk\Settings\Support\TeamResolver;
use UnitEnum;

class Settings
{
    use Concerns\Settings\EncryptsSettings;
    use Concerns\Settings\HasContext;
    use Concerns\Settings\HasDrivers;
    use Concerns\Settings\HasSerializers;
    use Concerns\Settings\HasTeams;
    use Concerns\Settings\InteractsWithCache;
    use Conditionable;
    use Macroable;

    public function __construct(Factory $factory, protected TeamResolver $teamResolver)
    {
        $this->setDriver($factory->driver());

        $this->configure();
    }

    public function forget(string|UnitEnum $key): mixed
    {
        $storageKey = $this->getKeyForStorage($this->normalizeKey($key));
        $cacheKey = $this->getCacheKey($storageKey);
        $teamId = $this->getTeamId();

        $result = $this->driver()->forget(
            key: $storageKey,
            teamId: $teamId,
        );

        SettingWasDeleted::dispatch(
            $key,
            $storageKey,
            $cacheKey,
            $teamId,
            $this->getContext(),
        );

        $this->cache()->forget($cacheKey);

        return $result;
    }

    public function get(
        string|UnitEnum $key,
        mixed $default = null,
        null|int|Closure|DateTimeInterface|DateInterval|array $cacheTtl = null,
    ): mixed {
        $storageKey = $this->getKeyForStorage($this->normalizeKey($key));

        $value = $this->fetchValue($storageKey, $cacheTtl);

        if (is_null($value)) {
            if ($this->cacheDefaultValues && $default !== null) {
                $this->cacheValue($storageKey, $this->prepareValueForStorage($default), $cacheTtl);
            }

            return $default;
        }

        return $this->unserializeValue($this->decryptValue($value));
    }

    public function has(string|UnitEnum $key): bool
    {
        $storageKey = $this->getKeyForStorage($this->normalizeKey($key));

        return $this->driver()->has(
            key: $storageKey,
            teamId: $this->getTeamId(),
        );
    }

    public function set(
        string|UnitEnum $key,
        mixed $value = null,
        null|int|Closure|DateTimeInterface|DateInterval|array $cacheTtl = null,
    ): mixed {
        // Only perform an update if the value changed.
        $originalValue = $this->withoutCache(function () use ($key): mixed {
            return $this->get($key);
        });

        if ($originalValue === $value) {
            return null;
        }

        $storageKey = $this->getKeyForStorage($this->normalizeKey($key));
        $teamId = $this->getTeamId();
        $cacheKey = $this->getCacheKey($storageKey);
        $storageValue = $this->prepareValueForStorage($value);

        $result = $this->driver()->set(
            key: $storageKey,
            value: $storageValue,
            teamId: $teamId,
        );

        SettingWasStored::dispatch(
            $key,
            $storageKey,
            $cacheKey,
            $value,
            $teamId,
            $this->getContext(),
        );

        // Warm the cache for the updated setting value.
        $this->cacheValue($storageKey, $storageValue, $cacheTtl);

        return $result;
    }

    public function isFalse(
        string|UnitEnum $key,
        mixed $default = false,
        null|int|Closure|DateTimeInterface|DateInterval|array $cacheTtl = null,
    ): bool {
        $value = $this->get(key: $key, default: $default, cacheTtl: $cacheTtl);

        return $value === false || $value === '0' || $value === 0;
    }

    public function isTrue(
        string|UnitEnum $key,
        mixed $default = true,
        null|int|Closure|DateTimeInterface|DateInterval|array $cacheTtl = null,
    ): bool {
        $value = $this->get(key: $key, default: $default, cacheTtl: $cacheTtl);

        return $value === true || $value === '1' || $value === 1;
    }

    public function all($keys = null): Collection
    {
        return collect($this->driver()->all(
            teamId: $this->getTeamId(),
            keys: $this->normalizeBulkLookupKeys($keys),
        ))->map(function (mixed $record): mixed {
            $record = $this->normalizeBulkValue($record);
            $value = $record->value;

            if ($value !== null) {
                $value = $this->unserializeValue($this->decryptValue($value));
            }

            $record->value = $value;
            $record->original_key = $record->key;
            $record->key = $this->getKeyGenerator()->removeContextFromKey($record->key);

            return $record;
        });
    }

    public function flush($keys = null): mixed
    {
        $storageKeys = $this->normalizeBulkLookupKeys($keys);

        $teamId = $this->getTeamId();

        $result = $this->driver()->flush(
            teamId: $teamId,
            keys: $storageKeys,
        );

        SettingsFlushed::dispatch(
            $storageKeys,
            $teamId,
            $this->getContext(),
        );

        // When a subset of keys is provided, we can flush the cache for the deleted keys.
        // We currently do not have a way to invalid the cache for all settings that were stored.
        if ($keys instanceof Collection) {
            $cache = $this->cache();

            $keys->each(function (string $key) use ($cache): void {
                $cache->forget($this->getCacheKey($key));
            });
        }

        return $result;
    }

    protected function fetchValue(
        string $key,
        null|int|Closure|DateTimeInterface|DateInterval|array $cacheTtl = null,
    ): mixed {
        $callback = fn () => $this->driver()->get(
            key: $key,
            teamId: $this->getTeamId(),
        );

        if ($this->cacheStatus->disabled()) {
            return $callback();
        }

        $ttl = $cacheTtl ?? $this->cacheStatus->getTtl();
        $cacheKey = $this->getCacheKey($key);

        if (is_array($ttl)) {
            return $this->cache()->flexible(
                $cacheKey,
                $ttl,
                $callback,
            );
        }

        if (is_null($ttl)) {
            return $this->cache()->rememberForever($cacheKey, $callback);
        }

        return $this->cache()->remember(
            $cacheKey,
            $ttl,
            $callback,
        );
    }

    protected function prepareValueForStorage(mixed $value): mixed
    {
        $serializedValue = $this->serializeValue($value);

        if ($this->encryptionStatus->disabled()) {
            return $serializedValue;
        }

        return Crypt::encrypt($serializedValue);
    }

    protected function normalizeKey(string|UnitEnum $key): string
    {
        $value = (string) settings_enum_value($key);

        if (Str::startsWith($value, 'file_')) {
            return str_replace('file_', 'file.', $value);
        }

        return $value;
    }

    protected function normalizeBulkLookupKeys($keys): string|Collection|bool
    {
        if (is_null($keys) && $this->getContext() !== null) {
            throw_if(
                $this->keyGenerator instanceof Md5KeyGenerator || $this->keyGenerator instanceof HashKeyGenerator,
                InvalidKeyGenerator::forPartialLookup($this->keyGenerator::class),
            );

            $context = $this->getContext();

            return is_bool($context)
                ? $context
                : $this->keyGenerator->generate('', $context);
        }

        return collect($keys)
            ->flatten()
            ->filter()
            ->map(fn (string|UnitEnum $key): string => $this->getKeyForStorage($this->normalizeKey($key)));
    }

    protected function getKeyForStorage(string $key): string
    {
        return $this->getKeyGenerator()->generate(key: $key, context: $this->getContext());
    }

    protected function configure(): void
    {
        $this->prefixCacheWith(SettingsConfig::getCacheKeyPrefix());
        $this->cacheDefaultValue(SettingsConfig::shouldCacheDefaultValues());
    }

    protected function normalizeBulkValue(mixed $record): object
    {
        if (is_array($record)) {
            $record = (object) $record;
        }

        throw_unless(
            is_object($record),
            InvalidBulkValueResult::notObject(),
        );

        throw_unless(
            isset($record->key, $record->value),
            InvalidBulkValueResult::missingValueOrKey(),
        );

        return $record;
    }
}
