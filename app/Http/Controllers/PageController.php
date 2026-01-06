<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display a listing of pages.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super admin');
        
        // If super admin provides company_id, filter by that company
        // Otherwise, if super admin, show all pages
        // If not super admin, show only pages from their company
        if ($isSuperAdmin && $request->has('company_id') && $request->company_id) {
            $pages = Page::with('company')->where('company_id', $request->company_id)->get();
        } elseif ($isSuperAdmin) {
            $pages = Page::with('company')->get();
        } else {
            $pages = Page::with('company')->where('company_id', $user->company_id)->get();
        }
        
        return response()->json([
            'data' => $pages,
            'total' => $pages->count(),
        ]);
    }

    /**
     * Store a newly created page.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super admin');
        
        $validated = $request->validate([
            'page_name' => 'required|string|max:255',
            'company_id' => $isSuperAdmin ? 'required|exists:companies,id' : 'nullable',
        ]);

        // Generate page_id from page_name (uppercase, replace spaces with underscores)
        $pageId = strtoupper(str_replace(' ', '_', $validated['page_name']));
        
        // Ensure uniqueness within company
        $basePageId = $pageId;
        $counter = 1;
        $companyId = $isSuperAdmin ? $validated['company_id'] : $user->company_id;
        while (Page::where('page_id', $pageId)->where('company_id', $companyId)->exists()) {
            $pageId = $basePageId . '_' . $counter;
            $counter++;
        }

        $page = Page::create([
            'page_id' => $pageId,
            'page_name' => $validated['page_name'],
            'company_id' => $companyId,
        ]);

        // Auto-assign new page to all admin users of the same company
        // Get admin role for this specific company
        $adminRole = Role::where('name', 'admin')
            ->where('company_id', $companyId)
            ->first();
        if ($adminRole) {
            // Get all users with admin role from this company
            $adminUsers = User::where('company_id', $companyId)
                ->whereHas('roles', function ($query) use ($adminRole) {
                    $query->where('roles.id', $adminRole->id);
                })
                ->get();
            \Log::info('Auto-assigning new page to admin users', ['page_id' => $page->id, 'page_name' => $page->page_name, 'company_id' => $companyId, 'admin_users_count' => $adminUsers->count()]);
            foreach ($adminUsers as $adminUser) {
                // Get all pages from the company and sync them (to ensure admin always has all pages)
                $allCompanyPages = Page::where('company_id', $companyId)->pluck('id')->toArray();
                $adminUser->pages()->sync($allCompanyPages);
                \Log::info('Assigned all company pages to admin user', ['user_id' => $adminUser->id, 'user_name' => $adminUser->name, 'total_pages' => count($allCompanyPages)]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Page created successfully',
            'page' => $page,
        ], 201);
    }

    /**
     * Update the specified page.
     */
    public function update(Request $request, Page $page)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super admin');
        
        $validated = $request->validate([
            'page_name' => 'required|string|max:255',
            'company_id' => $isSuperAdmin ? 'sometimes|required|exists:companies,id' : 'nullable',
        ]);

        // Generate page_id from page_name
        $pageId = strtoupper(str_replace(' ', '_', $validated['page_name']));
        
        // Ensure uniqueness within company (excluding current page)
        $basePageId = $pageId;
        $counter = 1;
        $companyId = $isSuperAdmin && isset($validated['company_id']) ? $validated['company_id'] : $page->company_id;
        while (Page::where('page_id', $pageId)->where('company_id', $companyId)->where('id', '!=', $page->id)->exists()) {
            $pageId = $basePageId . '_' . $counter;
            $counter++;
        }

        $updateData = [
            'page_id' => $pageId,
            'page_name' => $validated['page_name'],
        ];
        
        if ($isSuperAdmin && isset($validated['company_id'])) {
            $updateData['company_id'] = $validated['company_id'];
        }
        
        $page->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Page updated successfully',
            'page' => $page,
        ]);
    }

    /**
     * Remove the specified page.
     */
    public function destroy(Page $page)
    {
        $page->delete();
        return response()->json([
            'success' => true,
            'message' => 'Page deleted successfully',
        ]);
    }
}
