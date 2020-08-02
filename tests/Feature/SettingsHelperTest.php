<?php

namespace Rawilk\Settings\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Rawilk\Settings\Facades\Settings as SettingsFacade;
use Rawilk\Settings\Settings;
use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Tests\TestCase;

class SettingsHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'settings.driver' => 'eloquent',
            'settings.table' => 'settings',
            'settings.cache' => false,
            'settings.encryption' => false,
        ]);
    }

    /** @test */
    public function it_returns_an_instance_of_the_settings_class_when_no_arguments_are_passed_in(): void
    {
        self::assertInstanceOf(Settings::class, settings());
    }

    /** @test */
    public function it_sets_values_if_an_array_is_passed_in_as_the_first_argument(): void
    {
        settings([
            'foo' => 'bar',
            'bar' => 'foo',
        ]);

        self::assertCount(2, DB::table('settings')->get());
        self::assertEquals('bar', SettingsFacade::get('foo'));
        self::assertEquals('foo', SettingsFacade::get('bar'));
    }

    /** @test */
    public function it_sets_the_context_if_a_context_is_passed_in_as_the_third_argument(): void
    {
        $context = new Context(['foo' => 'bar']);
        settings(['foo' => 'bar']);

        self::assertFalse(SettingsFacade::context($context)->has('foo'));

        settings(['foo' => 'context value'], null, $context);

        self::assertTrue(SettingsFacade::context($context)->has('foo'));
        self::assertEquals('bar', SettingsFacade::get('foo'));
        self::assertEquals('context value', SettingsFacade::context($context)->get('foo'));
    }

    /** @test */
    public function it_can_retrieve_values(): void
    {
        settings()->set('foo', 'bar');

        self::assertEquals('bar', settings('foo'));
        self::assertEquals('bar', settings()->get('foo'));
    }

    /** @test */
    public function it_returns_a_default_value_if_a_setting_is_not_persisted(): void
    {
        self::assertEquals('my default', settings('foo', 'my default'));
    }

    /** @test */
    public function it_returns_values_based_on_context(): void
    {
        $context = new Context(['foo' => 'bar']);

        settings()->set('foo', 'bar');
        settings()->context($context)->set('foo', 'context foo');

        self::assertEquals('bar', settings('foo'));
        self::assertEquals('context foo', settings('foo', null, $context));
    }
}
