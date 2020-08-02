<?php

namespace Rawilk\Settings\Tests\Feature\Drivers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Rawilk\Settings\Drivers\DatabaseDriver;
use Rawilk\Settings\Tests\TestCase;

class DatabaseDriverTest extends TestCase
{
    use RefreshDatabase;

    protected DatabaseDriver $driver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->driver = new DatabaseDriver(app('db')->connection(), 'settings');
    }

    /** @test */
    public function it_creates_new_entries(): void
    {
        $this->driver->set('foo', 'bar');

        self::assertEquals(1, DB::table('settings')->count());
        self::assertEquals('bar', DB::table('settings')->where('key', 'foo')->value('value'));
    }

    /** @test */
    public function it_updates_existing_entries(): void
    {
        $this->driver->set('foo', 'bar');

        self::assertEquals('bar', DB::table('settings')->where('key', 'foo')->value('value'));

        $this->driver->set('foo', 'updated value');

        self::assertEquals(1, DB::table('settings')->count());
        self::assertEquals('updated value', DB::table('settings')->where('key', 'foo')->value('value'));
    }

    /** @test */
    public function it_checks_if_a_setting_is_persisted(): void
    {
        self::assertFalse($this->driver->has('foo'));

        $this->driver->set('foo', 'bar');

        self::assertTrue($this->driver->has('foo'));
        self::assertFalse($this->driver->has('not exists'));
    }

    /** @test */
    public function it_gets_a_persisted_setting_value(): void
    {
        $this->driver->set('foo', 'some value');

        self::assertEquals('some value', $this->driver->get('foo'));
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
