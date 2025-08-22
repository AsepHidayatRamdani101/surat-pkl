<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil jurusan untuk dihubungkan dengan kelas
        $tjkt = Jurusan::where('id', 1)->first();
        $tkr = Jurusan::where('id', 2)->first();
        $dkv = Jurusan::where('id', 3)->first();
        $mplb = Jurusan::where('id', 4)->first();


        // Membuat kelas untuk jurusan Teknik Jaringan Komputer dan Telekomunikasi
        $tjkt->kelas()->create([
            'nama_kelas' => 'XI TJKT 1',
            'tingkat' => 11,

        ]);

        $tjkt->kelas()->create([
            'nama_kelas' => 'XI TJKT 2',
            'tingkat' => 11,
        ]);

        $tjkt->kelas()->create([
            'nama_kelas' => 'XI TJKT 3',
            'tingkat' => 11,
        ]);

        // Membuat kelas untuk jurusan Teknik Kendaraan Ringan
        $tkr->kelas()->create([
            'nama_kelas' => 'XI TKR 1',
            'tingkat' => 11,
        ]);

        $tkr->kelas()->create([
            'nama_kelas' => 'XI TKR 2',
            'tingkat' => 11,
        ]);

        $tkr->kelas()->create([
            'nama_kelas' => 'XI TKR 3',
            'tingkat' => 11,
        ]);

        // Membuat kelas untuk jurusan Desain Komunikasi Visual
        $dkv->kelas()->create([
            'nama_kelas' => 'XI DKV 1',
            'tingkat' => 11,
        ]);

        $dkv->kelas()->create([
            'nama_kelas' => 'XI DKV 2',
            'tingkat' => 11,
        ]);

        $dkv->kelas()->create([
            'nama_kelas' => 'XI DKV 3',
            'tingkat' => 11,
        ]);

        // Membuat kelas untuk jurusan Manajemen Perkantoran dan Layanan Bisnis
        $mplb->kelas()->create([
            'nama_kelas' => 'XI MPLB 1',
            'tingkat' => 11,
        ]);

        $mplb->kelas()->create([
            'nama_kelas' => 'XI MPLB 2',
            'tingkat' => 11,
        ]);
    }
}
