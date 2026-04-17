<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Only super admin can access companies
        if (!$user->hasRole('super admin')) {
            return response()->json([
                'message' => 'Unauthorized. Only super admin can access companies.',
            ], 403);
        }

        // Get pagination parameters
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $sortColumn = $request->input('sort_column', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Build base query
        $query = Company::query();

        // Apply column filters
        if ($request->has('filters')) {
            $filters = $request->input('filters');

            if (!empty($filters['company_id'])) {
                $query->where('company_id', 'like', '%' . $filters['company_id'] . '%');
            }

            if (!empty($filters['company_name'])) {
                $query->where('company_name', 'like', '%' . $filters['company_name'] . '%');
            }
        }

        // Apply sorting
        if ($sortColumn) {
            $query->orderBy($sortColumn, $sortDirection);
        }

        // Paginate results
        $companies = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $companies->items(),
            'total' => $companies->total(),
            'current_page' => $companies->currentPage(),
            'last_page' => $companies->lastPage(),
            'per_page' => $companies->perPage(),
        ]);
    }

    /**
     * Store a newly created company.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Only super admin can create companies
        if (!$user->hasRole('super admin')) {
            return response()->json([
                'message' => 'Unauthorized. Only super admin can create companies.',
            ], 403);
        }

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
        ]);

        // Generate company_id from company_name (uppercase, replace spaces with underscores)
        $companyId = strtoupper(str_replace(' ', '_', $validated['company_name']));

        // Ensure uniqueness
        $baseCompanyId = $companyId;
        $counter = 1;
        while (Company::where('company_id', $companyId)->exists()) {
            $companyId = $baseCompanyId . '_' . $counter;
            $counter++;
        }

        $company = Company::create([
            'company_id' => $companyId,
            'company_name' => $validated['company_name'],
        ]);

        // Automatically create admin role for the new company (without permissions)
        // Use try-catch to handle Spatie's global uniqueness check
        try {
            Role::create([
                'name' => 'admin',
                'guard_name' => 'web',
                'company_id' => $company->id,
            ]);
        } catch (\Spatie\Permission\Exceptions\RoleAlreadyExists $e) {
            // Spatie throws this if a role with same name/guard exists globally
            // But we allow same name for different companies, so check if it's actually a duplicate per company
            $existingRole = Role::where('name', 'admin')
                ->where('guard_name', 'web')
                ->where('company_id', $company->id)
                ->first();

            if ($existingRole) {
                // Admin role already exists for this company, skip creation
                return response()->json([
                    'success' => true,
                    'message' => 'Company created successfully',
                    'company' => $company,
                ], 201);
            }

            // If it's a different company, use DB facade to bypass Spatie's validation
            // The database constraint (name, guard_name, company_id) will allow it
            DB::table('roles')->insert([
                'name' => 'admin',
                'guard_name' => 'web',
                'company_id' => $company->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Company created successfully',
            'company' => $company,
        ], 201);
    }

    /**
     * Update the specified company.
     */
    public function update(Request $request, Company $company)
    {
        $user = Auth::user();

        // Only super admin can update companies
        if (!$user->hasRole('super admin')) {
            return response()->json([
                'message' => 'Unauthorized. Only super admin can update companies.',
            ], 403);
        }

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
        ]);

        // Generate company_id from company_name
        $companyId = strtoupper(str_replace(' ', '_', $validated['company_name']));

        // Ensure uniqueness (excluding current company)
        $baseCompanyId = $companyId;
        $counter = 1;
        while (Company::where('company_id', $companyId)->where('id', '!=', $company->id)->exists()) {
            $companyId = $baseCompanyId . '_' . $counter;
            $counter++;
        }

        $company->update([
            'company_id' => $companyId,
            'company_name' => $validated['company_name'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Company updated successfully',
            'company' => $company,
        ]);
    }

    /**
     * Remove the specified company.
     */
    public function destroy(Company $company)
    {
        $user = Auth::user();

        // Only super admin can delete companies
        if (!$user->hasRole('super admin')) {
            return response()->json([
                'message' => 'Unauthorized. Only super admin can delete companies.',
            ], 403);
        }

        $company->delete();
        return response()->json([
            'success' => true,
            'message' => 'Company deleted successfully',
        ]);
    }
}
