<?php

declare(strict_types=1);

use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Support\ContextSerializers\ContextSerializer;
use Rawilk\Settings\Support\ContextSerializers\DotNotationContextSerializer;
use Rawilk\Settings\Support\KeyGenerators\Md5KeyGenerator;

beforeEach(function () {
    $this->keyGenerator = (new Md5KeyGenerator)
        ->setContextSerializer(new ContextSerializer);
});

it('generates an md5 hash of a key', function () {
    // N; is for a serialized null context object
    expect($this->keyGenerator->generate('my-key'))->toBe(md5('my-keyN;'));
});

it('generates an md5 hash of a key and context object', function () {
    $context = new Context([
        'id' => 123,
    ]);

    expect($this->keyGenerator->generate('my-key', $context))
        ->toBe(md5('my-key' . serialize($context)));
});

it('works with other context serializers', function () {
    $this->keyGenerator->setContextSerializer(new DotNotationContextSerializer);

    $context = new Context([
        'id' => 123,
        'bool-value' => false,
    ]);

    expect($this->keyGenerator->generate('my-key', $context))
        ->toBe(md5('my-keyid:123::bool-value:0'));
});
