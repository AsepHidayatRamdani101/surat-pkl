<?php

namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tjkt1 = Kelas::where('id', 1)->first();
        $tjkt2 = Kelas::where('id', 2)->first();
        $tjkt3 = Kelas::where('id', 3)->first();

        $tkr1 = Kelas::where('id', 4)->first();
        $tkr2 = Kelas::where('id', 5)->first();
        $tkr3 = Kelas::where('id', 6)->first();

        $dkv1 = Kelas::where('id', 7)->first();
        $dkv2 = Kelas::where('id', 8)->first();
        $dkv3 = Kelas::where('id', 9)->first();

        $mplb1 = Kelas::where('id', 10)->first();
        $mplb2 = Kelas::where('id', 11)->first();


        $tjkt1->siswa()->create([
            'nis' => '123',
            'nama_siswa' => 'John Doe',
        ]);

        $tjkt2->siswa()->create([
            'nis' => '456',
            'nama_siswa' => 'Jane Doe',
        ]);

        $tjkt3->siswa()->create([
            'nis' => '789',
            'nama_siswa' => 'Bob Smith',
        ]);

        $tkr1->siswa()->create([
            'nis' => '101',
            'nama_siswa' => 'Alice Johnson',
        ]);

        $tkr2->siswa()->create([
            'nis' => '202',
            'nama_siswa' => 'Michael Brown',
        ]);

        $tkr3->siswa()->create([
            'nis' => '303',
            'nama_siswa' => 'Emily Davis',
        ]);

        $dkv1->siswa()->create([
            'nis' => '404',
            'nama_siswa' => 'James Wilson',
        ]);

        $dkv2->siswa()->create([
            'nis' => '505',
            'nama_siswa' => 'Olivia Martinez',
        ]);

        $dkv3->siswa()->create([
            'nis' => '606',
            'nama_siswa' => 'Ethan Anderson',
        ]);

        $mplb1->siswa()->create([
            'nis' => '707',
            'nama_siswa' => 'Sophia Thompson',
        ]);

        $mplb2->siswa()->create([
            'nis' => '808',
            'nama_siswa' => 'Liam Martinez',
        ]);
    }
}
