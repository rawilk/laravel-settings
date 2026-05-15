<?php

declare(strict_types=1);

use Rawilk\Settings\Contracts\ValueSerializer;
use Rawilk\Settings\Settings;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    config([
        'settings.encryption' => false,
    ]);

    $this->settings = app(Settings::class);
});

test('custom value serializers can be used', function () {
    $valueSerializer = new class implements ValueSerializer
    {
        public function serialize($value): string
        {
            return 'serialized';
        }

        public function unserialize(string $serialized): mixed
        {
            return 'unserialized';
        }
    };

    $settings = settings();

    $settings->setValueSerializer($valueSerializer);

    $settings->set('foo', 'bar');

    assertDatabaseHas('settings', [
        'value' => 'serialized',
    ]);

    expect($settings->get('foo'))->toBe('unserialized');
});
