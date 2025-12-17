<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display a listing of roles
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $withUsers = $request->input('with_users', false);

            $query = Role::query();

            // Search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%")
                      ->orWhere('display_name', 'ILIKE', "%{$search}%")
                      ->orWhere('description', 'ILIKE', "%{$search}%");
                });
            }

            // Include users count and users if requested
            if ($withUsers) {
                $query->withCount('users')->with('users:id,name,email,nip');
            } else {
                $query->withCount('users');
            }

            $roles = $query->orderBy('id')->get();

            return response()->json([
                'success' => true,
                'message' => 'Roles retrieved successfully',
                'data' => $roles
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created role
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name|regex:/^[a-z_]+$/',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ], [
            'name.required' => 'Role name is required',
            'name.unique' => 'Role name already exists',
            'name.regex' => 'Role name must be lowercase letters and underscores only',
            'display_name.required' => 'Display name is required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $role = Role::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully',
                'data' => $role
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified role
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $role = Role::withCount('users')
                ->with(['users' => function($query) {
                    $query->select('users.id', 'users.name', 'users.email', 'users.nip', 'users.is_active')
                          ->where('is_active', true);
                }])
                ->find($id);

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Role retrieved successfully',
                'data' => $role
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified role
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        // Prevent editing core roles
        $protectedRoles = ['super_admin', 'admin', 'kepala_jic'];
        if (in_array($role->name, $protectedRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit core system roles'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:roles,name,' . $id . '|regex:/^[a-z_]+$/',
            'display_name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:500',
        ], [
            'name.unique' => 'Role name already exists',
            'name.regex' => 'Role name must be lowercase letters and underscores only',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = [];
            
            if ($request->has('name')) {
                $updateData['name'] = $request->name;
            }
            
            if ($request->has('display_name')) {
                $updateData['display_name'] = $request->display_name;
            }
            
            if ($request->has('description')) {
                $updateData['description'] = $request->description;
            }

            $role->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully',
                'data' => $role
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified role
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $role = Role::withCount('users')->find($id);

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found'
                ], 404);
            }

            // Prevent deleting core roles
            $protectedRoles = ['super_admin', 'admin', 'kepala_jic', 'kadiv_umum', 'kadiv', 'staff'];
            if (in_array($role->name, $protectedRoles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete core system roles'
                ], 403);
            }

            // Check if role has users
            if ($role->users_count > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete role that has {$role->users_count} user(s) assigned"
                ], 403);
            }

            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get role statistics
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics($id)
    {
        try {
            $role = Role::withCount('users')->find($id);

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found'
                ], 404);
            }

            // Get users with this role
            $users = $role->users()
                ->select('id', 'name', 'email', 'nip', 'is_active', 'created_at')
                ->get();

            $stats = [
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'description' => $role->description,
                ],
                'total_users' => $role->users_count,
                'active_users' => $users->where('is_active', true)->count(),
                'inactive_users' => $users->where('is_active', false)->count(),
                'users' => $users,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Role statistics retrieved successfully',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve role statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get simple list of roles (for dropdown)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        try {
            $roles = Role::select('id', 'name', 'display_name')
                ->orderBy('id')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Roles list retrieved successfully',
                'data' => $roles
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve roles list',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign role to user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignToUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ], [
            'user_id.required' => 'User ID is required',
            'user_id.exists' => 'User not found',
            'role_id.required' => 'Role ID is required',
            'role_id.exists' => 'Role not found',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = \App\Models\User::find($request->user_id);
            $role = Role::find($request->role_id);

            // Check if user already has this role
            if ($user->roles()->where('role_id', $role->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already has this role'
                ], 409);
            }

            // Assign role to user
            $user->roles()->attach($role->id);

            return response()->json([
                'success' => true,
                'message' => "Role '{$role->display_name}' assigned to user '{$user->name}' successfully",
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'role' => [
                        'id' => $role->id,
                        'name' => $role->name,
                        'display_name' => $role->display_name,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign role to user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove role from user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFromUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ], [
            'user_id.required' => 'User ID is required',
            'user_id.exists' => 'User not found',
            'role_id.required' => 'Role ID is required',
            'role_id.exists' => 'Role not found',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = \App\Models\User::find($request->user_id);
            $role = Role::find($request->role_id);

            // Check if user has this role
            if (!$user->roles()->where('role_id', $role->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have this role'
                ], 404);
            }

            // Prevent removing last role
            if ($user->roles()->count() <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot remove the last role from user'
                ], 403);
            }

            // Prevent removing super_admin role from super_admin user
            if ($role->name === 'super_admin' && $user->hasRole('super_admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot remove super_admin role'
                ], 403);
            }

            // Remove role from user
            $user->roles()->detach($role->id);

            return response()->json([
                'success' => true,
                'message' => "Role '{$role->display_name}' removed from user '{$user->name}' successfully"
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove role from user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync user roles (replace all roles)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncUserRoles(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'exists:roles,id',
        ], [
            'user_id.required' => 'User ID is required',
            'user_id.exists' => 'User not found',
            'role_ids.required' => 'At least one role must be selected',
            'role_ids.*.exists' => 'One or more roles not found',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = \App\Models\User::find($request->user_id);

            // Sync roles (this will remove old roles and add new ones)
            $user->roles()->sync($request->role_ids);

            // Reload user with roles
            $user->load('roles');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User roles synchronized successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'roles' => $user->roles
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync user roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}