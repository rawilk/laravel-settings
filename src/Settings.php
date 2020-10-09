<?php

namespace Rawilk\Settings;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
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
    protected Driver $driver;
    protected ?Encrypter $encrypter = null;
    protected KeyGenerator $keyGenerator;
    protected ValueSerializer $valueSerializer;

    protected bool $cacheEnabled = false;
    protected bool $encryptionEnabled = false;

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;

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
                fn () => $this->driver->get($generatedKey, $default)
            );
        } else {
            $value = $this->driver->get($generatedKey, $default);
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
        if (! $this->shouldSetNewValue($key, $value)) {
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
        $value = $this->get($key, $default);

        return $value === false || $value === '0' || $value === 1;
    }

    public function isTrue(string $key, $default = true): bool
    {
        $value = $this->get($key, $default);

        return $value === true || $value === '1' || $value === 1;
    }

    protected function normalizeKey(string $key): string
    {
        if (Str::startsWith($key, 'file_')) {
            $key = str_replace('file_', 'file.', $key);
        }

        return $key;
    }

    protected function getCacheKey(string $key): string
    {
        return config('settings.cache_key_prefix') . $key;
    }

    protected function getKeyForStorage(string $key): string
    {
        return $this->keyGenerator->generate($key, $this->context);
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
        } catch (\Throwable $e) {
            return $serialized;
        }
    }

    protected function shouldSetNewValue(string $key, $newValue): bool
    {
        if (! $this->cacheIsEnabled()) {
            return true;
        }

        $currentValue = $this->get($key);

        return $currentValue !== $newValue || ! $this->has($key);
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
        } catch (DecryptException $e) {
            return $value;
        }
    }
}
