<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JurusanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Jurusan::create(['nama_jurusan' => 'Teknik Jaringan Komputer dan Telekomunikasi', 'kode_jurusan' => 'TJKT']);
        Jurusan::create(['nama_jurusan' => 'Teknik Kendaraan Ringan', 'kode_jurusan' => 'TKR']);
        Jurusan::create(['nama_jurusan' => 'Desain Komunikasi Visual', 'kode_jurusan' => 'DKV']);
        Jurusan::create(['nama_jurusan' => 'Manajemen Perkantoran dan Layanan Bisnis', 'kode_jurusan' => 'MPLB']);
    }
}
