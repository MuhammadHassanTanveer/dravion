<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super admin');
        
        // Super admin always has access
        if (!$isSuperAdmin) {
            // Allow access if user has ANY role-related permission OR user-related permission
            // (users need to see roles when creating/editing users)
            $hasRolePermission = $user->can('view roles') || 
                                $user->can('add role') || 
                                $user->can('edit role') || 
                                $user->can('delete role') || 
                                $user->can('manage permissions');
            
            $hasUserPermission = $user->can('view users') || 
                                $user->can('add user') || 
                                $user->can('edit user') || 
                                $user->can('delete user');
            
            if (!$hasRolePermission && !$hasUserPermission) {
                return response()->json([
                    'message' => 'You do not have permission to access this resource.',
                ], 403);
            }
        }
        
        // For permission checking in response
        $hasRolePermission = $isSuperAdmin || 
                            $user->can('view roles') || 
                            $user->can('add role') || 
                            $user->can('edit role') || 
                            $user->can('delete role') || 
                            $user->can('manage permissions');
        
        // Get roles based on user type
        if ($isSuperAdmin) {
            // Super admin sees all roles with company information
            // If company_id is provided in request, filter by that company
            if ($request->has('company_id') && $request->company_id) {
                $roles = Role::with(['permissions', 'company'])
                    ->where('company_id', $request->company_id)
                    ->get();
            } else {
                $roles = Role::with(['permissions', 'company'])->get();
            }
        } else {
            // Other users see only roles from their company
            $roles = Role::with('permissions')
                ->where('company_id', $user->company_id)
                ->get();
        }
        
        // Hide "super admin" role from non-super admin users
        if (!$isSuperAdmin) {
            $roles = $roles->reject(function ($role) {
                return $role->name === 'super admin';
            })->values();
        }
        
        // Only return permissions if user has role management permissions OR is super admin
        $permissions = [];
        if ($hasRolePermission || $isSuperAdmin) {
            $permissions = Permission::all();
        }
        
        return response()->json([
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super admin');
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,id',
            'company_id' => $isSuperAdmin ? 'required|exists:companies,id' : 'nullable',
        ]);

        // Determine company_id after validation
        $companyId = $isSuperAdmin ? $validated['company_id'] : $user->company_id;
        
        // Validate uniqueness per company
        $existingRole = Role::where('name', $validated['name'])
            ->where('company_id', $companyId)
            ->first();
        
        if ($existingRole) {
            return response()->json([
                'success' => false,
                'message' => 'A role with this name already exists for this company.',
                'errors' => [
                    'name' => ['A role with this name already exists for this company.']
                ]
            ], 422);
        }

        try {
            $role = Role::create([
                'name' => $validated['name'],
                'guard_name' => 'web',
                'company_id' => $companyId,
            ]);
        } catch (\Spatie\Permission\Exceptions\RoleAlreadyExists $e) {
            // Spatie throws this if a role with same name/guard exists globally
            // But we allow same name for different companies, so check if it's actually a duplicate per company
            $existingRole = Role::where('name', $validated['name'])
                ->where('guard_name', 'web')
                ->where('company_id', $companyId)
                ->first();
            
            if ($existingRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'A role with this name already exists for this company.',
                    'errors' => [
                        'name' => ['A role with this name already exists for this company.']
                    ]
                ], 422);
            }
            
            // If it's a different company, use DB facade to bypass Spatie's validation
            // The database constraint (name, guard_name, company_id) will allow it
            $roleId = DB::table('roles')->insertGetId([
                'name' => $validated['name'],
                'guard_name' => 'web',
                'company_id' => $companyId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $role = Role::find($roleId);
        }

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'role' => $role->load(['permissions', 'company']),
        ], 201);
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        $user = $request->user();
        $isSuperAdmin = $user->hasRole('super admin');
        
        // Super admin bypasses all permission checks
        if (!$isSuperAdmin) {
            // Check if user can manage permissions
            if ($request->has('permissions') && !$user->can('manage permissions')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to manage permissions',
                ], 403);
            }
            
            // Check if user can edit role
            if ($request->has('name') && !$user->can('edit role')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to edit roles',
                ], 403);
            }
        }

        // Get the company_id for the role being updated
        $companyId = $role->company_id;
        
        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('roles', 'name')->ignore($role->id)->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if (isset($validated['name'])) {
            $role->update(['name' => $validated['name']]);
        }

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'role' => $role->load('permissions'),
        ]);
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        // Check if role is assigned to any users
        $usersWithRole = \App\Models\User::role($role->name)->count();
        
        if ($usersWithRole > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete role. This role is assigned to {$usersWithRole} user(s). Please remove the role from all users before deleting it.",
            ], 422);
        }
        
        $role->delete();
        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully',
        ]);
    }
}

