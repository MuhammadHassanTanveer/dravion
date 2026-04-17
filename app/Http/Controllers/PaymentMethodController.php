<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of payment methods.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super admin');

        // Get pagination parameters
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $sortColumn = $request->input('sort_column', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Build base query
        $query = PaymentMethod::with('company');

        // Super admin can see all payment methods with company info, others see only payment methods from their company
        if ($isSuperAdmin) {
            // If company_id is provided in request, filter by that company
            if ($request->has('company_id') && $request->company_id) {
                $query->where('company_id', $request->company_id);
            }
        } else {
            $query->where('company_id', $user->company_id);
        }

        // Apply column filters
        if ($request->has('filters')) {
            $filters = $request->input('filters');

            if (!empty($filters['payment_method_id'])) {
                $query->where('payment_method_id', 'like', '%' . $filters['payment_method_id'] . '%');
            }

            if (!empty($filters['payment_method_name'])) {
                $query->where('payment_method_name', 'like', '%' . $filters['payment_method_name'] . '%');
            }

            if (!empty($filters['company.company_name']) && $isSuperAdmin) {
                $query->whereHas('company', function ($q) use ($filters) {
                    $q->where('company_name', 'like', '%' . $filters['company.company_name'] . '%');
                });
            }
        }

        // Apply sorting
        if ($sortColumn) {
            if (str_contains($sortColumn, '.')) {
                $query->orderBy('created_at', $sortDirection);
            } else {
                $query->orderBy($sortColumn, $sortDirection);
            }
        }

        // Paginate results
        $paymentMethods = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $paymentMethods->items(),
            'total' => $paymentMethods->total(),
            'current_page' => $paymentMethods->currentPage(),
            'last_page' => $paymentMethods->lastPage(),
            'per_page' => $paymentMethods->perPage(),
        ]);
    }

    /**
     * Store a newly created payment method.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super admin');

        $validated = $request->validate([
            'payment_method_name' => 'required|string|max:255',
            'company_id' => $isSuperAdmin ? 'required|exists:companies,id' : 'nullable',
        ]);

        // Generate payment_method_id from payment_method_name (uppercase, replace spaces with underscores)
        $paymentMethodId = strtoupper(str_replace(' ', '_', $validated['payment_method_name']));

        // Ensure uniqueness within company
        $basePaymentMethodId = $paymentMethodId;
        $counter = 1;
        $companyId = $isSuperAdmin ? $validated['company_id'] : $user->company_id;
        while (PaymentMethod::where('payment_method_id', $paymentMethodId)->where('company_id', $companyId)->exists()) {
            $paymentMethodId = $basePaymentMethodId . '_' . $counter;
            $counter++;
        }

        $paymentMethod = PaymentMethod::create([
            'payment_method_id' => $paymentMethodId,
            'payment_method_name' => $validated['payment_method_name'],
            'company_id' => $companyId,
        ]);

        // Auto-assign new payment method to all admin users of the same company
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminUsers = User::role('admin')->where('company_id', $companyId)->get();
            \Log::info('Auto-assigning new payment method to admin users', ['payment_method_id' => $paymentMethod->id, 'payment_method_name' => $paymentMethod->payment_method_name, 'company_id' => $companyId, 'admin_users_count' => $adminUsers->count()]);
            foreach ($adminUsers as $adminUser) {
                // Use syncWithoutDetaching to add the new payment method without removing existing ones
                $adminUser->paymentMethods()->syncWithoutDetaching([$paymentMethod->id]);
                \Log::info('Assigned payment method to admin user', ['user_id' => $adminUser->id, 'user_name' => $adminUser->name, 'total_payment_methods' => $adminUser->paymentMethods()->count()]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment method created successfully',
            'payment_method' => $paymentMethod,
        ], 201);
    }

    /**
     * Update the specified payment method.
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super admin');

        $validated = $request->validate([
            'payment_method_name' => 'required|string|max:255',
            'company_id' => $isSuperAdmin ? 'sometimes|required|exists:companies,id' : 'nullable',
        ]);

        // Generate payment_method_id from payment_method_name
        $paymentMethodId = strtoupper(str_replace(' ', '_', $validated['payment_method_name']));

        // Ensure uniqueness within company (excluding current payment method)
        $basePaymentMethodId = $paymentMethodId;
        $counter = 1;
        $companyId = $isSuperAdmin && isset($validated['company_id']) ? $validated['company_id'] : $paymentMethod->company_id;
        while (PaymentMethod::where('payment_method_id', $paymentMethodId)->where('company_id', $companyId)->where('id', '!=', $paymentMethod->id)->exists()) {
            $paymentMethodId = $basePaymentMethodId . '_' . $counter;
            $counter++;
        }

        $updateData = [
            'payment_method_id' => $paymentMethodId,
            'payment_method_name' => $validated['payment_method_name'],
        ];

        if ($isSuperAdmin && isset($validated['company_id'])) {
            $updateData['company_id'] = $validated['company_id'];
        }

        $paymentMethod->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Payment method updated successfully',
            'payment_method' => $paymentMethod,
        ]);
    }

    /**
     * Remove the specified payment method.
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();
        return response()->json([
            'success' => true,
            'message' => 'Payment method deleted successfully',
        ]);
    }
}
