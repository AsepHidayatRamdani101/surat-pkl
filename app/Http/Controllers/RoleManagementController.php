<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleManagementController extends Controller
{
    public function index(): View
    {
        return view('role_management.index');
    }

    public function data(Request $request): JsonResponse
    {
        if ($request->ajax()) {
            $roles = Role::query()->orderBy('name');

            return DataTables::of($roles)
                ->addIndexColumn()
                ->addColumn('aksi', function ($row) {
                    return '
                        <div class="btn-group btn-aksi">
                            <button type="button" class="btn btn-outline-primary btn-sm btnEdit" title="Edit"
                                data-id="' . $row->id . '">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm btnHapus" title="Hapus"
                                data-id="' . $row->id . '">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>';
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }

        return response()->json([]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
        ]);

        Role::query()->create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        return response()->json(['message' => 'Role berhasil ditambahkan.']);
    }

    public function edit(Role $role): JsonResponse
    {
        return response()->json($role);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name,' . $role->id],
        ]);

        $oldName = $role->name;
        $newName = $validated['name'];

        DB::transaction(function () use ($role, $oldName, $newName) {
            $role->update(['name' => $newName]);

            // Keep compatibility with legacy string role column used by Gates.
            DB::table('users')
                ->where('role', $oldName)
                ->update(['role' => $newName]);
        });

        return response()->json(['message' => 'Role berhasil diperbarui.']);
    }

    public function destroy(Role $role): JsonResponse
    {
        $role->delete();

        return response()->json(['message' => 'Role berhasil dihapus.']);
    }
}
