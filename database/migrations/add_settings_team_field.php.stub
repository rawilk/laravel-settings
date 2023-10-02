<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! config('settings.teams')) {
            return;
        }

        Schema::table(config('settings.table'), function (Blueprint $table) {
            $table->unsignedBigInteger(config('settings.team_foreign_key'))->nullable()->after('id');
            $table->index(config('settings.team_foreign_key'), 'settings_team_id_index');

            $table->dropUnique('settings_key_unique');

            $table->unique([
                'key',
                config('settings.team_foreign_key'),
            ], 'settings_key_team_id_unique');
        });
    }
};
