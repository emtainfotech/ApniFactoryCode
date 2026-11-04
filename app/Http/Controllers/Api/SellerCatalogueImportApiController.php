<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SellerCatalogueImport;
use App\Services\SellerCatalogueImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SellerCatalogueImportApiController extends Controller
{
    public function __construct(private SellerCatalogueImportService $importService)
    {
    }

    /**
     * GET /api/v2/seller/catalogue/import/template
     * Download the standardized catalogue import template.
     */
    public function template(Request $request): BinaryFileResponse
    {
        $user = $request->user();
        $company = $user->resolveSellerCompany();

        if (!$company) {
            abort(403, 'No seller company account associated with authenticated user.');
        }

        $format = strtolower($request->query('format', 'xlsx'));
        $tempPath = $this->importService->generateTemplate($company, $format);

        $fileName = "apnifactory_catalogue_import_template.{$format}";
        $mime = $format === 'csv'
            ? 'text/csv; charset=UTF-8'
            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        return response()->download($tempPath, $fileName, [
            'Content-Type' => $mime,
        ])->deleteFileAfterSend(true);
    }

    /**
     * POST /api/v2/seller/catalogue/import/validate
     * Stage 1: Upload spreadsheet and perform validation.
     */
    public function validateUpload(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->resolveSellerCompany();

        if (!$company) {
            return response()->json(['status' => false, 'message' => 'No seller company found.'], 403);
        }

        $request->validate([
            'file' => 'required|file|max:20480', // 20MB max
        ]);

        $import = $this->importService->uploadAndValidate(
            $company,
            $user,
            $request->file('file')
        );

        $summary = $import->validation_summary['summary'] ?? [];

        return response()->json([
            'status'  => true,
            'message' => $import->isValidated() ? 'File validated successfully. Ready for confirmation.' : 'Validation finished with errors.',
            'data'    => [
                'import_id'          => $import->id,
                'status'             => $import->status,
                'original_filename'  => $import->original_filename,
                'total_rows'         => $import->total_rows,
                'valid_rows'         => $import->valid_rows,
                'invalid_rows'       => $import->invalid_rows,
                'summary'            => $summary,
                'sample_errors'      => $import->error_summary['sample_errors'] ?? [],
                'error_report_url'   => $import->api_error_report_url,
                'can_confirm'        => $import->canConfirm(),
            ],
        ], $import->isValidated() ? 200 : 422);
    }

    /**
     * POST /api/v2/seller/catalogue/import/{id}/confirm
     * Stage 2: Confirm and execute the validated catalogue import.
     */
    public function confirm(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $company = $user->resolveSellerCompany();

        if (!$company) {
            return response()->json(['status' => false, 'message' => 'No seller company found.'], 403);
        }

        // IDOR prevention
        $import = SellerCatalogueImport::where('company_id', $company->id)->findOrFail($id);

        if (!$import->canConfirm()) {
            return response()->json([
                'status'  => false,
                'message' => "Import #{$import->id} cannot be confirmed (current status: {$import->status}, valid rows: {$import->valid_rows}).",
            ], 400);
        }

        $skipInvalid = $request->boolean('skip_invalid', true);

        $updatedImport = $this->importService->executeImport($import, $skipInvalid);

        return response()->json([
            'status'  => true,
            'message' => 'Catalogue import processed successfully.',
            'data'    => [
                'import_id'        => $updatedImport->id,
                'status'           => $updatedImport->status,
                'created_products' => $updatedImport->created_products,
                'updated_products' => $updatedImport->updated_products,
                'created_variants' => $updatedImport->created_variants,
                'updated_variants' => $updatedImport->updated_variants,
                'created_listings' => $updatedImport->created_listings,
                'updated_listings' => $updatedImport->updated_listings,
                'failed_rows'      => $updatedImport->failed_rows,
                'completed_at'     => $updatedImport->completed_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * GET /api/v2/seller/catalogue/import/{id}
     * Inspect single import record status and validation diagnostics.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $company = $user->resolveSellerCompany();

        if (!$company) {
            return response()->json(['status' => false, 'message' => 'No seller company found.'], 403);
        }

        // IDOR prevention
        $import = SellerCatalogueImport::where('company_id', $company->id)->findOrFail($id);

        return response()->json([
            'status' => true,
            'data'   => [
                'id'                 => $import->id,
                'status'             => $import->status,
                'original_filename'  => $import->original_filename,
                'import_format'      => $import->import_format,
                'file_size'          => $import->file_size,
                'total_rows'         => $import->total_rows,
                'valid_rows'         => $import->valid_rows,
                'invalid_rows'       => $import->invalid_rows,
                'created_products'   => $import->created_products,
                'updated_products'   => $import->updated_products,
                'created_variants'   => $import->created_variants,
                'updated_variants'   => $import->updated_variants,
                'created_listings'   => $import->created_listings,
                'updated_listings'   => $import->updated_listings,
                'failed_rows'        => $import->failed_rows,
                'validation_summary' => $import->validation_summary['summary'] ?? null,
                'error_summary'      => $import->error_summary,
                'error_report_url'   => $import->api_error_report_url,
                'created_at'         => $import->created_at?->toIso8601String(),
                'completed_at'       => $import->completed_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * GET /api/v2/seller/catalogue/imports
     * List paginated import history for the company.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->resolveSellerCompany();

        if (!$company) {
            return response()->json(['status' => false, 'message' => 'No seller company found.'], 403);
        }

        if (!$user->isAdmin() && !$user->hasSellerPermission('excel.view_history')) {
            return response()->json(['status' => false, 'message' => 'Permission denied: excel.view_history required.'], 403);
        }

        $imports = SellerCatalogueImport::where('company_id', $company->id)
            ->with('user:id,name,email')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->through(fn($i) => [
                'id'                => $i->id,
                'status'            => $i->status,
                'original_filename' => $i->original_filename,
                'import_format'     => $i->import_format,
                'total_rows'        => $i->total_rows,
                'valid_rows'        => $i->valid_rows,
                'invalid_rows'      => $i->invalid_rows,
                'created_listings'  => $i->created_listings,
                'updated_listings'  => $i->updated_listings,
                'error_report_url'  => $i->api_error_report_url,
                'requested_by'      => $i->user ? ['id' => $i->user->id, 'name' => $i->user->name] : null,
                'created_at'        => $i->created_at?->toIso8601String(),
                'completed_at'      => $i->completed_at?->toIso8601String(),
            ]);

        return response()->json(['status' => true, 'data' => $imports]);
    }

    /**
     * GET /api/v2/seller/catalogue/imports/{id}/errors
     * Authenticated download of error report CSV.
     */
    public function errors(Request $request, int $id): BinaryFileResponse
    {
        $user = $request->user();
        $company = $user->resolveSellerCompany();

        if (!$company) {
            abort(403, 'No seller company found.');
        }

        // IDOR prevention
        $import = SellerCatalogueImport::where('company_id', $company->id)->findOrFail($id);

        return $this->importService->getErrorReportResponse($import, $user);
    }
}
