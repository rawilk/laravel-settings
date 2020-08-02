<?php

namespace Rawilk\Settings\Tests\Feature\Drivers;

use Rawilk\Settings\Contracts\Setting;
use Rawilk\Settings\Drivers\EloquentDriver;
use Rawilk\Settings\Tests\TestCase;

class EloquentDriverTest extends TestCase
{
    protected EloquentDriver $driver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->driver = new EloquentDriver(app(Setting::class));
    }

    /** @test */
    public function it_creates_new_entries(): void
    {
        $this->driver->set('foo', 'bar');

        self::assertCount(1, app(Setting::class)->all());
        self::assertEquals('bar', app(Setting::class)->first()->value);
    }

    /** @test */
    public function it_updates_existing_entries(): void
    {
        $this->driver->set('foo', 'bar');

        self::assertEquals('bar', app(Setting::class)->first()->value);

        $this->driver->set('foo', 'updated value');

        self::assertCount(1, app(Setting::class)->all());
        self::assertEquals('updated value', app(Setting::class)->first()->value);
    }

    /** @test */
    public function it_checks_if_a_setting_is_persisted(): void
    {
        self::assertFalse($this->driver->has('foo'));

        $this->driver->set('foo', 'bar');

        self::assertTrue($this->driver->has('foo'));
    }

    /** @test */
    public function it_gets_a_persisted_setting_value(): void
    {
        $this->driver->set('foo', 'bar');

        self::assertEquals('bar', $this->driver->get('foo'));
    }

    /** @test */
    public function it_returns_a_default_value_for_settings_that_are_not_persisted(): void
    {
        self::assertEquals('my default value', $this->driver->get('foo', 'my default value'));
    }

    /** @test */
    public function it_removes_persisted_settings(): void
    {
        $this->driver->set('foo', 'bar');

        self::assertTrue($this->driver->has('foo'));

        $this->driver->forget('foo');

        self::assertFalse($this->driver->has('foo'));
    }
}
