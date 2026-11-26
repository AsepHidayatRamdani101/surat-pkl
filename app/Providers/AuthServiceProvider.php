<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use App\Models\MenuKegiatanSetting;
use App\Support\RoleRegistry;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('kepala_program', function ($user) {
            return $user->role === 'kepala_program';
        });

        Gate::define('panitia', function ($user) {
            return $user->role === 'panitia';
        });

        Gate::define('siswa', function ($user) {
            return $user->role === 'siswa';
        });

        Gate::define('pembimbing', function ($user) {
            return $user->role === 'pembimbing';
        });

        foreach (RoleRegistry::keys() as $role) {
            Gate::define($role, fn ($user) => $user->role === $role);
        }

        Gate::define('wali_kelas', fn ($user) => $user->role === 'wali_kelas');

        // Semua role pimpinan/bidang (kepala sekolah & para waka).
        Gate::define('pimpinan', fn ($user) => RoleRegistry::has($user->role));

        $menuRoles = array_merge(['panitia', 'kepala_program', 'pembimbing', 'siswa'], RoleRegistry::keys());

        foreach ($menuRoles as $role) {
            foreach (['pembekalan', 'pkl', 'bimbingan', 'sidang'] as $kegiatan) {
                Gate::define("menu_{$kegiatan}_{$role}", function ($user) use ($kegiatan, $role) {
                    return $user->role === $role && MenuKegiatanSetting::isActive($kegiatan);
                });
            }
        }
    }
}
