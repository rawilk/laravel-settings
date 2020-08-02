<?php

namespace Rawilk\Settings\Tests\Unit;

use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Support\ContextSerializer;
use Rawilk\Settings\Tests\TestCase;

class ContextSerializerTest extends TestCase
{
    /** @test */
    public function it_accepts_a_context_argument(): void
    {
        $context = (new Context)->set('a', 'a');

        $serializer = new ContextSerializer;

        self::assertEquals(
            serialize($context),
            $serializer->serialize($context)
        );
    }

    /** @test */
    public function it_serializes_null_values(): void
    {
        $serializer = new ContextSerializer;

        self::assertEquals(
            serialize(null),
            $serializer->serialize(null)
        );
    }
}
