<?php

declare(strict_types=1);

namespace Rawilk\Settings\Support;

class EncryptionStatus
{
    protected bool $enabled = true;

    public function __construct()
    {
        $this->enabled = SettingsConfig::shouldEncrypt();
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
