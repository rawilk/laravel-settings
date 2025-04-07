<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('settings.table'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key')->unique()->index();
            $table->longText('value')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('settings.table'));
    }
};
