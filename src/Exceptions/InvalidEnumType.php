<?php

declare(strict_types=1);

namespace Rawilk\Settings\Exceptions;

use InvalidArgumentException;

final class InvalidEnumType extends InvalidArgumentException
{
    public static function make(string $enumClass): self
    {
        return new self("Only string backed enums are supported. `{$enumClass}` is not a string backed enum.");
    }
}
