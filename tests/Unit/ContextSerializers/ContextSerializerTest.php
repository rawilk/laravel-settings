<?php

declare(strict_types=1);

use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Support\ContextSerializers\ContextSerializer;

it('accepts a context argument', function () {
    $context = (new Context)->set('a', 'a');

    $serializer = new ContextSerializer;

    expect($serializer->serialize($context))->toBe(serialize($context));
});

it('serializes null values', function () {
    $serializer = new ContextSerializer;

    expect($serializer->serialize(null))->toBe(serialize(null));
});
