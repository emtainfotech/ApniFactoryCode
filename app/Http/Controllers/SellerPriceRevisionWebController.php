<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\PriceRevision;
use App\Models\Product;
use App\Models\SellerListing;
use App\Services\DynamicPriceRevisionService;
use App\Services\SellerPermissionService;
use Illuminate\Http\Request;

class SellerPriceRevisionWebController extends Controller
{
    public function __construct(
        private DynamicPriceRevisionService $pricingService,
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
     * Revisions list & history.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();

        if (!$user->isAdmin()) {
            if (!$user->hasSellerPermission('pricing.view') && !$user->hasSellerPermission('pricing.view_history')) {
                abort(403, 'Permission denied: pricing.view or pricing.view_history required.');
            }
        }

        $query = PriceRevision::forCompany($company->id)
            ->with(['creator', 'approver'])
            ->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('revision_method')) {
            $query->where('revision_method', $request->revision_method);
        }

        $revisions = $query->paginate(15);

        // Summary counts
        $counts = [
            'total' => PriceRevision::forCompany($company->id)->count(),
            'active' => PriceRevision::forCompany($company->id)->where('status', 'active')->count(),
            'scheduled' => PriceRevision::forCompany($company->id)->where('status', 'scheduled')->count(),
            'pending_approval' => PriceRevision::forCompany($company->id)->where('status', 'pending_approval')->count(),
        ];

        return view('seller.pricing.revisions.index', compact('company', 'revisions', 'counts'));
    }

    /**
     * Create / schedule new price revision wizard form.
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();

        if (!$user->isAdmin() && !$user->hasSellerPermission('pricing.update')) {
            abort(403, 'Permission denied: pricing.update required.');
        }

        $listings = SellerListing::where('company_id', $company->id)->where('status', '!=', 'Archived');
        $productIds = (clone $listings)->pluck('product_id')->unique();

        $categories = Category::whereIn('id', Product::whereIn('id', $productIds)->pluck('category_id')->unique())
            ->orderBy('name')
            ->get();

        $brands = Brand::whereIn('id', Product::whereIn('id', $productIds)->pluck('brand_id')->unique())
            ->orderBy('name')
            ->get();

        $units = (clone $listings)
            ->pluck('unit_of_measurement')
            ->filter()
            ->unique()
            ->values();

        return view('seller.pricing.revisions.create', compact('company', 'categories', 'brands', 'units'));
    }

    /**
     * Preview calculation via AJAX.
     */
    public function preview(Request $request)
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();

        if (!$user->isAdmin() && !$user->hasSellerPermission('pricing.view') && !$user->hasSellerPermission('pricing.update')) {
            return response()->json(['status' => false, 'message' => 'Permission denied.'], 403);
        }

        try {
            $preview = $this->pricingService->previewRevision($company, $request->all());
            return response()->json([
                'status' => true,
                'data' => $preview,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Store and schedule price revision.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();

        if (!$user->isAdmin() && !$user->hasSellerPermission('pricing.update')) {
            abort(403, 'Permission denied: pricing.update required.');
        }

        $request->validate([
            'effective_at' => 'required',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $revision = $this->pricingService->createRevision($company, $user, $request->all());

            return redirect()->route('seller.pricing.revisions.show', $revision->id)
                ->with('success', "Price revision {$revision->revision_code} successfully scheduled for {$revision->effective_at->format('d M Y, h:i A')}.");
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to schedule revision: ' . $e->getMessage());
        }
    }

    /**
     * Show price revision detail with affected SKU items.
     */
    public function show($id)
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();

        $revision = PriceRevision::forCompany($company->id)
            ->with(['creator', 'approver', 'items.product', 'items.variant'])
            ->where('id', $id)
            ->firstOrFail();

        return view('seller.pricing.revisions.show', compact('company', 'revision'));
    }

    /**
     * Cancel pending or scheduled price revision.
     */
    public function cancel(Request $request, $id)
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();

        if (!$user->isAdmin() && !$user->hasSellerPermission('pricing.update')) {
            abort(403, 'Permission denied: pricing.update required.');
        }

        $revision = PriceRevision::forCompany($company->id)->where('id', $id)->firstOrFail();

        try {
            $this->pricingService->cancelRevision($revision, $user);
            return redirect()->route('seller.pricing.revisions.show', $id)
                ->with('success', "Price revision {$revision->revision_code} has been cancelled.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
