<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\WakaRole;

class PeranController extends Controller
{
    public function ganti(string $peran)
    {
        $user = auth()->user();
        $akun = User::query()->whereKey($user->id)->first(['id', 'role', 'username']);
        abort_unless($akun && $akun->role === 'pembimbing', 403);

        $options = WakaRole::optionsFor((string) $akun->username);
        abort_unless(array_key_exists($peran, $options), 403);

        if ($peran === 'pembimbing') {
            session()->forget('peran_aktif');
        } else {
            session()->put('peran_aktif', $peran);
        }

        return redirect()->route('dashboard')->with('success', 'Peran aktif: ' . $options[$peran]);
    }
}
