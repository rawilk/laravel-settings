<?php

namespace Rawilk\Settings\Tests\Feature;

use Rawilk\Settings\Facades\Settings as SettingsFacade;
use Rawilk\Settings\Settings;
use Rawilk\Settings\Tests\TestCase;

class SettingsMacroTest extends TestCase
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
    public function custom_functions_can_be_added_to_settings(): void
    {
        Settings::macro('myCustomFunction', function ($key) {
            /** @var \Rawilk\Settings\Settings $this */
            $value = $this->get($key);

            return strtoupper($value);
        });

        SettingsFacade::set('foo', 'bar');

        self::assertSame('BAR', SettingsFacade::myCustomFunction('foo'));
        self::assertNotSame('bar', SettingsFacade::myCustomFunction('foo'));
        self::assertSame('bar', SettingsFacade::get('foo'));
    }
}
