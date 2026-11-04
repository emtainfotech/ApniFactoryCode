<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SellerCatalogueExport;
use App\Services\SellerCatalogueExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SellerCatalogueExportApiController extends Controller
{
    public function __construct(private SellerCatalogueExportService $exportService)
    {
    }

    /**
     * POST /api/v2/seller/catalogue/export
     * Initiate or execute an Excel/CSV catalogue export.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->resolveSellerCompany();

        if (!$company) {
            return response()->json([
                'status'  => false,
                'message' => 'No seller company account associated with authenticated user.',
            ], 403);
        }

        if (!$user->isAdmin() && !$user->hasSellerPermission('excel.export')) {
            return response()->json([
                'status'  => false,
                'message' => 'Permission denied: excel.export required to export catalogue.',
            ], 403);
        }

        $validated = $request->validate([
            'format'          => 'nullable|string|in:xlsx,csv,XLSX,CSV',
            'product_ids'     => 'nullable|array',
            'product_ids.*'   => 'integer',
            'product_id'      => 'nullable|integer',
            'listing_ids'     => 'nullable|array',
            'listing_ids.*'   => 'integer',
            'category_id'     => 'nullable|integer',
            'subcategory_id'  => 'nullable|integer',
            'brand_id'        => 'nullable|integer',
            'product_name'    => 'nullable|string|max:255',
            'seller_sku'      => 'nullable|string|max:100',
            'status'          => 'nullable|string|max:50',
            'variant_status'  => 'nullable|string|max:50',
            'stock_status'    => 'nullable|string|in:in_stock,out_of_stock,low_stock',
            'async'           => 'nullable|boolean',
        ]);

        $format = strtolower($validated['format'] ?? 'xlsx');
        $isAsync = (bool) ($validated['async'] ?? false);

        // Strip non-filter parameters
        $filters = collect($validated)->except(['format', 'async'])->filter(fn($v) => $v !== null && $v !== '')->toArray();

        $export = $this->exportService->initiateExport(
            $company,
            $user,
            $filters,
            $format,
            $request->ip(),
            $isAsync
        );

        $statusCode = $isAsync ? 202 : 200;

        return response()->json([
            'status'  => true,
            'message' => $isAsync ? 'Catalogue export queued for background processing.' : 'Catalogue export generated successfully.',
            'data'    => [
                'export_id'      => $export->id,
                'status'         => $export->status,
                'export_format'  => $export->export_format,
                'total_records'  => $export->total_records,
                'file_name'      => $export->file_name,
                'file_size'      => $export->file_size,
                'download_url'   => $export->api_download_url,
                'started_at'     => $export->started_at?->toIso8601String(),
                'completed_at'   => $export->completed_at?->toIso8601String(),
                'expires_at'     => $export->expires_at?->toIso8601String(),
                'filters'        => $export->filters,
            ],
        ], $statusCode);
    }

    /**
     * GET /api/v2/seller/catalogue/exports
     * List all export history for the authenticated seller's company.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->resolveSellerCompany();

        if (!$company) {
            return response()->json(['status' => false, 'message' => 'No seller company found.'], 403);
        }

        if (!$user->isAdmin() && !$user->hasSellerPermission('excel.export')) {
            return response()->json(['status' => false, 'message' => 'Permission denied: excel.export required.'], 403);
        }

        $exports = SellerCatalogueExport::where('company_id', $company->id)
            ->with('user:id,name,email')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->through(fn($e) => [
                'id'            => $e->id,
                'status'        => $e->status,
                'export_format' => $e->export_format,
                'total_records' => $e->total_records,
                'file_name'     => $e->file_name,
                'file_size'     => $e->file_size,
                'download_url'  => $e->api_download_url,
                'requested_by'  => $e->user ? ['id' => $e->user->id, 'name' => $e->user->name] : null,
                'filters'       => $e->filters,
                'created_at'    => $e->created_at?->toIso8601String(),
                'completed_at'  => $e->completed_at?->toIso8601String(),
                'expires_at'    => $e->expires_at?->toIso8601String(),
            ]);

        return response()->json(['status' => true, 'data' => $exports]);
    }

    /**
     * GET /api/v2/seller/catalogue/exports/{id}
     * Inspect single export job details and status.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $company = $user->resolveSellerCompany();

        if (!$company) {
            return response()->json(['status' => false, 'message' => 'No seller company found.'], 403);
        }

        // IDOR prevention: always verify company_id
        $export = SellerCatalogueExport::where('company_id', $company->id)->findOrFail($id);

        return response()->json([
            'status' => true,
            'data'   => [
                'id'            => $export->id,
                'status'        => $export->status,
                'export_format' => $export->export_format,
                'total_records' => $export->total_records,
                'file_name'     => $export->file_name,
                'file_size'     => $export->file_size,
                'download_url'  => $export->api_download_url,
                'error_message' => $export->error_message,
                'filters'       => $export->filters,
                'created_at'    => $export->created_at?->toIso8601String(),
                'started_at'    => $export->started_at?->toIso8601String(),
                'completed_at'  => $export->completed_at?->toIso8601String(),
                'expires_at'    => $export->expires_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * GET /api/v2/seller/catalogue/exports/{id}/download
     * Authenticated secure streaming file download.
     */
    public function download(Request $request, int $id): BinaryFileResponse
    {
        $user = $request->user();
        $company = $user->resolveSellerCompany();

        if (!$company) {
            abort(403, 'No seller company found.');
        }

        // IDOR prevention
        $export = SellerCatalogueExport::where('company_id', $company->id)->findOrFail($id);

        return $this->exportService->getDownloadResponse($export, $user);
    }
}

