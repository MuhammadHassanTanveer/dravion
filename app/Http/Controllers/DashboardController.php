<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Company;
use App\Models\DepositForm;
use App\Models\RedeemForm;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DashboardController extends Controller
{
    /**
     * Show the dashboard.
     */
    public function index(Request $request)
    {
        // The middleware handles permission checks, so we just return the view
        // All routes use the same view, Vue handles the routing
        return view('dashboard');
    }

    /**
     * Get dashboard statistics for super admin.
     */
    public function getSuperAdminStats(Request $request)
    {
        $user = Auth::user();
        
        // Only super admin can access this endpoint
        if (!$user->hasRole('super admin')) {
            return response()->json([
                'message' => 'Unauthorized. Only super admin can access this resource.',
            ], 403);
        }

        // Get total users count
        $totalUsers = User::count();

        // Get total roles count
        $totalRoles = Role::count();

        // Get active sessions count (sessions table)
        // Count distinct user_ids where last_activity is within session lifetime
        $activeSessions = DB::table('sessions')
            ->where('last_activity', '>', now()->subMinutes(config('session.lifetime', 120))->timestamp)
            ->whereNotNull('user_id')
            ->select('user_id')
            ->distinct()
            ->count();

        // Get total permissions count
        $totalPermissions = Permission::count();

        // Get total deposit sum (all companies)
        $totalDepositSum = DepositForm::sum('deposit');

        // Get all companies
        $allCompanies = Company::all();

        // Get deposit sum per company (including companies with 0 deposit)
        $depositsByCompany = $allCompanies->map(function ($company) {
            $totalDeposit = DepositForm::where('company_id', $company->id)->sum('deposit');
            return [
                'company_id' => $company->id,
                'company_name' => $company->company_name,
                'total_deposit' => (float) $totalDeposit,
            ];
        });

        // Get total redeem sum from paid column (all companies)
        $totalRedeemSum = RedeemForm::sum('paid');

        // Get redeem sum per company from paid column (including companies with 0 redeem)
        $redeemsByCompany = $allCompanies->map(function ($company) {
            $totalRedeem = RedeemForm::where('company_id', $company->id)->sum('paid');
            return [
                'company_id' => $company->id,
                'company_name' => $company->company_name,
                'total_redeem' => (float) $totalRedeem,
            ];
        });

        return response()->json([
            'totalUsers' => $totalUsers,
            'totalRoles' => $totalRoles,
            'activeSessions' => $activeSessions,
            'totalPermissions' => $totalPermissions,
            'totalDepositSum' => (float) $totalDepositSum,
            'depositsByCompany' => $depositsByCompany,
            'totalRedeemSum' => (float) $totalRedeemSum,
            'redeemsByCompany' => $redeemsByCompany,
        ]);
    }

    /**
     * Get dashboard statistics for company users.
     */
    public function getCompanyStats(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has a company
        if (!$user->company_id) {
            return response()->json([
                'message' => 'User does not belong to any company.',
            ], 403);
        }

        // Get date range from request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $pageId = $request->input('page_id');

        // Build query for deposits
        $depositQuery = DepositForm::where('company_id', $user->company_id);
        if ($startDate && $endDate) {
            $depositQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }
        if ($pageId) {
            $depositQuery->where('page_id', $pageId);
        }
        $totalDepositSum = $depositQuery->sum('deposit');

        // Build query for redeems
        $redeemQuery = RedeemForm::where('company_id', $user->company_id);
        if ($startDate && $endDate) {
            $redeemQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }
        if ($pageId) {
            $redeemQuery->where('page_id', $pageId);
        }
        $totalRedeemSum = $redeemQuery->sum('paid');

        // Calculate profit/loss
        $profitLoss = $totalDepositSum - $totalRedeemSum;

        return response()->json([
            'totalDepositSum' => (float) $totalDepositSum,
            'totalRedeemSum' => (float) $totalRedeemSum,
            'profitLoss' => (float) $profitLoss,
        ]);
    }

    /**
     * Get dashboard statistics for super admin with filters.
     */
    public function getSuperAdminStatsWithFilters(Request $request)
    {
        $user = Auth::user();
        
        // Only super admin can access this endpoint
        if (!$user->hasRole('super admin')) {
            return response()->json([
                'message' => 'Unauthorized. Only super admin can access this resource.',
            ], 403);
        }

        // Get filters from request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $companyId = $request->input('company_id');
        $pageId = $request->input('page_id');

        // Get total users count
        $totalUsers = User::count();

        // Get total roles count
        $totalRoles = Role::count();

        // Get active sessions count
        $activeSessions = DB::table('sessions')
            ->where('last_activity', '>', now()->subMinutes(config('session.lifetime', 120))->timestamp)
            ->whereNotNull('user_id')
            ->select('user_id')
            ->distinct()
            ->count();

        // Get total permissions count
        $totalPermissions = Permission::count();

        // Build deposit query
        $depositQuery = DepositForm::query();
        if ($startDate && $endDate) {
            $depositQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }
        if ($companyId) {
            $depositQuery->where('company_id', $companyId);
        }
        if ($pageId) {
            $depositQuery->where('page_id', $pageId);
        }
        $totalDepositSum = $depositQuery->sum('deposit');

        // Build redeem query
        $redeemQuery = RedeemForm::query();
        if ($startDate && $endDate) {
            $redeemQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }
        if ($companyId) {
            $redeemQuery->where('company_id', $companyId);
        }
        if ($pageId) {
            $redeemQuery->where('page_id', $pageId);
        }
        $totalRedeemSum = $redeemQuery->sum('paid');

        // Get all companies
        $allCompanies = Company::all();

        // Get deposit sum per company (including companies with 0 deposit)
        $depositsByCompany = $allCompanies->map(function ($company) use ($startDate, $endDate, $pageId) {
            $query = DepositForm::where('company_id', $company->id);
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }
            if ($pageId) {
                $query->where('page_id', $pageId);
            }
            $totalDeposit = $query->sum('deposit');
            return [
                'company_id' => $company->id,
                'company_name' => $company->company_name,
                'total_deposit' => (float) $totalDeposit,
            ];
        });

        // Get redeem sum per company from paid column (including companies with 0 redeem)
        $redeemsByCompany = $allCompanies->map(function ($company) use ($startDate, $endDate, $pageId) {
            $query = RedeemForm::where('company_id', $company->id);
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }
            if ($pageId) {
                $query->where('page_id', $pageId);
            }
            $totalRedeem = $query->sum('paid');
            return [
                'company_id' => $company->id,
                'company_name' => $company->company_name,
                'total_redeem' => (float) $totalRedeem,
            ];
        });

        // Calculate profit/loss per company
        $profitLossByCompany = $allCompanies->map(function ($company) use ($startDate, $endDate, $pageId) {
            $depositQuery = DepositForm::where('company_id', $company->id);
            $redeemQuery = RedeemForm::where('company_id', $company->id);
            
            if ($startDate && $endDate) {
                $depositQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                $redeemQuery->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }
            if ($pageId) {
                $depositQuery->where('page_id', $pageId);
                $redeemQuery->where('page_id', $pageId);
            }
            
            $totalDeposit = $depositQuery->sum('deposit');
            $totalRedeem = $redeemQuery->sum('paid');
            $profitLoss = $totalDeposit - $totalRedeem;
            
            return [
                'company_id' => $company->id,
                'company_name' => $company->company_name,
                'profit_loss' => (float) $profitLoss,
            ];
        });

        // Calculate total profit/loss
        $totalProfitLoss = $totalDepositSum - $totalRedeemSum;

        return response()->json([
            'totalUsers' => $totalUsers,
            'totalRoles' => $totalRoles,
            'activeSessions' => $activeSessions,
            'totalPermissions' => $totalPermissions,
            'totalDepositSum' => (float) $totalDepositSum,
            'depositsByCompany' => $depositsByCompany,
            'totalRedeemSum' => (float) $totalRedeemSum,
            'redeemsByCompany' => $redeemsByCompany,
            'totalProfitLoss' => (float) $totalProfitLoss,
            'profitLossByCompany' => $profitLossByCompany,
        ]);
    }
}

