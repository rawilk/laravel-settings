<?php

declare(strict_types=1);

use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Support\ContextSerializer;
use Rawilk\Settings\Support\KeyGenerator;

afterEach(function () {
    Mockery::close();
});

it('calls serializer when generating a key', function () {
    $context = new Context;

    $serializer = Mockery::mock(ContextSerializer::class);
    $serializer->shouldReceive('serialize')
        ->with($context)
        ->andReturn('serialized');

    $generator = new KeyGenerator($serializer);

    expect($generator->generate('key', $context))->toBe(md5('keyserialized'));
});
