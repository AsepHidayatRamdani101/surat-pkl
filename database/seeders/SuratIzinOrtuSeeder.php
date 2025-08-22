<?php

namespace Database\Seeders;

use App\Models\Siswa;
use App\Models\SuratIzinOrtu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuratIzinOrtuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $siswa = Siswa::where('id', 1)->first();

        SuratIzinOrtu::create([
            'nomor_surat' => '123',
            'siswa_id' => $siswa->id,
            'tanggal_surat' => now(),
            'nama_ortu' => 'John Doe',
            'alamat_ortu' => '123 Main St',
        ]);
    }
}
