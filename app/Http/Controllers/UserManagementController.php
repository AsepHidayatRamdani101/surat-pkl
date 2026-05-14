<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $roles = Role::query()->orderBy('name')->pluck('name');
        $jurusan = Jurusan::query()->orderBy('nama_jurusan')->get(['id', 'nama_jurusan']);

        // Keep legacy app roles available even when roles table is empty.
        if ($roles->isEmpty()) {
            $roles = collect(['panitia', 'kepala_program']);
        }

        return view('user_management.index', compact('roles', 'jurusan'));
    }

    public function data(Request $request): JsonResponse
    {
        if ($request->ajax()) {
            $users = User::query()->with('jurusan')->orderBy('name');

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('aksi', function ($row) {
                    $deleteDisabled = auth()->id() === $row->id ? 'disabled' : '';

                    return '
                        <button class="btn btn-sm btn-warning btnEdit" data-id="' . $row->id . '">Edit</button>
                        <button class="btn btn-sm btn-danger btnHapus" data-id="' . $row->id . '" ' . $deleteDisabled . '>Hapus</button>
                    ';
                })
                ->addColumn('jurusan_nama', function ($row) {
                    return $row->jurusan->nama_jurusan ?? '-';
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }

        return response()->json([]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string', 'max:100'],
            'jurusan_id' => ['nullable', 'exists:jurusan,id'],
        ]);

        $role = Role::query()->firstOrCreate([
            'name' => $validated['role'],
            'guard_name' => 'web',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'jurusan_id' => $validated['jurusan_id'] ?? null,
        ]);

        $user->syncRoles([$role->name]);

        return response()->json(['message' => 'User berhasil ditambahkan.']);
    }

    public function edit(User $user): JsonResponse
    {
        return response()->json($user);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'string', 'max:100'],
            'jurusan_id' => ['nullable', 'exists:jurusan,id'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'jurusan_id' => $validated['jurusan_id'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        $user->update($payload);

        $role = Role::query()->firstOrCreate([
            'name' => $validated['role'],
            'guard_name' => 'web',
        ]);
        $user->syncRoles([$role->name]);

        return response()->json(['message' => 'User berhasil diperbarui.']);
    }

    public function destroy(User $user): JsonResponse
    {
        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'User yang sedang login tidak bisa dihapus.'], 422);
        }

        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus.']);
    }
}
