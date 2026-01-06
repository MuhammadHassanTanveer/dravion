<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\UserPermissionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DepositFormController;
use App\Http\Controllers\RedeemFormController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    return redirect('/login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    // Get user permissions, pages, payment methods, and companies
    Route::get('/user/permissions', [UserPermissionController::class, 'getPermissions']);
    Route::get('/user/pages', [UserPermissionController::class, 'getPages']);
    Route::get('/user/payment-methods', [UserPermissionController::class, 'getPaymentMethods']);
    Route::get('/user/companies', [UserPermissionController::class, 'getCompanies']);
    Route::put('/user/profile', [UserPermissionController::class, 'updateProfile']);
    Route::put('/user/profile', [UserPermissionController::class, 'updateProfile']);
    
    // Dashboard routes - all use same view, Vue handles routing and permissions
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/users', [DashboardController::class, 'index'])->name('dashboard.users');
    Route::get('/dashboard/roles', [DashboardController::class, 'index'])->name('dashboard.roles');
    Route::get('/dashboard/pages', [DashboardController::class, 'index'])->name('dashboard.pages');
    Route::get('/dashboard/payment-methods', [DashboardController::class, 'index'])->name('dashboard.payment-methods');
    Route::get('/dashboard/companies', [DashboardController::class, 'index'])->name('dashboard.companies');
    Route::get('/dashboard/deposit-form', [DashboardController::class, 'index'])->name('dashboard.deposit-form');
    Route::get('/dashboard/redeem-form', [DashboardController::class, 'index'])->name('dashboard.redeem-form');
    Route::get('/dashboard/redeem-process', [DashboardController::class, 'index'])->name('dashboard.redeem-process');
    Route::get('/dashboard/profile', [DashboardController::class, 'index'])->name('dashboard.profile');
    
    // Dashboard statistics API
    Route::get('/dashboard/stats', [DashboardController::class, 'getSuperAdminStatsWithFilters'])->middleware('role:super admin');
    Route::get('/dashboard/company-stats', [DashboardController::class, 'getCompanyStats'])->middleware('auth');
    
    // API routes for users with permission checks
    // Allow access if user has ANY user-related permission (view, add, edit, or delete)
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store'])->middleware('permission:add user');
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('permission:edit user');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('permission:delete user');
    
    // API routes for roles with permission checks
    // Allow access if user has any role-related OR user-related permission (for user creation form)
    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:add role');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->middleware('role_or_permission:edit role|manage permissions');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:delete role');
    
    // API routes for pages with permission checks
    Route::get('/pages', [PageController::class, 'index'])->middleware('permission:view pages');
    Route::post('/pages', [PageController::class, 'store'])->middleware('permission:add page');
    Route::put('/pages/{page}', [PageController::class, 'update'])->middleware('permission:edit page');
    Route::delete('/pages/{page}', [PageController::class, 'destroy'])->middleware('permission:delete page');
    
    // API routes for payment methods with permission checks
    Route::get('/payment-methods', [PaymentMethodController::class, 'index'])->middleware('permission:view payment methods');
    Route::post('/payment-methods', [PaymentMethodController::class, 'store'])->middleware('permission:add payment method');
    Route::put('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'update'])->middleware('permission:edit payment method');
    Route::delete('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'destroy'])->middleware('permission:delete payment method');
    
    // API routes for companies (only super admin can access)
    Route::get('/companies', [CompanyController::class, 'index'])->middleware('role:super admin');
    Route::post('/companies', [CompanyController::class, 'store'])->middleware('role:super admin');
    Route::put('/companies/{company}', [CompanyController::class, 'update'])->middleware('role:super admin');
    Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->middleware('role:super admin');
    
    // API routes for customers
    Route::get('/customers/search', [CustomerController::class, 'search']);
    Route::post('/customers', [CustomerController::class, 'store']);
    
    // API routes for deposit forms with permission checks
    Route::get('/deposit-forms', [DepositFormController::class, 'index'])->middleware('permission:view deposit forms');
    Route::post('/deposit-forms', [DepositFormController::class, 'store'])->middleware('permission:add deposit form');
    Route::put('/deposit-forms/{depositForm}', [DepositFormController::class, 'update'])->middleware('permission:edit deposit form');
    Route::delete('/deposit-forms/{depositForm}', [DepositFormController::class, 'destroy'])->middleware('permission:delete deposit form');
    
    // API routes for redeem forms with permission checks
    Route::get('/redeem-forms', [RedeemFormController::class, 'index'])->middleware('permission:view redeem forms');
    Route::get('/redeem-forms/not-paid', [RedeemFormController::class, 'notPaid'])->middleware('permission:view redeem process');
    Route::post('/redeem-forms', [RedeemFormController::class, 'store'])->middleware('permission:add redeem form');
    Route::put('/redeem-forms/{redeemForm}', [RedeemFormController::class, 'update'])->middleware('permission:edit redeem form');
    Route::delete('/redeem-forms/{redeemForm}', [RedeemFormController::class, 'destroy'])->middleware('permission:delete redeem form');
});
