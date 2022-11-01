<?php

use Rawilk\Settings\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

// Helpers...
if (! function_exists('fake') && class_exists(\Faker\Factory::class)) {
    /**
     * Ensure the fake method exists. If we ever drop laravel 8 support, we can remove this helper.
     */
    function fake($locale = null)
    {
        $locale ??= app('config')->get('app.faker_locale') ?? 'en_US';

        $abstract = \Faker\Generator::class . ':' . $locale;

        if (! app()->bound($abstract)) {
            app()->singleton($abstract, fn () => \Faker\Factory::create($locale));
        }

        return app()->make($abstract);
    }
}
