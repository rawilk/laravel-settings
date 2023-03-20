<?php

namespace Rawilk\Settings\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Rawilk\Settings\SettingsServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            SettingsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        include_once __DIR__.'/../database/migrations/create_settings_table.php.stub';
        (new \CreateSettingsTable)->up();
    }
}
