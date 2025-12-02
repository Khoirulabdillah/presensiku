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
        Schema::create('presensi', function (Blueprint $table) {
            $table->id();
            $table->string('nip');
            $table->date('tanggal_presensi');
            $table->time('waktu_masuk');
            $table->time('waktu_pulang');
            $table->string('foto_masuk');
            $table->string('foto_pulang');
            $table->integer('latitude_masuk');
            $table->integer('latitude_pulang');
            $table->timestamps();
            
            $table->foreign('nip')->references('nip')->on('pegawai')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};
