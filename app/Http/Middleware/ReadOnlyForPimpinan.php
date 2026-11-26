<?php

namespace App\Http\Middleware;

use App\Support\RoleRegistry;
use App\Support\WaliKelasRole;
use Closure;
use Illuminate\Http\Request;

class ReadOnlyForPimpinan
{
    private const ALWAYS_ALLOWED = ['logout', 'profile*', 'password*'];

    public function handle(Request $request, Closure $next)
    {
        $role = $request->user()?->role;

        if (!RoleRegistry::has($role) && !WaliKelasRole::is($role)) {
            return $next($request);
        }

        // Sarana & Prasarana boleh mengelola data ruangan.
        $writable = $role === 'waka_sarpras' && $request->is('sidang/ruangan*');

        if (!$writable) {
            // Dipakai CSS (public/css/role-readonly.css) untuk menyembunyikan tombol aksi.
            config(['adminlte.classes_body' => trim(config('adminlte.classes_body', '') . ' role-readonly')]);

            if (!$request->isMethodSafe() && !$request->is(...self::ALWAYS_ALLOWED)) {
                $message = 'Role ' . WaliKelasRole::label($role) . ' hanya dapat melihat data.';

                abort_if($request->expectsJson(), 403, $message);
                abort(403, $message);
            }
        }

        return $next($request);
    }
}
