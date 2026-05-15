<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Rawilk\Settings\Facades\Settings;
use Rawilk\Settings\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

// Helpers...

/**
 * The Database driver doesn't seem to be using the same Sqlite connection
 * the tests are using, so we'll force it to here. This should fix issues
 * with the settings table not existing when the driver queries it.
 */
function setDatabaseDriverConnection(): void
{
    (fn () => $this->connection = DB::connection())->call(Settings::driver('database'));
}
