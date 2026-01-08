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
        // NOTE: Changing column type requires the doctrine/dbal package (composer require doctrine/dbal)
        Schema::table('office_settings', function (Blueprint $table) {
            // increase precision to keep more decimal places for coordinates
            $table->decimal('latitude', 18, 15)->change();
            $table->decimal('longitude', 18, 15)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('office_settings', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->change();
            $table->decimal('longitude', 11, 8)->change();
        });
    }
};
