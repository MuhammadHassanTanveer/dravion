<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Search customers by customer_id.
     */
    public function search(Request $request)
    {
        $customerId = $request->input('customer_id');
        
        if (!$customerId) {
            return response()->json(['customer' => null]);
        }

        $customer = Customer::where('customer_id', $customerId)->first();
        
        return response()->json([
            'customer' => $customer,
        ]);
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|string|max:255|unique:customers',
            'customer_name' => 'required|string|max:255',
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully',
            'customer' => $customer,
        ], 201);
    }
}
