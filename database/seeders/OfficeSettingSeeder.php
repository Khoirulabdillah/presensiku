<?php

namespace Database\Seeders;

use App\Models\OfficeSetting;
use Illuminate\Database\Seeder;

class OfficeSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OfficeSetting::updateOrCreate(
            [], // No conditions, so it will update the first or create if none
            [
                'latitude' => -6.3385652,
                'longitude' => 106.9447146,
                'radius' => 100,
            ]
        );
    }
}