<?php

namespace Rawilk\Settings\Tests\Feature;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Rawilk\Settings\Facades\Settings;
use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Tests\TestCase;

class SettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'settings.driver' => 'database',
            'settings.table' => 'settings',
            'settings.cache' => false,
            'settings.encryption' => false,
        ]);
    }

    /** @test */
    public function it_can_determine_if_a_setting_has_been_persisted(): void
    {
        self::assertFalse(Settings::has('foo'));

        Settings::set('foo', 'bar');

        self::assertTrue(Settings::has('foo'));

        DB::table('settings')->truncate();

        self::assertFalse(Settings::has('foo'));
    }

    /** @test */
    public function it_gets_persisted_setting_values(): void
    {
        Settings::set('foo', 'bar');

        self::assertEquals('bar', Settings::get('foo'));
    }

    /** @test */
    public function it_returns_a_default_value_if_a_setting_is_not_persisted(): void
    {
        self::assertEquals('default value', Settings::get('foo', 'default value'));
    }

    /** @test */
    public function it_can_retrieve_values_based_on_context(): void
    {
        Settings::set('foo', 'bar');

        $userContext = new Context(['user_id' => 1]);
        Settings::context($userContext)->set('foo', 'user_1_value');

        self::assertEquals(2, DB::table('settings')->count());
        self::assertEquals('bar', Settings::get('foo'));
        self::assertEquals('user_1_value', Settings::context($userContext)->get('foo'));
    }

    /** @test */
    public function it_can_determine_if_a_setting_is_persisted_based_on_context(): void
    {
        Settings::set('foo', 'bar');

        $userContext = new Context(['user_id' => 1]);
        $user2Context = new Context(['user_id' => 2]);
        self::assertTrue(Settings::has('foo'));
        self::assertFalse(Settings::context($userContext)->has('foo'));

        Settings::context($userContext)->set('foo', 'user 1 value');

        self::assertTrue(Settings::context($userContext)->has('foo'));
        self::assertFalse(Settings::context($user2Context)->has('foo'));

        Settings::context($user2Context)->set('foo', 'user 2 value');

        self::assertTrue(Settings::context($userContext)->has('foo'));
        self::assertTrue(Settings::context($user2Context)->has('foo'));
        self::assertTrue(Settings::has('foo'));
    }

    /** @test */
    public function it_can_remove_persisted_values_based_on_context(): void
    {
        $userContext = new Context(['user_id' => 1]);
        $user2Context = new Context(['user_id' => 2]);
        Settings::set('foo', 'bar');
        Settings::context($userContext)->set('foo', 'user 1 value');
        Settings::context($user2Context)->set('foo', 'user 2 value');

        self::assertTrue(Settings::has('foo'));
        self::assertTrue(Settings::context($userContext)->has('foo'));
        self::assertTrue(Settings::context($user2Context)->has('foo'));

        Settings::context($user2Context)->forget('foo');

        self::assertTrue(Settings::has('foo'));
        self::assertTrue(Settings::context($userContext)->has('foo'));
        self::assertFalse(Settings::context($user2Context)->has('foo'));
    }

    /** @test */
    public function it_persists_values(): void
    {
        Settings::set('foo', 'bar');

        self::assertEquals(1, DB::table('settings')->count());
        self::assertEquals('bar', Settings::get('foo'));

        Settings::set('foo', 'updated value');

        self::assertEquals(1, DB::table('settings')->count());
        self::assertEquals('updated value', Settings::get('foo'));
    }

    /** @test */
    public function it_removes_persisted_values_from_storage(): void
    {
        Settings::set('foo', 'bar');
        Settings::set('bar', 'foo');

        self::assertEquals(2, DB::table('settings')->count());

        self::assertTrue(Settings::has('foo'));
        self::assertTrue(Settings::has('bar'));

        Settings::forget('foo');

        self::assertEquals(1, DB::table('settings')->count());
        self::assertEquals('foo', Settings::get('bar'));

        self::assertFalse(Settings::has('foo'));
        self::assertTrue(Settings::has('bar'));
    }

    /** @test */
    public function it_can_evaluate_stored_boolean_settings(): void
    {
        Settings::set('app.debug', '1');
        self::assertTrue(Settings::isTrue('app.debug'));

        Settings::set('app.debug', '0');
        self::assertFalse(Settings::isTrue('app.debug'));
        self::assertTrue(Settings::isFalse('app.debug'));

        Settings::set('app.debug', true);
        self::assertTrue(Settings::isTrue('app.debug'));
        self::assertFalse(Settings::isFalse('app.debug'));
    }

    /** @test */
    public function it_can_cache_values_on_retrieval(): void
    {
        $this->enableSettingsCache();

        Settings::set('foo', 'bar');

        $this->resetQueryCount();
        self::assertEquals('bar', Settings::get('foo'));
        $this->assertQueryCount(1);

        $this->resetQueryCount();
        self::assertEquals('bar', Settings::get('foo'));
        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_flushes_the_cache_when_updating_a_value(): void
    {
        $this->enableSettingsCache();

        Settings::set('foo', 'bar');

        $this->resetQueryCount();
        self::assertEquals('bar', Settings::get('foo'));
        $this->assertQueryCount(1);

        $this->resetQueryCount();
        self::assertEquals('bar', Settings::get('foo'));
        $this->assertQueryCount(0);

        Settings::set('foo', 'updated value');
        $this->resetQueryCount();
        self::assertEquals('updated value', Settings::get('foo'));
        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_does_not_uncache_other_cached_settings_when_updating_a_value(): void
    {
        $this->enableSettingsCache();

        Settings::set('foo', 'bar');
        Settings::set('bar', 'foo');

        $this->resetQueryCount();
        self::assertEquals('bar', Settings::get('foo'));
        self::assertEquals('foo', Settings::get('bar'));
        $this->assertQueryCount(2);

        $this->resetQueryCount();
        self::assertEquals('bar', Settings::get('foo'));
        self::assertEquals('foo', Settings::get('bar'));
        $this->assertQueryCount(0);

        Settings::set('foo', 'updated value');
        $this->resetQueryCount();
        self::assertEquals('updated value', Settings::get('foo'));
        self::assertEquals('foo', Settings::get('bar'));
        $this->assertQueryCount(1);
    }

    /** @test */
    public function the_boolean_checks_use_cached_values_if_cache_is_enabled(): void
    {
        $this->enableSettingsCache();

        Settings::set('true.value', true);
        Settings::set('false.value', false);

        $this->resetQueryCount();
        self::assertTrue(Settings::isTrue('true.value'));
        self::assertTrue(Settings::isFalse('false.value'));
        $this->assertQueryCount(2);

        $this->resetQueryCount();
        self::assertTrue(Settings::isTrue('true.value'));
        self::assertTrue(Settings::isFalse('false.value'));
        $this->assertQueryCount(0);
    }

    /** @test */
    public function it_does_not_use_the_cache_if_the_cache_is_disabled(): void
    {
        Settings::disableCache();
        DB::enableQueryLog();

        Settings::set('foo', 'bar');

        $this->resetQueryCount();
        self::assertEquals('bar', Settings::get('foo'));
        $this->assertQueryCount(1);

        $this->resetQueryCount();
        self::assertEquals('bar', Settings::get('foo'));
        $this->assertQueryCount(1);
    }

    /** @test */
    public function it_can_encrypt_values(): void
    {
        Settings::enableEncryption();

        Settings::set('foo', 'bar');

        $storedSetting = DB::table('settings')->first();
        $unEncrypted = unserialize(Crypt::decrypt($storedSetting->value));

        self::assertEquals('bar', $unEncrypted);
    }

    /** @test */
    public function it_can_decrypt_values(): void
    {
        Settings::enableEncryption();

        Settings::set('foo', 'bar');

        // The stored value will be encrypted and not retrieved serialized yet if encryption
        // is enabled.
        $storedSetting = DB::table('settings')->first();
        self::assertFalse($this->isSerialized($storedSetting->value));

        self::assertEquals('bar', Settings::get('foo'));
    }

    /** @test */
    public function it_does_not_encrypt_if_encryption_is_disabled(): void
    {
        Settings::disableEncryption();

        Settings::set('foo', 'bar');

        $storedSetting = DB::table('settings')->first();

        self::assertTrue($this->isSerialized($storedSetting->value));
        self::assertEquals('bar', unserialize($storedSetting->value));
    }

    /** @test */
    public function it_does_not_try_to_decrypt_if_encryption_is_disabled(): void
    {
        Settings::enableEncryption();
        Settings::set('foo', 'bar');

        Settings::disableEncryption();

        self::assertNotEquals('bar', Settings::get('foo'));
        self::assertNotEquals(serialize('bar'), Settings::get('foo'));
    }

    protected function assertQueryCount(int $expected): void
    {
        self::assertCount($expected, DB::getQueryLog());
    }

    protected function enableSettingsCache(): void
    {
        Settings::enableCache();
        DB::connection()->enableQueryLog();
    }

    protected function resetQueryCount(): void
    {
        DB::flushQueryLog();
    }

    /**
     * Determine if something is serialized. Based off a solution from WordPress.
     *
     * @see https://developer.wordpress.org/reference/functions/is_serialized/
     *
     * @param string|mixed $data
     * @return bool
     */
    protected function isSerialized(mixed $data): bool
    {
        // If it isn't a string, it isn't serialized.
        if (! is_string($data)) {
            return false;
        }

        $data = trim($data);

        if ($data === 'N;') {
            return true;
        }

        if (strlen($data) < 4) {
            return false;
        }

        if ($data[1] !== ':') {
            return false;
        }

        $semiColon = strpos($data, ';');
        $brace = strpos($data, '}');

        // Either ; or } must exist.
        if ($semiColon === false && $brace === false) {
            return false;
        }

        // But neither must be in the first x characters.
        if ($semiColon !== false && $semiColon < 3) {
            return false;
        }

        if ($brace !== false && $brace < 4) {
            return false;
        }

        $token = $data[0];
        switch ($token) {
            case 's':
                if (strpos($data, '"') === false) {
                    return false;
                }
                // Or else fall through
                // no break
            case 'a':
            case 'O':
                return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $end = '';

                return (bool) preg_match("/^{$token}:[0-9.E+-]+;$end/", $data);
        }

        return false;
    }
}
