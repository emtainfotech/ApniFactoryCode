<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\SellerCatalogueBulkOperation;
use App\Models\SellerListing;
use App\Services\SellerCatalogueBulkService;
use App\Services\SellerPermissionService;
use Illuminate\Http\Request;

class SellerCatalogueBulkWebController extends Controller
{
    public function __construct(
        private SellerCatalogueBulkService $bulkService,
        private SellerPermissionService $permissionService
    ) {
        $this->middleware('auth');
    }

    private function resolveCompanyOrAbort(): Company
    {
        $company = $this->permissionService->resolveCompany(auth()->user());
        if (!$company) {
            abort(403, 'No seller company account associated with authenticated user.');
        }
        return $company;
    }

    /**
     * Show bulk catalogue management interface with filters & multiselect table.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();

        if (!$user->isAdmin()) {
            if (!$user->hasSellerPermission('products.view') && !$user->hasSellerPermission('products.edit')) {
                abort(403, 'Permission denied: products.view or products.edit required.');
            }
        }

        $query = SellerListing::where('company_id', $company->id)
            ->with(['product.category', 'product.brand', 'variant']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', ucfirst(strtolower($request->status)));
        }
        if ($request->filled('category_id')) {
            $query->whereHas('product', fn($q) => $q->where('category_id', (int)$request->category_id));
        }
        if ($request->filled('brand_id')) {
            $query->whereHas('product', fn($q) => $q->where('brand_id', (int)$request->brand_id));
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('seller_sku', 'LIKE', "%{$s}%")
                  ->orWhereHas('product', fn($pq) => $pq->where('name', 'LIKE', "%{$s}%")->orWhere('title', 'LIKE', "%{$s}%"));
            });
        }

        $listings = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        $categories = Category::whereIn('status', ['Active', '1', 1])->orderBy('name')->get();
        $brands     = Brand::whereIn('status', ['Active', '1', 1])->orderBy('name')->get();

        $recentOperations = SellerCatalogueBulkOperation::where('company_id', $company->id)
            ->with('user')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        return view('seller.catalogue.bulk', compact('company', 'listings', 'categories', 'brands', 'recentOperations'));
    }

    /**
     * Generate preview for web action (Stage 1).
     */
    public function preview(Request $request)
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();

        $request->validate([
            'operation_type' => 'required|string|in:status_update,field_update,archive,restore',
            'selection_mode' => 'required|string|in:ids,filter',
        ]);

        $params = $request->all();
        if ($params['selection_mode'] === 'ids') {
            $params['selection_criteria'] = $request->input('selected_ids', []);
            if (is_string($params['selection_criteria'])) {
                $params['selection_criteria'] = array_filter(array_map('intval', explode(',', $params['selection_criteria'])));
            }
        } else {
            $params['selection_criteria'] = $request->only(['status', 'category_id', 'brand_id', 'search']);
        }

        $operation = $this->bulkService->preview($company, $user, $params);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => true,
                'data'   => [
                    'operation_id'     => $operation->id,
                    'status'           => $operation->status,
                    'total_matching'   => $operation->total_matching,
                    'eligible_count'   => $operation->eligible_count,
                    'ineligible_count' => $operation->ineligible_count,
                    'can_confirm'      => $operation->canConfirm(),
                    'preview_sample'   => $operation->preview_data['preview_sample'] ?? [],
                    'ineligible_items' => $operation->preview_data['ineligible_items'] ?? [],
                ],
            ]);
        }

        return view('seller.catalogue.bulk-preview', compact('company', 'operation'));
    }

    /**
     * Confirm and execute the bulk operation (Stage 2).
     */
    public function confirm(Request $request, int $id)
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();

        $executed = $this->bulkService->confirm($company, $user, $id, false);

        return redirect()->route('seller.catalogue.bulk.history')
            ->with('success', "Bulk operation #{$executed->id} processed: {$executed->success_count} updated successfully, {$executed->failed_count} failed.");
    }

    /**
     * View bulk operation audit history.
     */
    public function history()
    {
        $company = $this->resolveCompanyOrAbort();

        $operations = SellerCatalogueBulkOperation::where('company_id', $company->id)
            ->with('user')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('seller.catalogue.bulk-history', compact('company', 'operations'));
    }
}

