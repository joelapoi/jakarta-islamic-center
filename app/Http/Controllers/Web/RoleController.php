<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display a listing of roles
     */
    public function index(Request $request)
    {
        $query = Role::withCount('users');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('display_name', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        $roles = $query->orderBy('id')->paginate(15);

        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        return view('roles.create');
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name|regex:/^[a-z_]+$/',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ], [
            'name.required' => 'Nama role wajib diisi',
            'name.unique' => 'Nama role sudah ada',
            'name.regex' => 'Nama role hanya boleh huruf kecil dan underscore',
            'display_name.required' => 'Nama tampilan wajib diisi',
        ]);

        try {
            $role = Role::create($request->all());
            return redirect()->route('roles.show', $role->id)
                ->with('success', 'Role berhasil dibuat');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal membuat role: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified role
     */
    public function show($id)
    {
        $role = Role::withCount('users')
            ->with(['users' => function($query) {
                $query->select('users.id', 'users.name', 'users.email', 'users.nip', 'users.is_active')
                      ->where('is_active', true);
            }])
            ->findOrFail($id);

        return view('roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit($id)
    {
        $role = Role::findOrFail($id);

        // Prevent editing core roles
        $protectedRoles = ['super_admin', 'admin', 'kepala_jic'];
        if (in_array($role->name, $protectedRoles)) {
            return back()->with('error', 'Role sistem tidak dapat diedit');
        }

        return view('roles.edit', compact('role'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        // Prevent editing core roles
        $protectedRoles = ['super_admin', 'admin', 'kepala_jic'];
        if (in_array($role->name, $protectedRoles)) {
            return back()->with('error', 'Role sistem tidak dapat diedit');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id . '|regex:/^[a-z_]+$/',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ], [
            'name.unique' => 'Nama role sudah ada',
            'name.regex' => 'Nama role hanya boleh huruf kecil dan underscore',
        ]);

        try {
            $role->update($request->all());
            return redirect()->route('roles.show', $role->id)
                ->with('success', 'Role berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal memperbarui role: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified role
     */
    public function destroy($id)
    {
        $role = Role::withCount('users')->findOrFail($id);

        // Prevent deleting core roles
        $protectedRoles = ['super_admin', 'admin', 'kepala_jic', 'kadiv_umum', 'kadiv', 'staff'];
        if (in_array($role->name, $protectedRoles)) {
            return back()->with('error', 'Role sistem tidak dapat dihapus');
        }

        // Check if role has users
        if ($role->users_count > 0) {
            return back()->with('error', "Tidak dapat menghapus role yang memiliki {$role->users_count} user");
        }

        try {
            $role->delete();
            return redirect()->route('roles.index')
                ->with('success', 'Role berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus role: ' . $e->getMessage());
        }
    }

    /**
     * Assign role to user
     */
    public function assignToUser(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ], [
            'user_id.required' => 'User wajib dipilih',
            'user_id.exists' => 'User tidak ditemukan',
        ]);

        $role = Role::findOrFail($id);
        $user = User::findOrFail($request->user_id);

        if ($user->roles()->where('role_id', $role->id)->exists()) {
            return back()->with('error', 'User sudah memiliki role ini');
        }

        try {
            $user->roles()->attach($role->id);
            return back()->with('success', "Role '{$role->display_name}' berhasil ditambahkan ke user '{$user->name}'");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambahkan role ke user: ' . $e->getMessage());
        }
    }

    /**
     * Remove role from user
     */
    public function removeFromUser($roleId, $userId)
    {
        $role = Role::findOrFail($roleId);
        $user = User::findOrFail($userId);

        if (!$user->roles()->where('role_id', $role->id)->exists()) {
            return back()->with('error', 'User tidak memiliki role ini');
        }

        // Prevent removing last role
        if ($user->roles()->count() <= 1) {
            return back()->with('error', 'Tidak dapat menghapus role terakhir dari user');
        }

        // Prevent removing super_admin role
        if ($role->name === 'super_admin' && $user->hasRole('super_admin')) {
            return back()->with('error', 'Tidak dapat menghapus role super_admin');
        }

        try {
            $user->roles()->detach($role->id);
            return back()->with('success', "Role '{$role->display_name}' berhasil dihapus dari user '{$user->name}'");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus role dari user: ' . $e->getMessage());
        }
    }
}