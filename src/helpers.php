<?php

use Rawilk\Settings\Settings;
use Rawilk\Settings\Support\Context;

if (! function_exists('settings')) {
    function settings($key = null, $default = null, $context = null)
    {
        /** @var \Rawilk\Settings\Settings $settings */
        $settings = app(Settings::class);

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

        if ($context instanceof Context) {
            $settings->context($context);
        }

        return $settings->get(key: $key, default: $default);
    }
}
