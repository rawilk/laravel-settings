<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Rawilk\Settings\Support\SettingsConfig;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(SettingsConfig::getSettingsTable(), function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->index();
            $table->longText('value')->nullable();
        });
    }
};
