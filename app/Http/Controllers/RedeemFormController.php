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
    public function index()
    {
        $user = auth()->user();
        
        // Super admin can see all redeem forms, others see only redeem forms from their company
        if ($user->hasRole('super admin')) {
            $redeemForms = RedeemForm::with(['customer', 'page', 'user', 'paymentMethod', 'company'])->orderBy('created_at', 'desc')->get();
        } else {
            $redeemForms = RedeemForm::with(['customer', 'page', 'user', 'paymentMethod', 'company'])
                ->where('company_id', $user->company_id)
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        return response()->json([
            'data' => $redeemForms,
            'total' => $redeemForms->count(),
        ]);
    }

    /**
     * Display a listing of redeem forms that are pending or in process.
     */
    public function notPaid()
    {
        $user = auth()->user();
        
        // Super admin can see all redeem forms from all companies
        // Regular users see only redeem forms from their company
        // Filter to show only entries where status is 'pending' or 'in process'
        if ($user->hasRole('super admin')) {
            $redeemForms = RedeemForm::with(['customer', 'page', 'user', 'paymentMethod', 'company'])
                ->whereIn('status', ['pending', 'in process'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $redeemForms = RedeemForm::with(['customer', 'page', 'user', 'paymentMethod', 'company'])
                ->where('company_id', $user->company_id)
                ->whereIn('status', ['pending', 'in process'])
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        return response()->json([
            'data' => $redeemForms,
            'total' => $redeemForms->count(),
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
