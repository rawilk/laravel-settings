<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Rawilk\Settings\Facades\Settings;
use Rawilk\Settings\Support\Context;

beforeEach(function () {
    config([
        'settings.driver' => 'database',
        'settings.table' => 'settings',
        'settings.cache' => false,
        'settings.encryption' => false,
    ]);
});

it('can determine if a setting has been persisted', function () {
    expect(Settings::has('foo'))->toBeFalse();

    Settings::set('foo', 'bar');

    expect(Settings::has('foo'))->toBeTrue();

    DB::table('settings')->truncate();

    expect(Settings::has('foo'))->toBeFalse();
});

it('gets persisted setting values', function () {
    Settings::set('foo', 'bar');

    expect(Settings::get('foo'))->toBe('bar');
});

it('returns a default value if a setting is not persisted', function () {
    expect(Settings::get('foo', 'default value'))->toBe('default value');
});

it('can retrieve values based on context', function () {
    Settings::set('foo', 'bar');

    $userContext = new Context(['user_id' => 1]);
    Settings::context($userContext)->set('foo', 'user_1_value');

    expect(DB::table('settings')->count())->toBe(2)
        ->and(Settings::get('foo'))->toBe('bar')
        ->and(Settings::context($userContext)->get('foo'))->toBe('user_1_value');
});

it('can determine if a setting is persisted based on context', function () {
    Settings::set('foo', 'bar');

    $userContext = new Context(['user_id' => 1]);
    $user2Context = new Context(['user_id' => 2]);

    expect(Settings::has('foo'))->toBeTrue()
        ->and(Settings::context($userContext)->has('foo'))->toBeFalse();

    Settings::context($userContext)->set('foo', 'user 1 value');

    expect(Settings::context($userContext)->has('foo'))->toBeTrue()
        ->and(Settings::context($user2Context)->has('foo'))->toBeFalse();

    Settings::context($user2Context)->set('foo', 'user 2 value');

    expect(Settings::context($userContext)->has('foo'))->toBeTrue()
        ->and(Settings::context($user2Context)->has('foo'))->toBeTrue()
        ->and(Settings::has('foo'))->toBeTrue();
});

it('can remove persisted values based on context', function () {
    $userContext = new Context(['user_id' => 1]);
    $user2Context = new Context(['user_id' => 2]);
    Settings::set('foo', 'bar');
    Settings::context($userContext)->set('foo', 'user 1 value');
    Settings::context($user2Context)->set('foo', 'user 2 value');

    expect(Settings::has('foo'))->toBeTrue()
        ->and(Settings::context($userContext)->has('foo'))->toBeTrue()
        ->and(Settings::context($user2Context)->has('foo'))->toBeTrue();

    Settings::context($user2Context)->forget('foo');

    expect(Settings::has('foo'))->toBeTrue()
        ->and(Settings::context($userContext)->has('foo'))->toBeTrue()
        ->and(Settings::context($user2Context)->has('foo'))->toBeFalse();
});

it('persists values', function () {
    Settings::set('foo', 'bar');

    expect(DB::table('settings')->count())->toBe(1)
        ->and(Settings::get('foo'))->toBe('bar');

    Settings::set('foo', 'updated value');

    expect(DB::table('settings')->count())->toBe(1)
        ->and(Settings::get('foo'))->toBe('updated value');
});

it('removes persisted values from storage', function () {
    Settings::set('foo', 'bar');
    Settings::set('bar', 'foo');

    expect(DB::table('settings')->count())->toBe(2)
        ->and(Settings::has('foo'))->toBeTrue()
        ->and(Settings::has('bar'))->toBeTrue();

    Settings::forget('foo');

    expect(DB::table('settings')->count())->toBe(1)
        ->and(Settings::has('foo'))->toBeFalse()
        ->and(Settings::has('bar'))->toBeTrue();
});

it('can evaluate stored boolean settings', function () {
    Settings::set('app.debug', '1');
    expect(Settings::isTrue('app.debug'))->toBeTrue();

    Settings::set('app.debug', '0');
    expect(Settings::isTrue('app.debug'))->toBeFalse()
        ->and(Settings::isFalse('app.debug'))->toBeTrue();

    Settings::set('app.debug', true);
    expect(Settings::isTrue('app.debug'))->toBeTrue()
        ->and(Settings::isFalse('app.debug'))->toBeFalse();
});

it('can cache values on retrieval', function () {
    enableSettingsCache();

    Settings::set('foo', 'bar');

    resetQueryCount();
    expect(Settings::get('foo'))->toBe('bar');
    assertQueryCount(1);

    resetQueryCount();
    expect(Settings::get('foo'))->toBe('bar');
    assertQueryCount(0);
});

it('flushes the cache when updating a value', function () {
    enableSettingsCache();

    Settings::set('foo', 'bar');

    resetQueryCount();
    expect(Settings::get('foo'))->toBe('bar');
    assertQueryCount(1);

    resetQueryCount();
    expect(Settings::get('foo'))->toBe('bar');
    assertQueryCount(0);

    Settings::set('foo', 'updated value');
    resetQueryCount();
    expect(Settings::get('foo'))->toBe('updated value');
    assertQueryCount(1);
});

it('does not invalidate other cached settings when updating a value', function () {
    enableSettingsCache();

    Settings::set('foo', 'bar');
    Settings::set('bar', 'foo');

    resetQueryCount();
    expect(Settings::get('foo'))->toBe('bar')
        ->and(Settings::get('bar'))->toBe('foo');
    assertQueryCount(2);

    resetQueryCount();
    expect(Settings::get('foo'))->toBe('bar')
        ->and(Settings::get('bar'))->toBe('foo');
    assertQueryCount(0);

    Settings::set('foo', 'updated value');
    resetQueryCount();
    expect(Settings::get('foo'))->toBe('updated value')
        ->and(Settings::get('bar'))->toBe('foo');
    assertQueryCount(1);
});

test('the boolean checks use cached values if cache is enabled', function () {
    enableSettingsCache();

    Settings::set('true.value', true);
    Settings::set('false.value', false);

    resetQueryCount();
    expect(Settings::isTrue('true.value'))->toBeTrue()
        ->and(Settings::isFalse('false.value'))->toBeTrue();
    assertQueryCount(2);

    resetQueryCount();
    expect(Settings::isTrue('true.value'))->toBeTrue()
        ->and(Settings::isFalse('false.value'))->toBeTrue();
    assertQueryCount(0);
});

it('does not use the cache if the cache is disabled', function () {
    Settings::disableCache();
    DB::enableQueryLog();

    Settings::set('foo', 'bar');

    resetQueryCount();
    expect(Settings::get('foo'))->toBe('bar');
    assertQueryCount(1);

    resetQueryCount();
    expect(Settings::get('foo'))->toBe('bar');
    assertQueryCount(1);
});

it('can encrypt values', function () {
    Settings::enableEncryption();

    Settings::set('foo', 'bar');

    $storedSetting = DB::table('settings')->first();
    $unEncrypted = unserialize(decrypt($storedSetting->value));

    expect($unEncrypted)->toBe('bar');
});

it('can decrypt values', function () {
    Settings::enableEncryption();

    Settings::set('foo', 'bar');

    // The stored value will be encrypted and not retrieve serialized yet if encryption
    // is enabled.
    $storedSetting = DB::table('settings')->first();
    expect(isSerialized($storedSetting->value))->toBeFalse()
        ->and(Settings::get('foo'))->toBe('bar');
});

it('does not encrypt if encryption is disabled', function () {
    Settings::disableEncryption();

    Settings::set('foo', 'bar');

    $storedSetting = DB::table('settings')->first();

    expect(isSerialized($storedSetting->value))->toBeTrue()
        ->and(unserialize($storedSetting->value))->toBe('bar');
});

it('does not try to decrypt if encryption is disabled', function () {
    Settings::enableEncryption();
    Settings::set('foo', 'bar');

    Settings::disableEncryption();

    expect(Settings::get('foo'))->not()->toBe('bar')
        ->and(Settings::get('foo'))->not()->toBe(serialize('bar'));
});

// Helpers...

function assertQueryCount(int $expected): void
{
    test()->expect(DB::getQueryLog())->toHaveCount($expected);
}

function enableSettingsCache(): void
{
    Settings::enableCache();
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
 * @return bool
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

            return (bool) preg_match("/^{$token}:[0-9.E+-]+;$end/", $data);
    }

    return false;
}
