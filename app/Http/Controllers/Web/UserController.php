<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%")
                  ->orWhere('nip', 'ILIKE', "%{$search}%")
                  ->orWhere('phone', 'ILIKE', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role_id')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('roles.id', $request->role_id);
            });
        }

        // Active status filter
        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $users = $query->latest()->paginate(15);
        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'nip' => 'nullable|string|max:50|unique:users,nip',
            'phone' => 'nullable|string|max:20',
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'exists:roles,id',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Nama wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.unique' => 'Email sudah digunakan',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'nip.unique' => 'NIP sudah digunakan',
            'role_ids.required' => 'Pilih minimal satu role',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'nip' => $request->nip,
                'phone' => $request->phone,
                'is_active' => $request->has('is_active') ? $request->is_active : true,
            ]);

            $user->roles()->attach($request->role_ids);

            DB::commit();

            return redirect()->route('users.show', $user->id)
                ->with('success', 'User berhasil dibuat');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membuat user: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified user
     */
    public function show($id)
    {
        $user = User::with(['roles', 'anggaranKegiatan', 'pencairanDana'])->findOrFail($id);
        
        // Get statistics
        $stats = [
            'anggaran_kegiatan' => [
                'total' => $user->anggaranKegiatan()->count(),
                'by_status' => $user->anggaranKegiatan()
                    ->selectRaw('status, count(*) as count')
                    ->groupBy('status')
                    ->get()
                    ->mapWithKeys(fn($item) => [$item->status => $item->count]),
            ],
            'pencairan_dana' => [
                'total' => $user->pencairanDana()->count(),
                'by_status' => $user->pencairanDana()
                    ->selectRaw('status, count(*) as count')
                    ->groupBy('status')
                    ->get()
                    ->mapWithKeys(fn($item) => [$item->status => $item->count]),
                'total_amount' => $user->pencairanDana()
                    ->where('status', 'dicairkan')
                    ->sum('jumlah_pencairan'),
            ],
        ];

        return view('users.show', compact('user', 'stats'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        $user = User::with('roles')->findOrFail($id);
        $roles = Role::all();
        
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6|confirmed',
            'nip' => 'nullable|string|max:50|unique:users,nip,' . $id,
            'phone' => 'nullable|string|max:20',
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'exists:roles,id',
            'is_active' => 'boolean',
        ], [
            'email.unique' => 'Email sudah digunakan',
            'nip.unique' => 'NIP sudah digunakan',
            'password.min' => 'Password minimal 6 karakter',
        ]);

        DB::beginTransaction();
        try {
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'nip' => $request->nip,
                'phone' => $request->phone,
                'is_active' => $request->has('is_active') ? $request->is_active : $user->is_active,
            ];
            
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);
            $user->roles()->sync($request->role_ids);

            DB::commit();

            return redirect()->route('users.show', $user->id)
                ->with('success', 'User berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memperbarui user: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri');
        }

        // Prevent deleting super admin
        if ($user->hasRole('super_admin')) {
            return back()->with('error', 'User super admin tidak dapat dihapus');
        }

        try {
            $user->delete();
            return redirect()->route('users.index')
                ->with('success', 'User berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        // Prevent toggling own account
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat mengubah status akun sendiri');
        }

        // Prevent deactivating super admin
        if ($user->hasRole('super_admin')) {
            return back()->with('error', 'Status user super admin tidak dapat diubah');
        }

        try {
            $user->update(['is_active' => !$user->is_active]);
            $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
            
            return back()->with('success', "User berhasil {$status}");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah status user: ' . $e->getMessage());
        }
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'new_password.required' => 'Password baru wajib diisi',
            'new_password.min' => 'Password minimal 6 karakter',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        $user = User::findOrFail($id);

        try {
            $user->update(['password' => Hash::make($request->new_password)]);
            return back()->with('success', 'Password berhasil direset');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mereset password: ' . $e->getMessage());
        }
    }

    /**
     * Show profile page
     */
    public function profile()
    {
        $user = auth()->user()->load('roles');
        return view('users.profile', compact('user'));
    }

    /**
     * Update own profile
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'nip' => 'nullable|string|max:50|unique:users,nip,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ], [
            'email.unique' => 'Email sudah digunakan',
            'nip.unique' => 'NIP sudah digunakan',
        ]);

        try {
            $user->update($request->only(['name', 'email', 'nip', 'phone']));
            return back()->with('success', 'Profil berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal memperbarui profil: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Change own password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed|different:current_password',
        ], [
            'current_password.required' => 'Password saat ini wajib diisi',
            'new_password.required' => 'Password baru wajib diisi',
            'new_password.min' => 'Password minimal 6 karakter',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok',
            'new_password.different' => 'Password baru harus berbeda dari password saat ini',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah']);
        }

        try {
            $user->update(['password' => Hash::make($request->new_password)]);
            return back()->with('success', 'Password berhasil diubah');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah password: ' . $e->getMessage());
        }
    }
}