<?php

namespace Database\Seeders;

use App\Models\Perusahaan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PerusahaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Perusahaan::create(['nama_perusahaan' => 'PT. ABC Teknologi', 'alamat' => 'Jl. Raya No. 123', 'kontak' => '021-1234567']);
        Perusahaan::create(['nama_perusahaan' => 'CV. Def Software', 'alamat' => 'Jl. Merdeka No. 45', 'kontak' => '021-7654321']);
        Perusahaan::create(['nama_perusahaan' => 'PT. GHI Digital', 'alamat' => 'Jl. Sudirman No. 678', 'kontak' => '021-9876543']);
    }
}
