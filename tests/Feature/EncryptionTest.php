<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

describe('encryption enabled', function () {
    beforeEach(function () {
        config([
            'settings.encryption' => true,
        ]);
    });

    it('encrypts values', function () {
        settings()->set('foo', 'bar');

        $storedSetting = DB::table('settings')->first();
        $decryptedValue = json_decode(Crypt::decrypt($storedSetting->value));

        expect($decryptedValue)->toBe('bar');
    });

    it('decrypts values', function () {
        settings()->set('foo', 'bar');

        // The stored value will be encrypted, so when we retrieve it, it won't be json_encoded
        // until it is decrypted.
        $storedSetting = DB::table('settings')->first();
        expect($storedSetting->value)->not->toBeJson()
            ->and(settings()->get('foo'))->toBe('bar');
    });

    it('will not try to decrypt encrypted values if encryption is disabled', function () {
        settings()->set('foo', 'bar');

        settings()->disableEncryption();

        $value = settings()->get('foo');

        expect($value)
            ->not->toBe('bar')
            ->not->toBe(json_encode('bar'));
    });

    it('will disable encryption for a callback', function () {
        settings()->withoutEncryption(function () {
            settings()->set('one', 'value one');
        });

        $setting = DB::table('settings')->first();

        expect($setting->value)->toBeJson()
            ->and(json_decode($setting->value))->toBe('value one');
    });

    it('will disable encryption for a callback without affecting previous state', function () {
        settings()->withoutEncryption(function () {
            settings()->set('one', 'value one');
        });

        $setting = DB::table('settings')->first();

        expect($setting->value)->toBeJson()
            ->and(json_decode($setting->value))->toBe('value one');

        settings()->set('two', 'value two');

        expect(DB::table('settings')->where('key', 'two')->first()->value)->not->toBeJson();
    });
});

describe('encryption disabled', function () {
    beforeEach(function () {
        config([
            'settings.encryption' => false,
        ]);
    });

    it('does not encrypt if encryption is disabled', function () {
        settings()->set('foo', 'bar');

        $storedSetting = DB::table('settings')->first();

        expect($storedSetting->value)->toBeJson()
            ->and(json_decode($storedSetting->value))->toBe('bar');
    });

    it('will disable encryption for a callback without affecting previous state even with exception', function () {
        settings()->disableEncryption();

        try {
            settings()->withoutEncryption(function () {
                settings()->set('inner', 'inner value');

                throw new Exception('Failed');
            });
        } catch (Throwable) {
        }

        $storedSetting = DB::table('settings')->where('key', 'inner')->first();

        expect($storedSetting->value)->toBeJson()
            ->and(json_decode($storedSetting->value))->toBe('inner value');

        settings()->set('outer', 'outer value');

        $storedSetting = DB::table('settings')->where('key', 'outer')->first();

        expect($storedSetting->value)->toBeJson()
            ->and(json_decode($storedSetting->value))->toBe('outer value');
    });
});
