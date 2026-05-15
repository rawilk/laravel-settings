<?php

declare(strict_types=1);

namespace Rawilk\Settings\Concerns\Settings;

use Closure;

/**
 * @mixin \Rawilk\Settings\Settings
 */
trait HasTeams
{
    /**
     * Allows caller to set a team for a single call/instance via `usingTeam()`.
     */
    protected mixed $temporaryTeam = false;

    public function disableTeams(): static
    {
        $this->teamResolver->disable();

        return $this;
    }

    public function enableTeams(): static
    {
        $this->teamResolver->enable();

        return $this;
    }

    public function teamsAreEnabled(): bool
    {
        return ! $this->teamResolver->disabled();
    }

    /**
     * Override the team temporarily for a single call/instance.
     */
    public function usingTeam(mixed $team, ?Closure $callback = null): mixed
    {
        if ($callback) {
            return $this->teamResolver->withTeam($team, $callback);
        }

        $this->temporaryTeam = $team;

        return $this;
    }

    public function noTeam(?Closure $callback = null): mixed
    {
        return $this->usingTeam(null, $callback);
    }

    protected function getTeamId(): mixed
    {
        if ($this->teamResolver->disabled()) {
            return false;
        }

        if ($this->temporaryTeam !== false) {
            return $this->teamResolver->getTeamId($this->temporaryTeam);
        }

        return $this->teamResolver->resolve();
    }
}
