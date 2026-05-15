<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Rawilk\Settings\Support\SettingsConfig;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(SettingsConfig::getSettingsTable(), function (Blueprint $table) {
            $table->unsignedBigInteger(SettingsConfig::getTeamsForeignKey())->nullable();
            $table->index(SettingsConfig::getTeamsForeignKey(), 'settings_team_id_index');

            $table->dropUnique('settings_key_unique');

            $table->unique([
                'key',
                SettingsConfig::getTeamsForeignKey(),
            ], 'settings_key_team_id_unique');
        });
    }
};
