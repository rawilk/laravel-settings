<?php

namespace Rawilk\Settings\Tests\Feature\Drivers;

use InvalidArgumentException;
use Rawilk\Settings\Drivers\DatabaseDriver;
use Rawilk\Settings\Drivers\EloquentDriver;
use Rawilk\Settings\Facades\Settings;
use Rawilk\Settings\Tests\Support\Drivers\CustomDriver;
use Rawilk\Settings\Tests\TestCase;

class FactoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'settings.driver' => 'eloquent',
        ]);
    }

    /** @test */
    public function it_gets_the_default_driver(): void
    {
        $driver = app('SettingsFactory')->driver();

        self::assertInstanceOf(EloquentDriver::class, $driver);
    }

    /** @test */
    public function it_can_set_the_default_driver(): void
    {
        app('SettingsFactory')->setDefaultDriver('database');

        self::assertInstanceOf(DatabaseDriver::class, app('SettingsFactory')->driver());
    }

    /** @test */
    public function it_throws_an_exception_for_unsupported_drivers(): void
    {
        app('SettingsFactory')->setDefaultDriver('unknown');

        $this->expectException(InvalidArgumentException::class);

        app('SettingsFactory')->driver();
    }

    /** @test */
    public function it_can_retrieve_a_known_driver_at_runtime(): void
    {
        // default driver
        self::assertInstanceOf(EloquentDriver::class, app('SettingsFactory')->driver());

        self::assertInstanceOf(DatabaseDriver::class, app('SettingsFactory')->driver('database'));
    }

    /** @test */
    public function it_throws_an_exception_if_a_drivers_config_is_missing(): void
    {
        config([
            'settings.drivers.database' => null,
        ]);

        $this->expectException(InvalidArgumentException::class);

        app('SettingsFactory')->driver('database');
    }

    /** @test */
    public function custom_drivers_can_be_used(): void
    {
        config([
            'settings.drivers.custom' => [
                'driver' => 'custom',
            ],
        ]);

        app('SettingsFactory')->extend('custom', function ($app, array $config) {
            return new CustomDriver;
        });

        self::assertInstanceOf(CustomDriver::class, app('SettingsFactory')->driver('custom'));
    }

    /** @test */
    public function a_custom_driver_can_be_the_default_driver(): void
    {
        config([
            'settings.drivers.custom' => [
                'driver' => 'custom',
            ],
        ]);

        app('SettingsFactory')->extend('custom', function ($app, array $config) {
            return new CustomDriver;
        });

        app('SettingsFactory')->setDefaultDriver('custom');

        self::assertInstanceOf(CustomDriver::class, app('SettingsFactory')->driver());
        self::assertInstanceOf(CustomDriver::class, Settings::getDriver());
    }
}
