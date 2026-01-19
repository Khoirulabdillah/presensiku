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
            $table->string('status')->nullable()->after('type');
            $table->integer('late_minutes')->nullable()->after('status');
            $table->integer('early_minutes')->nullable()->after('late_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            $table->dropColumn(['status', 'late_minutes', 'early_minutes']);
        });
    }
};
