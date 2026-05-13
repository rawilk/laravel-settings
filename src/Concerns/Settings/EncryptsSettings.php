<?php

declare(strict_types=1);

namespace Rawilk\Settings\Concerns\Settings;

use Illuminate\Contracts\Encryption\Encrypter;

/**
 * @mixin \Rawilk\Settings\Settings
 */
trait EncryptsSettings
{
    protected ?Encrypter $encrypter = null;

    protected bool $encryptionEnabled = false;

    public function disableEncryption(): static
    {
        $this->encryptionEnabled = false;

        return $this;
    }

    public function enableEncryption(): static
    {
        $this->encryptionEnabled = true;

        return $this;
    }

    public function setEncrypter(Encrypter $encrypter): static
    {
        $this->encrypter = $encrypter;

        return $this;
    }

    protected function encryptionIsEnabled(): bool
    {
        return $this->encryptionEnabled && $this->encrypter !== null;
    }

    protected function decryptValue(mixed $value): mixed
    {
        if (! $this->encryptionIsEnabled()) {
            return $value;
        }

        if (! is_string($value)) {
            return $value;
        }

        return rescue(fn () => $this->encrypter->decrypt($value), fn () => $value);
    }
}
