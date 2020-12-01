<?php

namespace Rawilk\Settings\Tests\Unit;

use Rawilk\Settings\Support\ValueSerializer;
use Rawilk\Settings\Tests\TestCase;

class ValueSerializerTest extends TestCase
{
    /**
     * @test
     * @param mixed $value
     * @dataProvider valuesToTest
     */
    public function it_serializes_values(mixed $value): void
    {
        $serializer = new ValueSerializer;

        self::assertEquals(
            serialize($value),
            $serializer->serialize($value)
        );
    }

    /**
     * @test
     * @param mixed $value
     * @dataProvider valuesToTest
     */
    public function it_unserializes_values(mixed $value): void
    {
        $serializer = new ValueSerializer;

        $serialized = serialize($value);

        self::assertEquals(
            $value,
            $serializer->unserialize($serialized)
        );
    }

    public function valuesToTest(): array
    {
        return [
            [null],
            [1],
            [1.1],
            ['string'],
            [['array' => 'array']],
            [(object) ['a' => 'b']],
        ];
    }
}
