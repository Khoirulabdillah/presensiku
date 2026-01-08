<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->text('foto_wajah_encoding')->nullable()->after('foto_wajah_asli');
        });
    }

    public function down()
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->dropColumn('foto_wajah_encoding');
        });
    }
};
