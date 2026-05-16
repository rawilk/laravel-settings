<?php

declare(strict_types=1);

use Rawilk\Settings\Support\Context;
use Rawilk\Settings\Support\PendingSettings;

if (! function_exists('settings')) {
    function settings($key = null, $default = null, $context = null): mixed
    {
        /** @var PendingSettings $settings */
        $settings = app(PendingSettings::class);

        // If nothing is passed in to the function, simply return the settings instance.
        if ($key === null) {
            return $settings;
        }

        // If an array is passed, we are setting values.
        if (is_array($key)) {
            foreach ($key as $name => $value) {
                if ($context instanceof Context) {
                    $settings->context($context);
                }

                $settings->set(key: $name, value: $value);
            }

            return null;
        }

        if ($context instanceof Context || is_bool($context)) {
            $settings->context($context);
        }

        return $settings->get(key: $key, default: $default);
    }
}

if (! function_exists('settings_enum_value')) {
    /**
     * Return a scalar value for the given value that might be an enum.
     *
     * @internal
     *
     * @template TValue
     * @template TDefault
     *
     * @param  TValue  $value
     * @param  TDefault|callable(TValue): TDefault  $default
     * @return ($value is empty ? TDefault : mixed)
     */
    function settings_enum_value($value, mixed $default = null): mixed
    {
        return match (true) {
            $value instanceof BackedEnum => $value->value,
            $value instanceof UnitEnum => $value->name,

            default => $value ?? value($default),
        };
    }
}
