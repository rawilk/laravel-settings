<?php

declare(strict_types=1);

namespace Rawilk\Settings\Contracts;

interface ValueSerializer
{
    public function serialize($value): string;

    public function unserialize(string $serialized): mixed;
}
