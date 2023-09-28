<?php

declare(strict_types=1);

namespace Rawilk\Settings;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Rawilk\Settings\Contracts\Driver;
use Rawilk\Settings\Contracts\KeyGenerator;
use Rawilk\Settings\Contracts\ValueSerializer;
use Rawilk\Settings\Events\SettingsFlushed;
use Rawilk\Settings\Events\SettingWasDeleted;
use Rawilk\Settings\Events\SettingWasStored;
use Rawilk\Settings\Exceptions\InvalidBulkValueResult;
use Rawilk\Settings\Exceptions\InvalidKeyGenerator;
use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Support\KeyGenerators\Md5KeyGenerator;

class Settings
{
    use Macroable;

    protected ?Cache $cache = null;

    protected null|Context|bool $context = null;

    protected ?Encrypter $encrypter = null;

    protected bool $cacheEnabled = false;

    protected bool $encryptionEnabled = false;

    // Allows us to disable cache usage for a single call easily.
    protected bool $temporarilyDisableCache = false;

    // Instruct us to reset the context after a call (such as `get()`).
    // Meant for internal use only.
    protected bool $resetContext = true;

    protected bool $teams = false;

    /** @var null|string|int */
    protected mixed $teamId = null;

    protected ?string $teamForeignKey = null;

    protected string $cacheKeyPrefix = '';

    public function __construct(
        protected Driver $driver,
        protected KeyGenerator $keyGenerator,
        protected ValueSerializer $valueSerializer,
    ) {
    }

    // mainly for testing purposes
    public function getDriver(): Driver
    {
        return $this->driver;
    }

    /**
     * Pass in `false` for context when calling `all()` to only return results
     * that do not have context.
     */
    public function context(Context|bool $context = null): self
    {
        $this->context = $context;

        return $this;
    }

    public function getTeamId(): mixed
    {
        return $this->teamId;
    }

    /**
     * Set the team id for teams/groups support. This id is used when querying settings.
     *
     * @param  int|string|null|\Illuminate\Database\Eloquent\Model  $id
     */
    public function setTeamId(mixed $id): self
    {
        if ($id instanceof Model) {
            $id = $id->getKey();
        }

        $this->teamId = $id;

        return $this;
    }

    public function getTeamForeignKey(): ?string
    {
        return $this->teamForeignKey;
    }

    public function setTeamForeignKey(?string $foreignKey): self
    {
        $this->teamForeignKey = $foreignKey;

        return $this;
    }

    public function forget($key)
    {
        $key = $this->normalizeKey($key);

        $generatedKey = $this->getKeyForStorage($key);

        $driverResult = $this->driver->forget(
            key: $generatedKey,
            teamId: $this->teams ? $this->teamId : false,
        );

        SettingWasDeleted::dispatch(
            $key,
            $generatedKey,
            $this->getCacheKey($generatedKey),
            $this->teams ? $this->teamId : false,
            $this->context,
        );

        if ($this->temporarilyDisableCache || $this->cacheIsEnabled()) {
            $this->cache->forget($this->getCacheKey($generatedKey));
        }

        if ($this->resetContext) {
            $this->context();
        }

        $this->temporarilyDisableCache = false;
        $this->resetContext = true;

        return $driverResult;
    }

    public function get(string $key, $default = null)
    {
        $key = $this->normalizeKey($key);

        $generatedKey = $this->getKeyForStorage($key);

        if ($this->cacheIsEnabled()) {
            $value = $this->cache->rememberForever(
                $this->getCacheKey($generatedKey),
                fn () => $this->driver->get(
                    key: $generatedKey,
                    default: $default,
                    teamId: $this->teams ? $this->teamId : false,
                )
            );
        } else {
            $value = $this->driver->get(
                key: $generatedKey,
                default: $default,
                teamId: $this->teams ? $this->teamId : false,
            );
        }

        if ($value !== null && $value !== $default) {
            $value = $this->unserializeValue($this->decryptValue($value));
        }

        if ($this->resetContext) {
            $this->context();
        }

        $this->temporarilyDisableCache = false;
        $this->resetContext = true;

        return $value ?? $default;
    }

    public function all($keys = null): Collection
    {
        $keys = $this->normalizeBulkLookupKey($keys);

        $values = collect($this->driver->all(
            teamId: $this->teams ? $this->teamId : false,
            keys: $keys,
        ))->map(function (mixed $record): mixed {
            $record = $this->normalizeBulkRetrievedValue($record);
            $value = $record->value;

            if ($value !== null) {
                $value = $this->unserializeValue($this->decryptValue($value));
            }

            $record->value = $value;
            $record->original_key = $record->key;
            $record->key = $this->keyGenerator->removeContextFromKey($record->key);

            return $record;
        });

        if ($this->resetContext) {
            $this->context();
        }

        $this->temporarilyDisableCache = false;
        $this->resetContext = true;

        return $values;
    }

    public function has($key): bool
    {
        $key = $this->normalizeKey($key);

        $has = $this->driver->has(
            key: $this->getKeyForStorage($key),
            teamId: $this->teams ? $this->teamId : false,
        );

        if ($this->resetContext) {
            $this->context();
        }

        $this->temporarilyDisableCache = false;
        $this->resetContext = true;

        return $has;
    }

    public function set(string $key, $value = null): mixed
    {
        $key = $this->normalizeKey($key);

        // We really only need to update the value if it has changed
        // to prevent the cache being reset on the key.
        if (! $this->shouldSetNewValue(key: $key, newValue: $value)) {
            $this->context();

            return null;
        }

        $generatedKey = $this->getKeyForStorage($key);
        $serializedValue = $this->serializeValue($value);

        $driverResult = $this->driver->set(
            key: $generatedKey,
            value: $this->encryptionIsEnabled() ? $this->encrypter->encrypt($serializedValue) : $serializedValue,
            teamId: $this->teams ? $this->teamId : false,
        );

        SettingWasStored::dispatch(
            $key,
            $generatedKey,
            $this->getCacheKey($generatedKey),
            $value,
            $this->teams ? $this->teamId : false,
            $this->context,
        );

        if ($this->temporarilyDisableCache || $this->cacheIsEnabled()) {
            $this->cache->forget($this->getCacheKey($generatedKey));
        }

        $this->context();
        $this->temporarilyDisableCache = false;

        return $driverResult;
    }

    public function isFalse(string $key, $default = false): bool
    {
        $value = $this->get(key: $key, default: $default);

        return $value === false || $value === '0' || $value === 0;
    }

    public function isTrue(string $key, $default = true): bool
    {
        $value = $this->get(key: $key, default: $default);

        return $value === true || $value === '1' || $value === 1;
    }

    public function flush($keys = null): mixed
    {
        $keys = $this->normalizeBulkLookupKey($keys);

        $driverResult = $this->driver->flush(
            teamId: $this->teams ? $this->teamId : false,
            keys: $keys,
        );

        SettingsFlushed::dispatch(
            $keys,
            $this->teams ? $this->teamId : false,
            $this->context,
        );

        // Flush the cache for all deleted keys.
        // Note: Only works when a subset of keys is specified.
        if ($keys instanceof Collection && ($this->temporarilyDisableCache || $this->cacheIsEnabled())) {
            $keys->each(function (string $key) {
                $this->cache->forget($this->getCacheKey($key));
            });
        }

        if ($this->resetContext) {
            $this->context();
        }

        $this->temporarilyDisableCache = false;
        $this->resetContext = true;

        return $driverResult;
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

    public function disableEncryption(): self
    {
        $this->encryptionEnabled = false;

        return $this;
    }

    public function enableEncryption(): self
    {
        $this->encryptionEnabled = true;

        return $this;
    }

    public function setEncrypter(Encrypter $encrypter): self
    {
        $this->encrypter = $encrypter;

        return $this;
    }

    public function enableTeams(): self
    {
        $this->teams = true;

        return $this;
    }

    public function disableTeams(): self
    {
        $this->teams = false;

        return $this;
    }

    public function teamsAreEnabled(): bool
    {
        return $this->teams;
    }

    public function useCacheKeyPrefix(string $prefix): self
    {
        $this->cacheKeyPrefix = $prefix;

        return $this;
    }

    public function getKeyGenerator(): KeyGenerator
    {
        return $this->keyGenerator;
    }

    /**
     * Generate the key to use for caching a specific setting.
     * This is meant for external usage.
     */
    public function cacheKeyForSetting(string $key): string
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

    protected function normalizeKey(string $key): string
    {
        if (Str::startsWith(haystack: $key, needles: 'file_')) {
            return str_replace(search: 'file_', replace: 'file.', subject: $key);
        }

        return $key;
    }

    protected function getCacheKey(string $key): string
    {
        $cacheKey = $this->cacheKeyPrefix . $key;

        if ($this->teams) {
            $teamId = $this->teamId ?? 'null';

            $cacheKey .= "::team:{$teamId}";
        }

        return $cacheKey;
    }

    protected function getKeyForStorage(string $key): string
    {
        return $this->keyGenerator->generate(key: $key, context: $this->context);
    }

    protected function serializeValue($value): string
    {
        return $this->valueSerializer->serialize($value);
    }

    protected function unserializeValue($serialized)
    {
        if (! is_string($serialized)) {
            return $serialized;
        }

        // Attempt to unserialize the value, but return the original value if that fails.
        return rescue(fn () => $this->valueSerializer->unserialize($serialized), fn () => $serialized);
    }

    protected function shouldSetNewValue(string $key, $newValue): bool
    {
        if (! $this->cacheIsEnabled()) {
            return true;
        }

        // To prevent decryption errors, we will check if we have a setting set for the current context and key.
        if (! $this->doNotResetContext()->has($key)) {
            return true;
        }

        return $newValue !== $this->doNotResetContext()->get($key);
    }

    protected function cacheIsEnabled(): bool
    {
        if ($this->temporarilyDisableCache) {
            return false;
        }

        return $this->cacheEnabled && $this->cache !== null;
    }

    protected function encryptionIsEnabled(): bool
    {
        return $this->encryptionEnabled && $this->encrypter !== null;
    }

    protected function decryptValue($value)
    {
        if (! $this->encryptionIsEnabled()) {
            return $value;
        }

        if (! is_string($value)) {
            return $value;
        }

        return rescue(fn () => $this->encrypter->decrypt($value), fn () => $value);
    }

    protected function doNotResetContext(): self
    {
        $this->resetContext = false;

        return $this;
    }

    protected function normalizeBulkRetrievedValue(mixed $record): object
    {
        if (is_array($record)) {
            $record = (object) $record;
        }

        throw_unless(
            is_object($record) || $record instanceof Model,
            InvalidBulkValueResult::notObject(),
        );

        throw_unless(
            isset($record->key, $record->value),
            InvalidBulkValueResult::missingValueOrKey(),
        );

        return $record;
    }

    protected function normalizeBulkLookupKey($key): string|Collection|bool
    {
        if (is_null($key) && $this->context !== null) {
            throw_if(
                $this->keyGenerator instanceof Md5KeyGenerator,
                InvalidKeyGenerator::forPartialLookup($this->keyGenerator::class),
            );

            return is_bool($this->context)
                ? $this->context
                : $this->keyGenerator->generate('', $this->context);
        }

        return collect($key)
            ->flatten()
            ->filter()
            ->map(fn (string $key): string => $this->getKeyForStorage($this->normalizeKey($key)));
    }
}
