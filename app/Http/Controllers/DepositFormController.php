<?php

namespace App\Http\Controllers;

use App\Models\DepositForm;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepositFormController extends Controller
{
    /**
     * Display a listing of deposit forms.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get pagination parameters
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $search = $request->input('search', '');
        $sortColumn = $request->input('sort_column', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Base query with relationships
        $query = DepositForm::with(['customer', 'page', 'user', 'company', 'paymentMethod']);

        // Super admin can see all deposit forms, others see only deposit forms from their company
        if (!$user->hasRole('super admin')) {
            $query->where('company_id', $user->company_id);
        }

        // Apply column filters
        if ($request->has('filters')) {
            $filters = $request->input('filters');

            if (!empty($filters['customer.customer_id'])) {
                $query->whereHas('customer', function ($q) use ($filters) {
                    $q->where('customer_id', 'like', '%' . $filters['customer.customer_id'] . '%');
                });
            }

            if (!empty($filters['customer.customer_name'])) {
                $query->whereHas('customer', function ($q) use ($filters) {
                    $q->where('customer_name', 'like', '%' . $filters['customer.customer_name'] . '%');
                });
            }

            if (!empty($filters['deposit'])) {
                $query->where('deposit', 'like', '%' . $filters['deposit'] . '%');
            }

            if (!empty($filters['bonus'])) {
                $query->where('bonus', 'like', '%' . $filters['bonus'] . '%');
            }

            if (!empty($filters['game_id'])) {
                $query->where('game_id', 'like', '%' . $filters['game_id'] . '%');
            }

            if (!empty($filters['page.page_name'])) {
                $query->whereHas('page', function ($q) use ($filters) {
                    $q->where('page_name', 'like', '%' . $filters['page.page_name'] . '%');
                });
            }

            if (!empty($filters['payment_method.payment_method_name'])) {
                $query->whereHas('paymentMethod', function ($q) use ($filters) {
                    $q->where('payment_method_name', 'like', '%' . $filters['payment_method.payment_method_name'] . '%');
                });
            }

            if (!empty($filters['user.name'])) {
                $query->whereHas('user', function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['user.name'] . '%');
                });
            }

            if (!empty($filters['remarks'])) {
                $query->where('remarks', 'like', '%' . $filters['remarks'] . '%');
            }

            if (!empty($filters['company.company_name']) && $user->hasRole('super admin')) {
                $query->whereHas('company', function ($q) use ($filters) {
                    $q->where('company_name', 'like', '%' . $filters['company.company_name'] . '%');
                });
            }

            // Date range filter
            if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
                $query->whereBetween('created_at', [
                    $filters['date_from'] . ' 00:00:00',
                    $filters['date_to'] . ' 23:59:59'
                ]);
            }
        }

        // Apply sorting
        if ($sortColumn) {
            // Handle nested sorting
            if (str_contains($sortColumn, '.')) {
                // For now, default to created_at for nested columns
                $query->orderBy('created_at', $sortDirection);
            } else {
                $query->orderBy($sortColumn, $sortDirection);
            }
        }

        // Paginate results
        $depositForms = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $depositForms->items(),
            'total' => $depositForms->total(),
            'current_page' => $depositForms->currentPage(),
            'last_page' => $depositForms->lastPage(),
            'per_page' => $depositForms->perPage(),
        ]);
    }

    /**
     * Store a newly created deposit form.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super admin');

        $validated = $request->validate([
            'customer_id' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'deposit' => 'required|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'game_id' => 'nullable|string|max:255',
            'page_id' => 'required|exists:pages,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'remarks' => 'nullable|string',
            'company_id' => $isSuperAdmin ? 'required|exists:companies,id' : 'nullable',
        ]);

        // Find or create customer
        $customer = Customer::firstOrCreate(
            ['customer_id' => $validated['customer_id']],
            ['customer_name' => $validated['customer_name']]
        );

        // Update customer name if it was provided and different
        if ($customer->customer_name !== $validated['customer_name']) {
            $customer->update(['customer_name' => $validated['customer_name']]);
        }

        // Determine company_id
        $companyId = $isSuperAdmin ? $validated['company_id'] : $user->company_id;

        // Create deposit form
        $depositForm = DepositForm::create([
            'customer_id' => $customer->id,
            'deposit' => $validated['deposit'],
            'bonus' => $validated['bonus'] ?? 0,
            'game_id' => $validated['game_id'] ?? null,
            'page_id' => $validated['page_id'],
            'payment_method_id' => $validated['payment_method_id'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
            'user_id' => Auth::id(),
            'company_id' => $companyId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Deposit form submitted successfully',
            'deposit_form' => $depositForm->load(['customer', 'page', 'paymentMethod']),
        ], 201);
    }

    /**
     * Update the specified deposit form.
     */
    public function update(Request $request, DepositForm $depositForm)
    {
        $validated = $request->validate([
            'customer_id' => 'sometimes|required|string|max:255',
            'customer_name' => 'sometimes|required|string|max:255',
            'deposit' => 'sometimes|required|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'game_id' => 'nullable|string|max:255',
            'page_id' => 'sometimes|required|exists:pages,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'remarks' => 'nullable|string',
        ]);

        // Update customer if customer_id or customer_name is provided
        if (isset($validated['customer_id']) || isset($validated['customer_name'])) {
            $customer = Customer::firstOrCreate(
                ['customer_id' => $validated['customer_id'] ?? $depositForm->customer->customer_id],
                ['customer_name' => $validated['customer_name'] ?? $depositForm->customer->customer_name]
            );

            if (isset($validated['customer_name']) && $customer->customer_name !== $validated['customer_name']) {
                $customer->update(['customer_name' => $validated['customer_name']]);
            }

            $validated['customer_id'] = $customer->id;
        }

        // Update user_id to track who updated
        $validated['user_id'] = Auth::id();

        $depositForm->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Deposit form updated successfully',
            'deposit_form' => $depositForm->load(['customer', 'page', 'user', 'company', 'paymentMethod']),
        ]);
    }

    /**
     * Remove the specified deposit form.
     */
    public function destroy(DepositForm $depositForm)
    {
        $depositForm->delete();

        return response()->json([
            'success' => true,
            'message' => 'Deposit form deleted successfully',
        ]);
    }
}
