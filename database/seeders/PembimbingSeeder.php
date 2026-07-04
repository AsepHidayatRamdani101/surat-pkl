<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use App\Models\Pembimbing;
use Illuminate\Database\Seeder;

class PembimbingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jurusanMap = Jurusan::pluck('id', 'kode_jurusan');

        $pembimbingList = [
            [
                'nama_pembimbing' => 'Asep Hidayat, S.Pd.',
                'nip_pembimbing' => '198503122010011001',
                'jabatan_pembimbing' => 'Guru',
                'jenis_kelamin' => 'Laki-laki',
                'no_hp_pembimbing' => '081210000001',
                'jumlah_jam' => 24,
                'jenis_guru' => 'adaptif_normatif',
                'jurusan_id' => null,
            ],
            [
                'nama_pembimbing' => 'Dewi Lestari, S.Pd.',
                'nip_pembimbing' => '198704152011012002',
                'jabatan_pembimbing' => 'Guru',
                'jenis_kelamin' => 'Perempuan',
                'no_hp_pembimbing' => '081210000002',
                'jumlah_jam' => 22,
                'jenis_guru' => 'adaptif_normatif',
                'jurusan_id' => null,
            ],
            [
                'nama_pembimbing' => 'Rudi Hartono, S.Kom.',
                'nip_pembimbing' => '198609182012011003',
                'jabatan_pembimbing' => 'Kepala Program',
                'jenis_kelamin' => 'Laki-laki',
                'no_hp_pembimbing' => '081210000003',
                'jumlah_jam' => 20,
                'jenis_guru' => 'guru_produktif',
                'jurusan_id' => $jurusanMap['TJKT'] ?? null,
            ],
            [
                'nama_pembimbing' => 'Nina Marlina, S.Pd.',
                'nip_pembimbing' => '198811202013012004',
                'jabatan_pembimbing' => 'Guru',
                'jenis_kelamin' => 'Perempuan',
                'no_hp_pembimbing' => '081210000004',
                'jumlah_jam' => 18,
                'jenis_guru' => 'guru_produktif',
                'jurusan_id' => $jurusanMap['DKV'] ?? null,
            ],
            [
                'nama_pembimbing' => 'Bagus Saputra, S.T.',
                'nip_pembimbing' => '198402032009011005',
                'jabatan_pembimbing' => 'Wakil Kepala Sekolah',
                'jenis_kelamin' => 'Laki-laki',
                'no_hp_pembimbing' => '081210000005',
                'jumlah_jam' => 16,
                'jenis_guru' => 'adaptif_normatif',
                'jurusan_id' => null,
            ],
            [
                'nama_pembimbing' => 'Siti Rahmawati, M.Pd.',
                'nip_pembimbing' => '198901072014012006',
                'jabatan_pembimbing' => 'Guru',
                'jenis_kelamin' => 'Perempuan',
                'no_hp_pembimbing' => '081210000006',
                'jumlah_jam' => 26,
                'jenis_guru' => 'guru_produktif',
                'jurusan_id' => $jurusanMap['MPLB'] ?? null,
            ],
            [
                'nama_pembimbing' => 'Andri Kurniawan, S.Pd.',
                'nip_pembimbing' => '198305112008011007',
                'jabatan_pembimbing' => 'Guru',
                'jenis_kelamin' => 'Laki-laki',
                'no_hp_pembimbing' => '081210000007',
                'jumlah_jam' => 28,
                'jenis_guru' => 'guru_produktif',
                'jurusan_id' => $jurusanMap['TKR'] ?? null,
            ],
            [
                'nama_pembimbing' => 'Maya Permata, S.Kom.',
                'nip_pembimbing' => '199003252015012008',
                'jabatan_pembimbing' => 'Guru',
                'jenis_kelamin' => 'Perempuan',
                'no_hp_pembimbing' => '081210000008',
                'jumlah_jam' => 20,
                'jenis_guru' => 'guru_produktif',
                'jurusan_id' => $jurusanMap['TJKT'] ?? null,
            ],
            [
                'nama_pembimbing' => 'Fajar Nugraha, S.Pd.',
                'nip_pembimbing' => '198712142011011009',
                'jabatan_pembimbing' => 'Kepala Program',
                'jenis_kelamin' => 'Laki-laki',
                'no_hp_pembimbing' => '081210000009',
                'jumlah_jam' => 24,
                'jenis_guru' => 'adaptif_normatif',
                'jurusan_id' => null,
            ],
            [
                'nama_pembimbing' => 'Lina Kusumawati, S.Pd.',
                'nip_pembimbing' => '199106302016012010',
                'jabatan_pembimbing' => 'Guru',
                'jenis_kelamin' => 'Perempuan',
                'no_hp_pembimbing' => '081210000010',
                'jumlah_jam' => 18,
                'jenis_guru' => 'guru_produktif',
                'jurusan_id' => $jurusanMap['DKV'] ?? null,
            ],
        ];

        foreach ($pembimbingList as $pembimbing) {
            Pembimbing::updateOrCreate(
                ['nip_pembimbing' => $pembimbing['nip_pembimbing']],
                $pembimbing
            );
        }
    }
}
