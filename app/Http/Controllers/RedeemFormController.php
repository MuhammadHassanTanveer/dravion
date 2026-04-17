<?php

namespace App\Http\Controllers;

use App\Models\RedeemForm;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedeemFormController extends Controller
{
    /**
     * Display a listing of redeem forms.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get pagination parameters
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $sortColumn = $request->input('sort_column', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Base query with relationships
        $query = RedeemForm::with(['customer', 'page', 'user', 'paymentMethod', 'company']);

        // Super admin can see all redeem forms, others see only redeem forms from their company
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

            if (!empty($filters['redeem'])) {
                $query->where('redeem', 'like', '%' . $filters['redeem'] . '%');
            }

            if (!empty($filters['tip'])) {
                $query->where('tip', 'like', '%' . $filters['tip'] . '%');
            }

            if (!empty($filters['paid'])) {
                $query->where('paid', 'like', '%' . $filters['paid'] . '%');
            }

            if (!empty($filters['customer_tag'])) {
                $query->where('customer_tag', 'like', '%' . $filters['customer_tag'] . '%');
            }

            if (!empty($filters['game_id'])) {
                $query->where('game_id', 'like', '%' . $filters['game_id'] . '%');
            }

            if (!empty($filters['page.page_name'])) {
                $query->whereHas('page', function ($q) use ($filters) {
                    $q->where('page_name', 'like', '%' . $filters['page.page_name'] . '%');
                });
            }

            if (!empty($filters['payment_method'])) {
                $query->whereHas('paymentMethod', function ($q) use ($filters) {
                    $q->where('payment_method_name', 'like', '%' . $filters['payment_method'] . '%');
                });
            }

            if (!empty($filters['user.name'])) {
                $query->whereHas('user', function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['user.name'] . '%');
                });
            }

            if (!empty($filters['status'])) {
                $query->where('status', 'like', '%' . $filters['status'] . '%');
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
            if (str_contains($sortColumn, '.')) {
                $query->orderBy('created_at', $sortDirection);
            } else {
                $query->orderBy($sortColumn, $sortDirection);
            }
        }

        // Paginate results
        $redeemForms = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $redeemForms->items(),
            'total' => $redeemForms->total(),
            'current_page' => $redeemForms->currentPage(),
            'last_page' => $redeemForms->lastPage(),
            'per_page' => $redeemForms->perPage(),
        ]);
    }

    /**
     * Display a listing of redeem forms that are pending or in process.
     */
    public function notPaid(Request $request)
    {
        $user = auth()->user();

        // Get pagination parameters
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $sortColumn = $request->input('sort_column', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Base query with relationships - filter to show only 'pending' or 'in process'
        $query = RedeemForm::with(['customer', 'page', 'user', 'paymentMethod', 'company'])
            ->whereIn('status', ['pending', 'in process']);

        // Super admin can see all, others see only their company
        if (!$user->hasRole('super admin')) {
            $query->where('company_id', $user->company_id);
        }

        // Apply column filters
        if ($request->has('filters')) {
            $filters = $request->input('filters');

            if (!empty($filters['customer.customer_name'])) {
                $query->whereHas('customer', function ($q) use ($filters) {
                    $q->where('customer_name', 'like', '%' . $filters['customer.customer_name'] . '%');
                });
            }

            if (!empty($filters['paid'])) {
                $query->where('paid', 'like', '%' . $filters['paid'] . '%');
            }

            if (!empty($filters['customer_tag'])) {
                $query->where('customer_tag', 'like', '%' . $filters['customer_tag'] . '%');
            }

            if (!empty($filters['game_id'])) {
                $query->where('game_id', 'like', '%' . $filters['game_id'] . '%');
            }

            if (!empty($filters['payment_method'])) {
                $query->whereHas('paymentMethod', function ($q) use ($filters) {
                    $q->where('payment_method_name', 'like', '%' . $filters['payment_method'] . '%');
                });
            }

            if (!empty($filters['user.name'])) {
                $query->whereHas('user', function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['user.name'] . '%');
                });
            }

            if (!empty($filters['status'])) {
                $query->where('status', 'like', '%' . $filters['status'] . '%');
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
            if (str_contains($sortColumn, '.')) {
                $query->orderBy('created_at', $sortDirection);
            } else {
                $query->orderBy($sortColumn, $sortDirection);
            }
        }

        // Paginate results
        $redeemForms = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $redeemForms->items(),
            'total' => $redeemForms->total(),
            'current_page' => $redeemForms->currentPage(),
            'last_page' => $redeemForms->lastPage(),
            'per_page' => $redeemForms->perPage(),
        ]);
    }

    /**
     * Store a newly created redeem form.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super admin');

        $validated = $request->validate([
            'customer_id' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'redeem' => 'required|numeric|min:0',
            'tip' => 'nullable|numeric|min:0',
            'paid' => 'nullable|numeric|min:0',
            'customer_tag' => 'nullable|string|max:255',
            'page_id' => 'required|exists:pages,id',
            'game_id' => 'nullable|string|max:255',
            'status' => 'sometimes|in:pending,in process,paid',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'company_id' => $isSuperAdmin ? 'required|exists:companies,id' : 'nullable',
        ]);

        // Automatically set status to 'pending' if not provided
        if (!isset($validated['status'])) {
            $validated['status'] = 'pending';
        }

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

        // Create redeem form
        $redeemForm = RedeemForm::create([
            'customer_id' => $customer->id,
            'redeem' => $validated['redeem'],
            'tip' => $validated['tip'] ?? 0,
            'paid' => $validated['paid'] ?? 0,
            'customer_tag' => $validated['customer_tag'] ?? null,
            'page_id' => $validated['page_id'],
            'game_id' => $validated['game_id'] ?? null,
            'status' => $validated['status'],
            'payment_method_id' => $validated['payment_method_id'] ?? null,
            'user_id' => Auth::id(),
            'company_id' => $companyId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Redeem form submitted successfully',
            'redeem_form' => $redeemForm->load(['customer', 'page', 'user']),
        ], 201);
    }

    /**
     * Update the specified redeem form.
     */
    public function update(Request $request, RedeemForm $redeemForm)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super admin');

        $validated = $request->validate([
            'customer_id' => 'sometimes|required|string|max:255',
            'customer_name' => 'sometimes|required|string|max:255',
            'redeem' => 'sometimes|required|numeric|min:0',
            'tip' => 'nullable|numeric|min:0',
            'paid' => 'nullable|numeric|min:0',
            'customer_tag' => 'nullable|string|max:255',
            'page_id' => 'sometimes|required|exists:pages,id',
            'game_id' => 'nullable|string|max:255',
            'status' => 'sometimes|required|in:pending,in process,paid',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'company_id' => $isSuperAdmin ? 'sometimes|required|exists:companies,id' : 'nullable',
        ]);

        // Update customer if customer_id or customer_name is provided
        if (isset($validated['customer_id']) || isset($validated['customer_name'])) {
            $customer = Customer::firstOrCreate(
                ['customer_id' => $validated['customer_id'] ?? $redeemForm->customer->customer_id],
                ['customer_name' => $validated['customer_name'] ?? $redeemForm->customer->customer_name]
            );

            if (isset($validated['customer_name']) && $customer->customer_name !== $validated['customer_name']) {
                $customer->update(['customer_name' => $validated['customer_name']]);
            }

            $validated['customer_id'] = $customer->id;
        }

        // Update user_id to track who updated
        $validated['user_id'] = Auth::id();

        // Update company_id if super admin and provided
        if ($isSuperAdmin && isset($validated['company_id'])) {
            $validated['company_id'] = $validated['company_id'];
        }

        $redeemForm->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Redeem form updated successfully',
            'redeem_form' => $redeemForm->load(['customer', 'page', 'user', 'paymentMethod', 'company']),
        ]);
    }

    /**
     * Remove the specified redeem form.
     */
    public function destroy(RedeemForm $redeemForm)
    {
        $redeemForm->delete();

        return response()->json([
            'success' => true,
            'message' => 'Redeem form deleted successfully',
        ]);
    }
}
