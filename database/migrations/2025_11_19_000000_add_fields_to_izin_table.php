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
        if (! Schema::hasTable('izin')) {
            return;
        }

        Schema::table('izin', function (Blueprint $table) {
            if (! Schema::hasColumn('izin', 'tanggal_mulai')) {
                $table->date('tanggal_mulai')->nullable()->after('jenis_izin');
            }
            if (! Schema::hasColumn('izin', 'tanggal_selesai')) {
                $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
            }
            if (! Schema::hasColumn('izin', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('tanggal_selesai');
            }
            if (! Schema::hasColumn('izin', 'bukti_path')) {
                $table->string('bukti_path')->nullable()->after('keterangan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('izin')) {
            return;
        }

        Schema::table('izin', function (Blueprint $table) {
            if (Schema::hasColumn('izin', 'tanggal_mulai')) {
                $table->dropColumn('tanggal_mulai');
            }
            if (Schema::hasColumn('izin', 'tanggal_selesai')) {
                $table->dropColumn('tanggal_selesai');
            }
            if (Schema::hasColumn('izin', 'keterangan')) {
                $table->dropColumn('keterangan');
            }
            if (Schema::hasColumn('izin', 'bukti_path')) {
                $table->dropColumn('bukti_path');
            }
        });
    }
};
