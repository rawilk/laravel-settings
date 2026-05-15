<?php

declare(strict_types=1);

namespace Rawilk\Settings\Exceptions;

use Exception;
use Rawilk\Settings\Contracts\ContextSerializer;
use Rawilk\Settings\Contracts\KeyGenerator;
use Rawilk\Settings\Contracts\Setting;
use Rawilk\Settings\Contracts\ValueSerializer;

final class InvalidConfig extends Exception
{
    public static function invalidEloquentModel(string $modelClass): self
    {
        return new self('Invalid Eloquent settings model class: [' . $modelClass . ']. Your model must implement the [' . Setting::class . '] interface.');
    }

    public static function invalidContextSerializer(string $class): self
    {
        return new self('Invalid context serializer [' . $class . ']. Context serializers must implement the [' . ContextSerializer::class . '] interface.');
    }

    public static function invalidKeyGenerator(string $class): self
    {
        return new self('Invalid key generator [' . $class . ']. Key generators must implement the [' . KeyGenerator::class . '] interface.');
    }

    public static function invalidValueSerializer(string $class): self
    {
        return new self('Invalid value serializer [' . $class . ']. Value serializers must implement the [' . ValueSerializer::class . '] interface.');
    }
}
