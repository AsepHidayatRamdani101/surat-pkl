<?php

namespace App\Http\Middleware;

use App\Support\WaliKelasRole;
use App\Support\WakaRole;
use Closure;
use Illuminate\Http\Request;

/**
 * Pembimbing yang diaktifkan sebagai waka, atau ditunjuk sebagai wali kelas, dapat
 * berganti peran. Peran aktif hanya berlaku di memori untuk request ini; kolom
 * users.role tidak diubah.
 */
class ApplyActiveRole
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->role === 'pembimbing') {
            $options = WakaRole::optionsFor((string) $user->username);
            $aktif = 'pembimbing';
            $dipilih = $request->session()->get('peran_aktif');

            if ($dipilih && array_key_exists($dipilih, $options)) {
                $user->role = $dipilih;
                $user->syncOriginalAttribute('role');
                $aktif = $dipilih;

                if ($dipilih === WaliKelasRole::ROLE) {
                    $request->attributes->set('wali_kelas_ids', WaliKelasRole::kelasIdsForNip((string) $user->username));
                }
            } else {
                $request->session()->forget('peran_aktif');
            }

            $request->attributes->set('peran_options', count($options) > 1 ? $options : []);
            $request->attributes->set('peran_aktif', $aktif);
        }

        return $next($request);
    }
}
