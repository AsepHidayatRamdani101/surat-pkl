<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Kepala Program',
            'email' => 'kepalaprogram@example.com',
            'password' => Hash::make('password'), // Ganti dengan password aman
            'role' => 'kepala_program',
        ]);

        // Panitia
        User::create([
            'name' => 'Panitia PKL',
            'email' => 'panitia@example.com',
            'password' => Hash::make('password'), // Ganti juga
            'role' => 'panitia',
        ]);

        User::create([
            'name' => 'Akon Maulana, S.Pd.',
            'email' => 'akon@example.com',
            'password' => Hash::make('password'), // Ganti dengan password aman
            'role' => 'kepala_program',
            'jurusan_id' => '2',
        ]);

        User::create([
            'name' => 'Pendi Abdul Wahab,S.T.',
            'email' => 'pendi@example.com',
            'password' => Hash::make('password'), // Ganti dengan password aman
            'role' => 'kepala_program',
            'jurusan_id' => '1',
        ]);
    }
}
