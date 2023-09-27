<?php

declare(strict_types=1);

namespace Rawilk\Settings\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Rawilk\Settings\SettingsServiceProvider;

class TestCase extends Orchestra
{
    public function getEnvironmentSetUp($app): void
    {
        $migrations = [
            'create_settings_table.php.stub',
        ];

        foreach ($migrations as $migrationName) {
            $migration = include __DIR__ . '/../database/migrations/' . $migrationName;
            $migration->up();
        }
    }

    protected function getPackageProviders($app): array
    {
        return [
            SettingsServiceProvider::class,
        ];
    }
}
