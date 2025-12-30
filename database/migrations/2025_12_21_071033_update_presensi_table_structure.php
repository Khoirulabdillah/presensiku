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
        Schema::table('presensi', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn(['waktu_masuk', 'waktu_pulang', 'foto_masuk', 'foto_pulang', 'latitude_masuk', 'latitude_pulang']);

            // Add new columns
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->string('foto_masuk')->nullable();
            $table->string('foto_pulang')->nullable();
            $table->string('type')->nullable(); // 'masuk' or 'pulang'
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            // Drop new columns
            $table->dropColumn(['jam_masuk', 'jam_pulang', 'foto_masuk', 'foto_pulang', 'type', 'latitude', 'longitude']);

            // Add back old columns
            $table->time('waktu_masuk');
            $table->time('waktu_pulang');
            $table->string('foto_masuk');
            $table->string('foto_pulang');
            $table->integer('latitude_masuk');
            $table->integer('latitude_pulang');
        });
    }
};
