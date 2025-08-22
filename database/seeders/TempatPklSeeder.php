<?php

namespace Database\Seeders;

use App\Models\Siswa;
use App\Models\TempatPkl;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TempatPklSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $siswa = Siswa::where('id', 843)->first();

        TempatPkl::create([
            'perusahaan_id' => 2,
            'siswa_id' => $siswa->id,
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(7),
            'surat_kesediaan_path' => 'surat_kesediaan.pdf',
            'surat_izin_path' => 'surat_izin.pdf',
            'created_by' => 1,

        ]);
    }
}
