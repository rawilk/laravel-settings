<?php

declare(strict_types=1);

namespace Rawilk\Settings\Tests;

use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase as Orchestra;
use Rawilk\Settings\Models\Setting;
use Rawilk\Settings\SettingsServiceProvider;
use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Support\ContextSerializers\KeyValueContextSerializer;
use Rawilk\Settings\Support\KeyGenerators\ReadableKeyGenerator;
use Rawilk\Settings\Support\ValueSerializers\JsonValueSerializer;

class TestCase extends Orchestra
{
    public function getEnvironmentSetUp($app): void
    {
        // Set some defaults for the serializers.
        config()->set('settings.key_generator', ReadableKeyGenerator::class);
        config()->set('settings.context_serializer', KeyValueContextSerializer::class);
        config()->set('settings.value_serializer', JsonValueSerializer::class);

        config()->set('settings.teams', true);
        config()->set('settings.team_foreign_key', 'team_id');

        config()->set('settings.driver', 'eloquent');
        config()->set('settings.table', 'settings');

        config()->set('app.key', Encrypter::generateKey(config('app.cipher')));

        $migration = include __DIR__ . '/../database/migrations/create_settings_table.php.stub';
        $migration->up();

        $migration = include __DIR__ . '/../database/migrations/add_settings_team_field.php.stub';
        $migration->up();

        $migration = include __DIR__ . '/Support/database/migrations/create_test_tables.php';
        $migration->up();
    }

    protected function getPackageProviders($app): array
    {
        return [
            SettingsServiceProvider::class,
        ];
    }

    protected function storeSetting(string $key, mixed $value, ?int $teamId = null, ?Context $context = null): void
    {
        Setting::updateOrCreate([
            'key' => $this->generateSettingKey($key, $context),
            'team_id' => $teamId,
        ], [
            'value' => $this->serializeValue($value),
        ]);
    }

    protected function serializeValue(mixed $value): mixed
    {
        return app(JsonValueSerializer::class)->serialize($value);
    }

    protected function generateSettingKey(string $key, ?Context $context = null): string
    {
        $generator = app(ReadableKeyGenerator::class);

        $generator->setContextSerializer(app(KeyValueContextSerializer::class));

        return $generator->generate($key, $context);
    }

    protected function enableQueryLog(): void
    {
        DB::connection()->enableQueryLog();
    }

    protected function resetQueryCount(): void
    {
        DB::flushQueryLog();
    }
}
