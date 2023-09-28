<?php

declare(strict_types=1);

namespace Rawilk\Settings\Support\ValueSerializers;

use Rawilk\Settings\Contracts\ValueSerializer as ValueSerializerContract;

class JsonValueSerializer implements ValueSerializerContract
{
    public function serialize($value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    public function unserialize(string $serialized): mixed
    {
        return json_decode($serialized, true, 512, JSON_THROW_ON_ERROR);
    }
}
