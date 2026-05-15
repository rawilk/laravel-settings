<?php

declare(strict_types=1);

namespace Rawilk\Settings\Concerns\Settings;

use Closure;
use Illuminate\Support\Facades\Crypt;
use Rawilk\Settings\Support\EncryptionStatus;

/**
 * @mixin \Rawilk\Settings\Settings
 */
trait EncryptsSettings
{
    protected EncryptionStatus $encryptionStatus;

    public function disableEncryption(): static
    {
        $this->encryptionStatus->disable();

        return $this;
    }

    public function enableEncryption(): static
    {
        $this->encryptionStatus->enable();

        return $this;
    }

    public function withoutEncryption(Closure $callback): mixed
    {
        if ($this->encryptionStatus->disabled()) {
            return $callback();
        }

        $this->encryptionStatus->disable();

        try {
            return $callback();
        } finally {
            $this->encryptionStatus->enable();
        }
    }

    public function setEncryptionStatus(EncryptionStatus $encryptionStatus): static
    {
        $this->encryptionStatus = $encryptionStatus;

        return $this;
    }

    protected function decryptValue(mixed $value): mixed
    {
        if ($this->encryptionStatus->disabled()) {
            return $value;
        }

        if (! is_string($value)) {
            return $value;
        }

        return rescue(fn () => Crypt::decrypt($value), fn () => $value);
    }
}
