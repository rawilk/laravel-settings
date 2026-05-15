<?php

declare(strict_types=1);

namespace Rawilk\Settings\Support;

use Closure;
use Illuminate\Database\Eloquent\Model;

class TeamResolver
{
    protected bool $enabled = false;

    protected ?Closure $resolverOverride = null;

    protected mixed $teamOverride = false;

    protected mixed $defaultTeam = null;

    public function __construct()
    {
        $this->enabled = SettingsConfig::teamsAreEnabled();
    }

    public function resolve(): string|int|null
    {
        if ($this->teamOverride !== false) {
            return $this->getTeamId($this->teamOverride);
        }

        if ($this->resolverOverride !== null) {
            $resolverTeam = ($this->resolverOverride)();

            return $this->getTeamId($resolverTeam);
        }

        return $this->getTeamId($this->defaultTeam);
    }

    /**
     * Override the resolver by using a callback.
     */
    public function resolveUsing(?Closure $callback): static
    {
        $this->resolverOverride = $callback;

        return $this;
    }

    public function setDefaultTeam(mixed $team): static
    {
        $this->defaultTeam = $team;

        return $this;
    }

    /**
     * Execute a callback with a specific team, then restore the previous value.
     */
    public function withTeam(mixed $team, Closure $callback): mixed
    {
        $previousTeam = $this->teamOverride;
        $this->teamOverride = $team;

        try {
            return $callback();
        } finally {
            $this->teamOverride = $previousTeam;
        }
    }

    /**
     * Override the default team.
     */
    public function setTeam(mixed $team): static
    {
        $this->teamOverride = $team;

        return $this;
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

    public function getTeamId(mixed $team): string|int|null
    {
        if (is_null($team)) {
            return null;
        }

        if ($team instanceof Model) {
            return $team->getKey();
        }

        return $team;
    }
}
