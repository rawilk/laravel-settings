<?php

declare(strict_types=1);

namespace Rawilk\Settings\Concerns\Settings;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Rawilk\Settings\Settings
 */
trait HasTeams
{
    protected bool $teams = false;

    /** @var null|string|int */
    protected mixed $teamId = null;

    protected ?string $teamForeignKey = null;

    // Allows us to use a team id for a single call.
    protected mixed $temporaryTeamId = false;

    public function getTeamId(): mixed
    {
        return $this->teamId;
    }

    /**
     * Set the team id for teams/groups support. This id is used when querying settings.
     *
     * @param  int|string|null|Model  $id
     */
    public function setTeamId(mixed $id): static
    {
        if ($id instanceof Model) {
            $id = $id->getKey();
        }

        $this->teamId = $id;

        return $this;
    }

    public function usingTeam(mixed $teamId): static
    {
        if ($teamId instanceof Model) {
            $teamId = $teamId->getKey();
        }

        $this->temporaryTeamId = $teamId;

        return $this;
    }

    public function withoutTeams(): static
    {
        $this->temporaryTeamId = null;

        return $this;
    }

    public function getTeamForeignKey(): ?string
    {
        return $this->teamForeignKey;
    }

    public function setTeamForeignKey(?string $foreignKey): static
    {
        $this->teamForeignKey = $foreignKey;

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

    protected function teamIdForCall(): mixed
    {
        if ($this->temporaryTeamId !== false) {
            return $this->temporaryTeamId;
        }

        return $this->teams ? $this->teamId : false;
    }
}
