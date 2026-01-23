<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pegawai;
use App\Models\Presensi;
use Carbon\Carbon;

class PresensiDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure there's at least one divisi to attach to
        $divisi = \App\Models\Divisi::first();

        // Ensure there's at least one user to satisfy non-null users_id foreign key
        $user = \App\Models\User::first();
        if (! $user) {
            $user = \App\Models\User::create([
                'name' => 'Seeder User',
                'email' => 'seeder@example.test',
                'password' => bcrypt('password'),
            ]);
        }

        for ($i = 1; $i <= 5; $i++) {
            $nip = 'NIP' . str_pad($i, 4, '0', STR_PAD_LEFT);

            $pegawai = Pegawai::updateOrCreate(
                ['nip' => $nip],
                [
                    'divisi_id' => $divisi?->id,
                    'users_id' => $user->id,
                    'nip' => $nip,
                    'nama_pegawai' => "Pegawai {$i}",
                    'jabatan' => ['Staff', 'Supervisor', 'Manager'][($i - 1) % 3],
                    'foto_wajah_asli' => null,
                    'foto_wajah_encoding' => null,
                ]
            );

            // Create presensi for the last 7 days
            for ($d = 0; $d < 7; $d++) {
                $date = Carbon::now()->subDays($d)->startOfDay();

                // Simulate arrival between 07:45 and 09:15
                $minutesAfter8 = rand(-15, 75); // can be early (-15) to late (75)
                $jamMasuk = $date->copy()->addHours(8)->addMinutes($minutesAfter8);

                $jamPulang = $date->copy()->addHours(17)->addMinutes(rand(-30, 30));

                $late = max(0, $minutesAfter8);
                $status = $late > 15 ? 'Terlambat' : 'Tepat Waktu';

                Presensi::updateOrCreate([
                    'nip' => $nip,
                    'tanggal_presensi' => $date->toDateString(),
                ], [
                    'nip' => $nip,
                    'tanggal_presensi' => $date->toDateString(),
                    'jam_masuk' => $jamMasuk->format('H:i'),
                    'jam_pulang' => $jamPulang->format('H:i'),
                    'foto_masuk' => null,
                    'foto_pulang' => null,
                    'type' => 'masuk',
                    'latitude' => round(-6.200000 + (rand(-100, 100) / 10000), 6),
                    'longitude' => round(106.816666 + (rand(-100, 100) / 10000), 6),
                    'status' => $status,
                    'late_minutes' => $late,
                    'early_minutes' => 0,
                ]);
            }
        }
    }
}
