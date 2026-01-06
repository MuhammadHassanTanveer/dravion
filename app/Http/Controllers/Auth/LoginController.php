<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string',
            'password' => 'required|string',
        ]);

        // Trim user_id to handle any whitespace
        $userId = trim($request->user_id);
        
        // Find user by user_id (case-insensitive match using LOWER() or COLLATE)
        $user = \App\Models\User::whereRaw('LOWER(user_id) = ?', [strtolower($userId)])->first();

        if (!$user) {
            \Log::warning('Login attempt failed: User not found', [
                'user_id' => $userId,
                'ip' => $request->ip(),
            ]);
            
            throw ValidationException::withMessages([
                'user_id' => ['The provided credentials do not match our records.'],
            ]);
        }

        // Check password
        if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            \Log::warning('Login attempt failed: Invalid password', [
                'user_id' => $userId,
                'user_name' => $user->name,
                'ip' => $request->ip(),
            ]);
            
            throw ValidationException::withMessages([
                'user_id' => ['The provided credentials do not match our records.'],
            ]);
        }

        // Login successful
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        \Log::info('User logged in successfully', [
            'user_id' => $user->user_id,
            'user_name' => $user->name,
            'company_id' => $user->company_id,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => '/dashboard',
        ]);
    }

    /**
     * Handle a logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}

