<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPermissionController extends Controller
{
    /**
     * Get current user's permissions.
     * Filters permissions to only include those from roles that belong to the user's company.
     */
    public function getPermissions()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['permissions' => []], 401);
        }

        // Get user's roles filtered by company_id
        // This ensures we only get permissions from roles that belong to the user's company
        $userRoles = $user->roles()
            ->where('roles.company_id', $user->company_id)
            ->get();
        
        // Collect all permissions from the user's company-specific roles
        $permissions = collect();
        foreach ($userRoles as $role) {
            $rolePermissions = $role->permissions;
            $permissions = $permissions->merge($rolePermissions);
        }
        
        // Remove duplicates and get unique permission names
        $permissionNames = $permissions->pluck('name')->unique()->values()->toArray();
        
        // Get role names from company-specific roles
        $roleNames = $userRoles->pluck('name')->toArray();
        $primaryRole = $roleNames[0] ?? 'No Role';

        return response()->json([
            'permissions' => $permissionNames,
            'user' => [
                'id' => $user->id,
                'user_id' => $user->user_id,
                'name' => $user->name,
                'role' => $primaryRole,
                'company_id' => $user->company_id,
            ],
        ]);
    }

    /**
     * Get current user's assigned pages.
     */
    public function getPages()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['pages' => []], 401);
        }

        // Get pages assigned to user, filtered by company
        $pages = $user->pages()
            ->where('pages.company_id', $user->company_id)
            ->get();

        return response()->json([
            'pages' => $pages,
        ]);
    }

    /**
     * Get current user's payment methods.
     * For non-admin users: Show all payment methods from their company.
     * For admin users: Show all payment methods from their company.
     */
    public function getPaymentMethods()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['payment_methods' => []], 401);
        }

        // Get all payment methods from user's company (not just assigned ones)
        // This allows all users to see and use payment methods from their company
        $paymentMethods = \App\Models\PaymentMethod::where('company_id', $user->company_id)->get();

        return response()->json([
            'payment_methods' => $paymentMethods,
        ]);
    }

    /**
     * Get all companies (for super admin).
     */
    public function getCompanies()
    {
        $user = Auth::user();
        
        if (!$user || !$user->hasRole('super admin')) {
            return response()->json(['companies' => []], 403);
        }

        $companies = \App\Models\Company::all();

        return response()->json([
            'companies' => $companies,
        ]);
    }

    /**
     * Update current user's profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'current_password' => 'required_with:password|string',
            'password' => 'nullable|string|min:8|confirmed',
            'password_confirmation' => 'nullable|string',
        ]);

        // Update name
        $user->name = $validated['name'];
        
        // Update password if provided
        if (isset($validated['password']) && $validated['password']) {
            // Verify current password
            if (!\Illuminate\Support\Facades\Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'message' => 'The provided current password is incorrect.',
                    'errors' => ['current_password' => ['The provided current password is incorrect.']]
                ], 422);
            }
            
            // Update password (will be automatically hashed by the User model's 'hashed' cast)
            $user->password = $validated['password'];
        }
        
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'user_id' => $user->user_id,
                'name' => $user->name,
                'role' => $user->getRoleNames()->first() ?? 'No Role',
                'company_id' => $user->company_id,
            ],
        ]);
    }
}

