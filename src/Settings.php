<?php

namespace Rawilk\Settings;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use JetBrains\PhpStorm\Pure;
use Rawilk\Settings\Contracts\Driver;
use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Support\ContextSerializer;
use Rawilk\Settings\Support\KeyGenerator;
use Rawilk\Settings\Support\ValueSerializer;

class Settings implements Driver
{
    use Macroable;

    protected ?Cache $cache = null;
    protected ?Context $context = null;
    protected ?Encrypter $encrypter = null;
    protected KeyGenerator $keyGenerator;
    protected ValueSerializer $valueSerializer;

    protected bool $cacheEnabled = false;
    protected bool $encryptionEnabled = false;

    #[Pure]
    public function __construct(protected Driver $driver)
    {
        $this->keyGenerator = new KeyGenerator(new ContextSerializer);
        $this->valueSerializer = new ValueSerializer;
    }

    // mainly for testing purposes
    public function getDriver(): Driver
    {
        return $this->driver;
    }

    public function context(Context $context = null): self
    {
        $this->context = $context;

        return $this;
    }

    public function forget($key)
    {
        $key = $this->normalizeKey($key);

        $generatedKey = $this->getKeyForStorage($key);

        $driverResult = $this->driver->forget($generatedKey);

        if ($this->cacheIsEnabled()) {
            $this->cache->forget($this->getCacheKey($generatedKey));
        }

        $this->context();

        return $driverResult;
    }

    public function get(string $key, $default = null)
    {
        $key = $this->normalizeKey($key);

        $generatedKey = $this->getKeyForStorage($key);

        if ($this->cacheIsEnabled()) {
            $value = $this->cache->rememberForever(
                $this->getCacheKey($generatedKey),
                fn () => $this->driver->get(key: $generatedKey, default: $default)
            );
        } else {
            $value = $this->driver->get(key: $generatedKey, default: $default);
        }

        if ($value !== null && $value !== $default) {
            $value = $this->unserializeValue($this->decryptValue($value));
        }

        $this->context();

        return $value ?? $default;
    }

    public function has($key): bool
    {
        $key = $this->normalizeKey($key);

        $has = $this->driver->has($this->getKeyForStorage($key));

        $this->context();

        return $has;
    }

    public function set(string $key, $value = null)
    {
        $key = $this->normalizeKey($key);

        // We really only need to update the value if is has changed
        // to prevent the cache being reset on the key.
        if (! $this->shouldSetNewValue(key: $key, newValue: $value)) {
            $this->context();

            return null;
        }

        $generatedKey = $this->getKeyForStorage($key);
        $serializedValue = $this->serializeValue($value);

        $driverResult = $this->driver->set(
            $generatedKey,
            $this->encryptionIsEnabled() ? $this->encrypter->encrypt($serializedValue) : $serializedValue
        );

        if ($this->cacheIsEnabled()) {
            $this->cache->forget($this->getCacheKey($generatedKey));
        }

        $this->context();

        return $driverResult;
    }

    public function isFalse(string $key, $default = false): bool
    {
        $value = $this->get(key: $key, default: $default);

        return $value === false || $value === '0' || $value === 1;
    }

    public function isTrue(string $key, $default = true): bool
    {
        $value = $this->get(key: $key, default: $default);

        return $value === true || $value === '1' || $value === 1;
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
        return config('settings.cache_key_prefix') . $key;
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
        // Attempt to unserialize the value, but return the original value if that fails.
        try {
            return $this->valueSerializer->unserialize($serialized);
        } catch (\Throwable) {
            return $serialized;
        }
    }

    protected function shouldSetNewValue(string $key, $newValue): bool
    {
        if (! $this->cacheIsEnabled()) {
            return true;
        }

        // Prevent the context from being reset before we can save...
        // See issue #3 (https://github.com/rawilk/laravel-settings/issues/3)
        $currentContext = $this->context;

        $currentValue = $this->get($key);

        $shouldUpdate = $currentValue !== $newValue || ! $this->has($key);

        // Now that we've made our calls, we can set our context back to what it was.
        $this->context($currentContext);

        return $shouldUpdate;
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

    public function setCache(Cache $cache): self
    {
        $this->cache = $cache;

        return $this;
    }

    protected function cacheIsEnabled(): bool
    {
        return $this->cacheEnabled && $this->cache !== null;
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

    protected function encryptionIsEnabled(): bool
    {
        return $this->encryptionEnabled && $this->encrypter !== null;
    }

    protected function decryptValue($value)
    {
        if (! $this->encryptionIsEnabled()) {
            return $value;
        }

        try {
            return $this->encrypter->decrypt($value);
        } catch (DecryptException) {
            return $value;
        }
    }
}
