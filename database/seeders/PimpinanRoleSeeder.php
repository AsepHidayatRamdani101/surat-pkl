<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\RoleRegistry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class PimpinanRoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (RoleRegistry::ROLES as $key => $label) {
            $role = Role::firstOrCreate(['name' => $key, 'guard_name' => 'web']);

            $user = User::updateOrCreate(
                ['email' => $key . '@example.com'],
                [
                    'name' => $label,
                    'username' => $key,
                    'password' => Hash::make('password'), // Ganti setelah login pertama
                    'role' => $key,
                ]
            );

            $user->syncRoles([$role->name]);
        }
    }
}
