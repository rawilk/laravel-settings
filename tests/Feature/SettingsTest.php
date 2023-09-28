<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Rawilk\Settings\Facades\Settings as SettingsFacade;
use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Support\ValueSerializers\JsonValueSerializer;

beforeEach(function () {
    config([
        'settings.driver' => 'database',
        'settings.table' => 'settings',
        'settings.cache' => false,
        'settings.encryption' => false,
    ]);

    setDatabaseDriverConnection();
});

it('can determine if a setting has been persisted', function () {
    expect(SettingsFacade::has('foo'))->toBeFalse();

    SettingsFacade::set('foo', 'bar');

    expect(SettingsFacade::has('foo'))->toBeTrue();

    DB::table('settings')->truncate();

    expect(SettingsFacade::has('foo'))->toBeFalse();
});

it('gets persisted setting values', function () {
    SettingsFacade::set('foo', 'bar');

    expect(SettingsFacade::get('foo'))->toBe('bar');
});

it('returns a default value if a setting is not persisted', function () {
    expect(SettingsFacade::get('foo', 'default value'))->toBe('default value');
});

it('can retrieve values based on context', function () {
    SettingsFacade::set('foo', 'bar');

    $userContext = new Context(['user_id' => 1]);
    SettingsFacade::context($userContext)->set('foo', 'user_1_value');

    expect(DB::table('settings')->count())->toBe(2)
        ->and(SettingsFacade::get('foo'))->toBe('bar')
        ->and(SettingsFacade::context($userContext)->get('foo'))->toBe('user_1_value');
});

it('can determine if a setting is persisted based on context', function () {
    SettingsFacade::set('foo', 'bar');

    $userContext = new Context(['user_id' => 1]);
    $user2Context = new Context(['user_id' => 2]);

    expect(SettingsFacade::has('foo'))->toBeTrue()
        ->and(SettingsFacade::context($userContext)->has('foo'))->toBeFalse();

    SettingsFacade::context($userContext)->set('foo', 'user 1 value');

    expect(SettingsFacade::context($userContext)->has('foo'))->toBeTrue()
        ->and(SettingsFacade::context($user2Context)->has('foo'))->toBeFalse();

    SettingsFacade::context($user2Context)->set('foo', 'user 2 value');

    expect(SettingsFacade::context($userContext)->has('foo'))->toBeTrue()
        ->and(SettingsFacade::context($user2Context)->has('foo'))->toBeTrue()
        ->and(SettingsFacade::has('foo'))->toBeTrue();
});

it('can remove persisted values based on context', function () {
    $userContext = new Context(['user_id' => 1]);
    $user2Context = new Context(['user_id' => 2]);
    SettingsFacade::set('foo', 'bar');
    SettingsFacade::context($userContext)->set('foo', 'user 1 value');
    SettingsFacade::context($user2Context)->set('foo', 'user 2 value');

    expect(SettingsFacade::has('foo'))->toBeTrue()
        ->and(SettingsFacade::context($userContext)->has('foo'))->toBeTrue()
        ->and(SettingsFacade::context($user2Context)->has('foo'))->toBeTrue();

    SettingsFacade::context($user2Context)->forget('foo');

    expect(SettingsFacade::has('foo'))->toBeTrue()
        ->and(SettingsFacade::context($userContext)->has('foo'))->toBeTrue()
        ->and(SettingsFacade::context($user2Context)->has('foo'))->toBeFalse();
});

it('persists values', function () {
    SettingsFacade::set('foo', 'bar');

    expect(DB::table('settings')->count())->toBe(1)
        ->and(SettingsFacade::get('foo'))->toBe('bar');

    SettingsFacade::set('foo', 'updated value');

    expect(DB::table('settings')->count())->toBe(1)
        ->and(SettingsFacade::get('foo'))->toBe('updated value');
});

it('removes persisted values from storage', function () {
    SettingsFacade::set('foo', 'bar');
    SettingsFacade::set('bar', 'foo');

    expect(DB::table('settings')->count())->toBe(2)
        ->and(SettingsFacade::has('foo'))->toBeTrue()
        ->and(SettingsFacade::has('bar'))->toBeTrue();

    SettingsFacade::forget('foo');

    expect(DB::table('settings')->count())->toBe(1)
        ->and(SettingsFacade::has('foo'))->toBeFalse()
        ->and(SettingsFacade::has('bar'))->toBeTrue();
});

it('can evaluate stored boolean settings', function () {
    SettingsFacade::set('app.debug', '1');
    expect(SettingsFacade::isTrue('app.debug'))->toBeTrue();

    SettingsFacade::set('app.debug', '0');
    expect(SettingsFacade::isTrue('app.debug'))->toBeFalse()
        ->and(SettingsFacade::isFalse('app.debug'))->toBeTrue();

    SettingsFacade::set('app.debug', true);
    expect(SettingsFacade::isTrue('app.debug'))->toBeTrue()
        ->and(SettingsFacade::isFalse('app.debug'))->toBeFalse();
});

it('can evaluate boolean stored settings using the json value serializer', function () {
    $settings = settings();
    (fn () => $this->valueSerializer = new JsonValueSerializer)->call($settings);

    $settings->set('app.debug', '1');
    expect($settings->isTrue('app.debug'))->toBeTrue();

    $settings->set('app.debug', '0');
    expect($settings->isTrue('app.debug'))->toBeFalse()
        ->and($settings->isFalse('app.debug'))->toBeTrue();

    $settings->set('app.debug', true);
    expect($settings->isTrue('app.debug'))->toBeTrue()
        ->and($settings->isFalse('app.debug'))->toBeFalse();
});

it('can cache values on retrieval', function () {
    enableSettingsCache();

    SettingsFacade::set('foo', 'bar');

    resetQueryCount();
    expect(SettingsFacade::get('foo'))->toBe('bar');
    assertQueryCount(1);

    resetQueryCount();
    expect(SettingsFacade::get('foo'))->toBe('bar');
    assertQueryCount(0);
});

it('flushes the cache when updating a value', function () {
    enableSettingsCache();

    SettingsFacade::set('foo', 'bar');

    resetQueryCount();
    expect(SettingsFacade::get('foo'))->toBe('bar');
    assertQueryCount(1);

    resetQueryCount();
    expect(SettingsFacade::get('foo'))->toBe('bar');
    assertQueryCount(0);

    SettingsFacade::set('foo', 'updated value');
    resetQueryCount();
    expect(SettingsFacade::get('foo'))->toBe('updated value');
    assertQueryCount(1);
});

it('does not invalidate other cached settings when updating a value', function () {
    enableSettingsCache();

    SettingsFacade::set('foo', 'bar');
    SettingsFacade::set('bar', 'foo');

    resetQueryCount();
    expect(SettingsFacade::get('foo'))->toBe('bar')
        ->and(SettingsFacade::get('bar'))->toBe('foo');
    assertQueryCount(2);

    resetQueryCount();
    expect(SettingsFacade::get('foo'))->toBe('bar')
        ->and(SettingsFacade::get('bar'))->toBe('foo');
    assertQueryCount(0);

    SettingsFacade::set('foo', 'updated value');
    resetQueryCount();
    expect(SettingsFacade::get('foo'))->toBe('updated value')
        ->and(SettingsFacade::get('bar'))->toBe('foo');
    assertQueryCount(1);
});

test('the boolean checks use cached values if cache is enabled', function () {
    enableSettingsCache();

    SettingsFacade::set('true.value', true);
    SettingsFacade::set('false.value', false);

    resetQueryCount();
    expect(SettingsFacade::isTrue('true.value'))->toBeTrue()
        ->and(SettingsFacade::isFalse('false.value'))->toBeTrue();
    assertQueryCount(2);

    resetQueryCount();
    expect(SettingsFacade::isTrue('true.value'))->toBeTrue()
        ->and(SettingsFacade::isFalse('false.value'))->toBeTrue();
    assertQueryCount(0);
});

it('does not use the cache if the cache is disabled', function () {
    SettingsFacade::disableCache();
    DB::enableQueryLog();

    SettingsFacade::set('foo', 'bar');

    resetQueryCount();
    expect(SettingsFacade::get('foo'))->toBe('bar');
    assertQueryCount(1);

    resetQueryCount();
    expect(SettingsFacade::get('foo'))->toBe('bar');
    assertQueryCount(1);
});

it('can encrypt values', function () {
    SettingsFacade::enableEncryption();

    SettingsFacade::set('foo', 'bar');

    $storedSetting = DB::table('settings')->first();
    $unEncrypted = unserialize(decrypt($storedSetting->value));

    expect($unEncrypted)->toBe('bar');
});

it('can decrypt values', function () {
    SettingsFacade::enableEncryption();

    SettingsFacade::set('foo', 'bar');

    // The stored value will be encrypted and not retrieve serialized yet if encryption
    // is enabled.
    $storedSetting = DB::table('settings')->first();
    expect(isSerialized($storedSetting->value))->toBeFalse()
        ->and(SettingsFacade::get('foo'))->toBe('bar');
});

it('does not encrypt if encryption is disabled', function () {
    SettingsFacade::disableEncryption();

    SettingsFacade::set('foo', 'bar');

    $storedSetting = DB::table('settings')->first();

    expect(isSerialized($storedSetting->value))->toBeTrue()
        ->and(unserialize($storedSetting->value))->toBe('bar');
});

it('does not try to decrypt if encryption is disabled', function () {
    SettingsFacade::enableEncryption();
    SettingsFacade::set('foo', 'bar');

    SettingsFacade::disableEncryption();

    $value = SettingsFacade::get('foo');

    expect($value)
        ->not->toBe('bar')
        ->not->toBe(serialize('bar'));
});

test('custom value serializers can be used', function () {
    $settings = settings();
    (fn () => $this->valueSerializer = new JsonValueSerializer)->call($settings);

    $settings->disableEncryption();

    $settings->set('foo', 'my value');
    $settings->set('array-value', ['foo' => 'bar', 'bool' => true]);

    $this->assertDatabaseHas('settings', [
        'value' => '"my value"',
    ]);

    $this->assertDatabaseHas('settings', [
        'value' => '{"foo":"bar","bool":true}',
    ]);

    expect($settings->get('foo'))->toBe('my value')
        ->and($settings->get('array-value'))->toBeArray()
        ->and($settings->get('array-value'))->toMatchArray(['foo' => 'bar', 'bool' => true]);
});

// Helpers...

function assertQueryCount(int $expected): void
{
    test()->expect(DB::getQueryLog())->toHaveCount($expected);
}

function enableSettingsCache(): void
{
    SettingsFacade::enableCache();
    DB::connection()->enableQueryLog();
}

function resetQueryCount(): void
{
    DB::flushQueryLog();
}

/**
 * Determine if something is serialized. Based off a solution from WordPress.
 *
 * @see https://developer.wordpress.org/reference/functions/is_serialized/
 *
 * @param  string|mixed  $data
 */
function isSerialized(mixed $data): bool
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

            return (bool) preg_match("/^{$token}:[0-9.E+-]+;{$end}/", $data);
    }

    return false;
}
