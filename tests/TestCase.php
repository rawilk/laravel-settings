<?php

declare(strict_types=1);

namespace Rawilk\Settings\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Rawilk\Settings\SettingsServiceProvider;
use Rawilk\Settings\Support\ContextSerializers\KeyValueContextSerializer;
use Rawilk\Settings\Support\KeyGenerators\ReadableKeyGenerator;
use Rawilk\Settings\Support\ValueSerializers\JsonValueSerializer;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set some defaults for the serializers.
        config()->set('settings.key_generator', ReadableKeyGenerator::class);
        config()->set('settings.context_serializer', KeyValueContextSerializer::class);
        config()->set('settings.value_serializer', JsonValueSerializer::class);
    }

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
