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
        Schema::create('office_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('radius')->default(50);
            // jam kerja: jam masuk dan jam pulang
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->integer('batas_awal_masuk')->default(30); // menit sebelum jam masuk
            $table->integer('toleransi_terlambat')->default(15); // menit setelah jam masuk
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_settings');
    }
};