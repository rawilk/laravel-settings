<?php

declare(strict_types=1);

namespace Rawilk\Settings\Support;

use Closure;
use Illuminate\Support\Traits\ForwardsCalls;
use Rawilk\Settings\Settings;

/**
 * @mixin Settings
 */
class PendingSettings
{
    use ForwardsCalls;

    protected Settings $settings;

    public function __construct(
        Settings $settings,
        CacheStatus $cacheStatus,
        EncryptionStatus $encryptionStatus,
    ) {
        $this->settings = $settings
            ->setCacheStatus($cacheStatus)
            ->setEncryptionStatus($encryptionStatus)
            ->setKeyGenerator(SettingsConfig::getKeyGenerator())
            ->setValueSerializer(SettingsConfig::getValueSerializer());
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->forwardCallTo($this->settings, $name, $arguments);
    }

    /**
     * @deprecated Use defaultTeam() insstead.
     */
    public function setTeamId(mixed $team, ?Closure $callback = null): mixed
    {
        return $this->defaultTeam($team, $callback);
    }

    public function defaultTeam(mixed $team, ?Closure $callback = null): mixed
    {
        $resolver = app(TeamResolver::class);

        if ($callback) {
            return $resolver->withTeam($team, $callback);
        }

        $resolver->setDefaultTeam($team);

        return $this;
    }

    public function settings(): Settings
    {
        return $this->settings;
    }
}
