<?php

declare(strict_types=1);

namespace Rawilk\Settings\Support\ValueSerializers;

use Rawilk\Settings\Contracts\ValueSerializer as ValueSerializerContract;

class ValueSerializer implements ValueSerializerContract
{
    public function serialize($value): string
    {
        return serialize($value);
    }

    public function unserialize(string $serialized): mixed
    {
        return unserialize($serialized, ['allowed_classes' => false]);
    }
}
