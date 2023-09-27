<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Rawilk\Settings\Facades\Settings;
use Rawilk\Settings\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

// Helpers...
if (! function_exists('fake') && class_exists(\Faker\Factory::class)) {
    /**
     * Ensure the fake method exists. If we ever drop laravel 8 support, we can remove this helper.
     */
    function fake($locale = null)
    {
        $locale ??= app('config')->get('app.faker_locale') ?? 'en_US';

        $abstract = \Faker\Generator::class . ':' . $locale;

        if (! app()->bound($abstract)) {
            app()->singleton($abstract, fn () => \Faker\Factory::create($locale));
        }

        return app()->make($abstract);
    }
}

/**
 * The Database driver doesn't seem to be using the same Sqlite connection
 * the tests are using, so we'll force it to here. This should fix issues
 * with the settings table not existing when the driver queries it.
 */
function setDatabaseDriverConnection(): void
{
    $driver = Settings::getDriver();
    $reflection = new ReflectionClass($driver);

    $property = $reflection->getProperty('connection');
    $property->setAccessible(true);
    $property->setValue($driver, DB::connection());
}

function migrateTestTables(): void
{
    $migration = include __DIR__ . '/Support/database/migrations/create_test_tables.php';
    $migration->up();
}

function migrateTeams(): void
{
    $migration = include __DIR__ . '/../database/migrations/add_settings_team_field.php.stub';
    $migration->up();
}
