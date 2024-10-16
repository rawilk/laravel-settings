<?php

declare(strict_types=1);

use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Support\ContextSerializers\ContextSerializer;
use Rawilk\Settings\Support\ContextSerializers\DotNotationContextSerializer;
use Rawilk\Settings\Support\KeyGenerators\HashKeyGenerator;

beforeEach(function () {
    $this->keyGenerator = (new HashKeyGenerator)
        ->setContextSerializer(new ContextSerializer);

    config([
        'settings.hash_algorithm' => 'xxh128',
    ]);
});

it('generates a hash of a key', function () {
    // N; is for a serialized null context object
    expect($this->keyGenerator->generate('my-key'))->toBe(hash('xxh128', 'my-keyN;'));
});

it('generates a hash of a key and context object', function () {
    $context = new Context([
        'id' => 123,
    ]);

    expect($this->keyGenerator->generate('my-key', $context))
        ->toBe(hash('xxh128', 'my-key' . serialize($context)));
});

it('works with other context serializers', function () {
    $this->keyGenerator->setContextSerializer(new DotNotationContextSerializer);

    $context = new Context([
        'id' => 123,
        'bool-value' => false,
    ]);

    expect($this->keyGenerator->generate('my-key', $context))
        ->toBe(hash('xxh128', 'my-keyid:123::bool-value:0'));
});
