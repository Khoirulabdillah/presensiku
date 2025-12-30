<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Divisi;

class DivisiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisiData = [
            ['nama_divisi' => 'Sekretaris'],
            ['nama_divisi' => 'Humas'],
            ['nama_divisi' => 'Kepala Desa'],
            ['nama_divisi' => 'Administrasi'],
            ['nama_divisi' => 'Keuangan'],
            ['nama_divisi' => 'Kesehatan'],
            ['nama_divisi' => 'Pendidikan'],
        ];

        foreach ($divisiData as $divisi) {
            Divisi::create($divisi);
        }
    }
}
