<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('office_settings', function (Blueprint $table) {
            $table->time('jam_masuk')->default('08:00:00');
            $table->time('jam_pulang')->default('17:00:00');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('office_settings', function (Blueprint $table) {
            $table->dropColumn(['jam_masuk', 'jam_pulang']);
        });
    }
};
