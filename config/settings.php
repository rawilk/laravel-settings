<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Settings Table
    |--------------------------------------------------------------------------
    |
    | Database table used to store settings in.
    |
    */
    'table' => 'settings',

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | If enabled, all settings are cached after accessing them.
    |
    */
    'cache' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | Specify a prefix to prepend to any setting key being cached.
    |
    */
    'cache_key_prefix' => 'settings.',

    /*
    |--------------------------------------------------------------------------
    | Encryption
    |--------------------------------------------------------------------------
    |
    | If enabled, all values are encrypted and decrypted.
    |
    */
    'encryption' => true,

    /*
    |--------------------------------------------------------------------------
    | Driver
    |--------------------------------------------------------------------------
    |
    | The driver to use to store and retrieve settings from. You are free
    | to add more drivers in the `drivers` configuration below.
    |
    */
    'driver' => env('SETTINGS_DRIVER', 'eloquent'),

    /*
    |--------------------------------------------------------------------------
    | Drivers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the driver information for each repository that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with this package. You are free to add more.
    |
    | Each driver you add must implement the \Rawilk\Settings\Contracts\Driver interface.
    |
    */
    'drivers' => [
        'database' => [
            'driver' => 'database',
            'connection' => env('DB_CONNECTION', 'mysql'),
        ],
        'eloquent' => [
            'driver' => 'eloquent',

            /*
             * You can use any model you like for the setting, but it needs to implement
             * the \Rawilk\Settings\Contracts\Setting interface.
             */
            'model' => \Rawilk\Settings\Models\Setting::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Teams
    |--------------------------------------------------------------------------
    |
    | When set to true the package implements teams using the `team_foreign_key`.
    |
    | If you want the migrations to register the `team_foreign_key`, you must
    | set this to true before running the migration.
    |
    | If you already ran the migrations, then you must make a new migration to
    | add the `team_foreign_key` column to the settings table, and update the
    | unique constraint on the table. See the `add_settings_team_field` migration
    | for how to do this.
    |
    */
    'teams' => false,

    /*
    |--------------------------------------------------------------------------
    | Team Foreign Key
    |--------------------------------------------------------------------------
    |
    | When teams is set to true, our database/eloquent drivers will use this
    | column as a team foreign key to scope queries to.
    |
    | The team id will also be included in a cache key when caching is enabled.
    |
    */
    'team_foreign_key' => 'team_id',

    /*
    |--------------------------------------------------------------------------
    | Context Serializer
    |--------------------------------------------------------------------------
    |
    | The context serializer is responsible for converting a Context object
    | into a string, which gets appended to a setting key in the database.
    |
    | Any custom serializer you use must implement the
    | \Rawilk\Settings\Contracts\ContextSerializer interface.
    |
    | Supported:
    | - \Rawilk\Settings\Support\ContextSerializers\ContextSerializer (default)
    | - \Rawilk\Settings\Support\ContextSerializers\DotNotationContextSerializer
    |
    */
    'context_serializer' => \Rawilk\Settings\Support\ContextSerializers\ContextSerializer::class,

    /*
    |--------------------------------------------------------------------------
    | Key Generator
    |--------------------------------------------------------------------------
    |
    | The key generator is responsible for generating a suitable key for a
    | setting.
    |
    | Any custom key generator you use must implement the
    | \Rawilk\Settings\Contracts\KeyGenerator interface.
    |
    | Supported:
    | - \Rawilk\Settings\Support\KeyGenerators\ReadableKeyGenerator
    | - \Rawilk\Settings\Support\KeyGenerators\Md5KeyGenerator (default)
    |
    */
    'key_generator' => \Rawilk\Settings\Support\KeyGenerators\Md5KeyGenerator::class,

    /*
    |--------------------------------------------------------------------------
    | Value Serializer
    |--------------------------------------------------------------------------
    |
    | By default, we use php's serialize() and unserialize() functions to
    | prepare the setting values for storage. You may use the `JsonValueSerializer`
    | instead if you want to store the values as json instead.
    |
    | Any custom value serializer you use must implement the
    | \Rawilk\Settings\Contracts\ValueSerializer interface.
    |
    */
    'value_serializer' => \Rawilk\Settings\Support\ValueSerializers\ValueSerializer::class,

    /*
    |--------------------------------------------------------------------------
    | Cache Default Value
    |--------------------------------------------------------------------------
    |
    | When a setting is not persisted, we will cache the passed in default value
    | if this is set to true. This may not always be desirable, so you can
    | disable it here if needed.
    |
    */
    'cache_default_value' => true,

    /*
    |--------------------------------------------------------------------------
    | Unserialize Safelist
    |--------------------------------------------------------------------------
    |
    | When using the default value serializer class from this package, we
    | will only unserialize objects that have their classes safelisted here.
    | Any other objects will be unserialized to something like:
    | __PHP_Incomplete_Class(App\Models\User) {...}
    |
    | To prevent any objects from being unserialized, simply set this to
    | an empty array.
    */
    'unserialize_safelist' => [
        \Carbon\Carbon::class,
        \Carbon\CarbonImmutable::class,
        \Illuminate\Support\Carbon::class,
    ],
];
