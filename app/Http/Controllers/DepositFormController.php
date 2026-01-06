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
    public function index()
    {
        $user = auth()->user();
        
        // Super admin can see all deposit forms, others see only deposit forms from their company
        if ($user->hasRole('super admin')) {
            $depositForms = DepositForm::with(['customer', 'page', 'user', 'company', 'paymentMethod'])->orderBy('created_at', 'desc')->get();
        } else {
            $depositForms = DepositForm::with(['customer', 'page', 'user', 'company', 'paymentMethod'])
                ->where('company_id', $user->company_id)
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        return response()->json([
            'data' => $depositForms,
            'total' => $depositForms->count(),
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
