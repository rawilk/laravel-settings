<?php

declare(strict_types=1);

namespace Rawilk\Settings\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

final class InvalidBulkValueResult extends Exception
{
    public static function notObject(): self
    {
        return new self('A record returned from `all()` must be an array, object, or inherit from ' . Model::class);
    }

    public static function missingValueOrKey(): self
    {
        return new self('A record returned from `all()` must have a `value` and `key` property.');
    }
}
