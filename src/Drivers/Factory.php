<?php

declare(strict_types=1);

namespace Rawilk\Settings\Drivers;

use Illuminate\Support\Manager;
use Rawilk\Settings\Support\SettingsConfig;
use Rawilk\Settings\Support\TeamResolver;

class Factory extends Manager
{
    public function getDefaultDriver(): ?string
    {
        return SettingsConfig::getDefaultDriver();
    }

    protected function createDatabaseDriver(): DatabaseDriver
    {
        return new DatabaseDriver(
            connection: $this->getContainer()['db']->connection(SettingsConfig::getDatabaseDriverConnection()),
            table: SettingsConfig::getSettingsTable(),
            teamResolver: app(TeamResolver::class),
            teamForeignKey: SettingsConfig::getTeamsForeignKey(),
        );
    }

    protected function createEloquentDriver(): EloquentDriver
    {
        return new EloquentDriver(
            model: SettingsConfig::getModel(),
        );
    }
}
