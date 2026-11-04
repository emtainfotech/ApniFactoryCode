<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\SellerCatalogueExport;
use App\Services\SellerCatalogueExportService;
use App\Services\SellerPermissionService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SellerCatalogueExportWebController extends Controller
{
    public function __construct(
        private SellerCatalogueExportService $exportService,
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
     * Show Export Centre interface.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();

        if (!$user->isAdmin() && !$user->hasSellerPermission('excel.export')) {
            abort(403, 'Permission denied: excel.export required to access export console.');
        }

        $categories = Category::whereIn('status', ['Active', '1', 1])->orderBy('name', 'asc')->get();
        $brands     = Brand::whereIn('status', ['Active', '1', 1])->orderBy('name', 'asc')->get();

        $recentExports = SellerCatalogueExport::where('company_id', $company->id)
            ->with('user')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        $canViewPricing   = $user->isAdmin() || $user->hasSellerPermission('pricing.view');
        $canViewInventory = $user->isAdmin() || $user->hasSellerPermission('inventory.view');

        return view('seller.catalogue.export', compact(
            'company',
            'categories',
            'brands',
            'recentExports',
            'canViewPricing',
            'canViewInventory'
        ));
    }

    /**
     * Handle export generation request from web interface.
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();

        if (!$user->isAdmin() && !$user->hasSellerPermission('excel.export')) {
            abort(403, 'Permission denied: excel.export required to export catalogue.');
        }

        $validated = $request->validate([
            'format'         => 'nullable|string|in:xlsx,csv',
            'category_id'    => 'nullable|integer',
            'brand_id'       => 'nullable|integer',
            'product_name'   => 'nullable|string|max:255',
            'seller_sku'     => 'nullable|string|max:100',
            'status'         => 'nullable|string|max:50',
            'stock_status'   => 'nullable|string|in:in_stock,out_of_stock,low_stock',
            'delivery_mode'  => 'nullable|string|in:direct,async',
        ]);

        $format = $validated['format'] ?? 'xlsx';
        $isAsync = ($validated['delivery_mode'] ?? 'direct') === 'async';

        $filters = collect($validated)->except(['format', 'delivery_mode'])->filter(fn($v) => $v !== null && $v !== '')->toArray();

        $export = $this->exportService->initiateExport(
            $company,
            $user,
            $filters,
            $format,
            $request->ip(),
            $isAsync
        );

        if ($isAsync) {
            return redirect()->route('seller.catalogue.export.history')
                ->with('success', "Catalogue export #{$export->id} has been queued for background processing.");
        }

        return $this->exportService->getDownloadResponse($export, $user);
    }

    /**
     * View complete export history.
     */
    public function history(Request $request)
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();

        if (!$user->isAdmin() && !$user->hasSellerPermission('excel.export')) {
            abort(403, 'Permission denied: excel.export required.');
        }

        $exports = SellerCatalogueExport::where('company_id', $company->id)
            ->with('user')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('seller.catalogue.export-history', compact('company', 'exports'));
    }

    /**
     * Download completed export file.
     */
    public function download(int $id): BinaryFileResponse
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();

        $export = SellerCatalogueExport::where('company_id', $company->id)->findOrFail($id);

        return $this->exportService->getDownloadResponse($export, $user);
    }
}

