<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index()
    {
        $user = auth()->user();
        
        // Check if user has ANY user-related permission (view, add, edit, or delete)
        if (!$user->can('view users') && 
            !$user->can('add user') && 
            !$user->can('edit user') && 
            !$user->can('delete user')) {
            return response()->json([
                'message' => 'You do not have permission to access this resource.',
            ], 403);
        }
        
        // Super admin can see all users (except other super admins), others see only users from their company
        if ($user->hasRole('super admin')) {
            $users = User::with(['roles', 'pages', 'company'])
                ->whereDoesntHave('roles', function ($query) {
                    $query->where('name', 'super admin');
                })
                ->get();
        } else {
            $isAdmin = $user->hasRole('admin');
            
            // Company admins see only users from their company, excluding super admin users
            // Other users (non-admin, non-super admin) see only users from their company, excluding both super admin and admin users
            $users = User::with(['roles', 'pages', 'company'])
                ->where('company_id', $user->company_id)
                ->whereDoesntHave('roles', function ($query) {
                    $query->where('name', 'super admin');
                });
            
            // If user is not admin, also exclude admin users
            if (!$isAdmin) {
                $users = $users->whereDoesntHave('roles', function ($query) {
                    $query->where('name', 'admin');
                });
            }
            
            $users = $users->get();
        }
        
        return response()->json([
            'data' => $users,
            'total' => $users->count(),
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        \Log::info('Creating user - Request data:', $request->all());
        
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser->hasRole('super admin');
        
        $validated = $request->validate([
            'user_id' => 'required|string|max:255|unique:users',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'role' => 'nullable|string',
            'company_id' => $isSuperAdmin ? 'required|exists:companies,id' : 'nullable',
            'pages' => 'sometimes|array',
            'pages.*' => 'exists:pages,id',
        ]);
        
        \Log::info('Validated data:', $validated);

        // If not super admin, use current user's company_id
        if (!$isSuperAdmin) {
            $validated['company_id'] = $currentUser->company_id;
        }

        $user = User::create([
            'user_id' => $validated['user_id'],
            'name' => $validated['name'],
            'password' => $validated['password'], // Let Laravel's 'hashed' cast handle hashing
            'role' => $validated['role'] ?? null,
            'company_id' => $validated['company_id'],
        ]);

        if (isset($validated['role']) && $validated['role']) {
            // Get role from the user's company (or selected company for super admin)
            $companyId = isset($validated['company_id']) ? $validated['company_id'] : $user->company_id;
            $role = Role::where('name', $validated['role'])
                ->where('company_id', $companyId)
                ->first();
            if ($role) {
                // Prevent non-super admin users from assigning super admin role
                if ($role->name === 'super admin' && !$currentUser->hasRole('super admin')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have permission to assign super admin role.',
                    ], 403);
                }
                $user->assignRole($role);
            }
        }

        // Assign pages - if admin role, assign all pages from user's company; otherwise use provided pages
        // Check if user has admin role (after role assignment)
        $isAdmin = $user->hasRole('admin');
        
        if ($isAdmin) {
            // Admin gets all pages from their company only
            $companyId = $validated['company_id'];
            $allPages = Page::where('company_id', $companyId)->get();
            $allPageIds = $allPages->pluck('id')->toArray();
            \Log::info('Assigning all pages to admin user from company', ['user_id' => $user->id, 'company_id' => $companyId, 'page_ids' => $allPageIds, 'count' => count($allPageIds)]);
            $user->pages()->sync($allPageIds);
        } elseif (isset($validated['pages']) && is_array($validated['pages']) && count($validated['pages']) > 0) {
            // Non-admin gets only selected pages
            // Ensure we have an array of integers
            $pageIds = array_values(array_filter(array_map('intval', $validated['pages'])));
            \Log::info('Syncing pages for user', ['user_id' => $user->id, 'page_ids' => $pageIds, 'count' => count($pageIds), 'original' => $validated['pages']]);
            $result = $user->pages()->sync($pageIds);
            \Log::info('Sync result:', $result);
        } elseif (isset($validated['pages']) && (empty($validated['pages']) || !is_array($validated['pages']))) {
            // If pages array is empty or invalid, clear pages
            $user->pages()->sync([]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'user' => $user->load(['roles', 'pages']),
        ], 201);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser->hasRole('super admin');
        
        // Prevent non-super admin users from editing super admin users
        if (!$isSuperAdmin && $user->hasRole('super admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit super admin users.',
            ], 403);
        }
        
        $validated = $request->validate([
            'user_id' => 'sometimes|required|string|max:255|unique:users,user_id,' . $user->id,
            'name' => 'sometimes|required|string|max:255',
            'password' => 'sometimes|nullable|string|min:8',
            'role' => 'sometimes|nullable|string',
            'company_id' => $isSuperAdmin ? 'sometimes|required|exists:companies,id' : 'nullable',
            'pages' => 'sometimes|array',
            'pages.*' => 'exists:pages,id',
        ]);
        
        // If not super admin, use current user's company_id (cannot change company)
        if (!$isSuperAdmin) {
            $validated['company_id'] = $currentUser->company_id;
        }

        if (isset($validated['password']) && $validated['password']) {
            // Let Laravel's 'hashed' cast handle hashing - don't hash it manually
            // The password will be automatically hashed when setting
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        if (isset($validated['role'])) {
            $user->roles()->detach();
            if ($validated['role']) {
                // Get role from the user's company (or selected company for super admin)
                $companyId = isset($validated['company_id']) ? $validated['company_id'] : $user->company_id;
                $role = Role::where('name', $validated['role'])
                    ->where('company_id', $companyId)
                    ->first();
                if ($role) {
                    // Prevent non-super admin users from assigning super admin role
                    if ($role->name === 'super admin' && !$currentUser->hasRole('super admin')) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You do not have permission to assign super admin role.',
                        ], 403);
                    }
                    $user->assignRole($role);
                }
            }
        }

        // Update pages - if admin role, assign all pages from user's company; otherwise use provided pages
        // Check if user has admin role (after role assignment)
        $user->refresh(); // Refresh to get updated role
        $isAdmin = $user->hasRole('admin');
        
        if ($isAdmin) {
            // Admin gets all pages from their company only
            $companyId = isset($validated['company_id']) ? $validated['company_id'] : $user->company_id;
            $allPages = Page::where('company_id', $companyId)->get();
            $allPageIds = $allPages->pluck('id')->toArray();
            \Log::info('Updating admin user - assigning all pages from company', ['user_id' => $user->id, 'company_id' => $companyId, 'page_ids' => $allPageIds, 'count' => count($allPageIds)]);
            $user->pages()->sync($allPageIds);
        } elseif (isset($validated['pages']) && is_array($validated['pages']) && count($validated['pages']) > 0) {
            // Non-admin gets only selected pages
            // Ensure we have an array of integers
            $pageIds = array_values(array_filter(array_map('intval', $validated['pages'])));
            \Log::info('Updating pages for user', ['user_id' => $user->id, 'page_ids' => $pageIds, 'count' => count($pageIds), 'original' => $validated['pages']]);
            $result = $user->pages()->sync($pageIds);
            \Log::info('Sync result:', $result);
        } elseif (isset($validated['pages']) && (empty($validated['pages']) || !is_array($validated['pages']))) {
            // If pages array is empty or invalid, clear pages (only if pages key was explicitly provided)
            $user->pages()->sync([]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'user' => $user->load(['roles', 'pages']),
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser->hasRole('super admin');
        
        // Prevent non-super admin users from deleting super admin users
        if (!$isSuperAdmin && $user->hasRole('super admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete super admin users.',
            ], 403);
        }
        
        $user->delete();
        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ]);
    }
}

