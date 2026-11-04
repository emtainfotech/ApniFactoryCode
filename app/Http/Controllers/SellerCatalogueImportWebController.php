<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\SellerCatalogueImport;
use App\Services\SellerCatalogueImportService;
use App\Services\SellerPermissionService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SellerCatalogueImportWebController extends Controller
{
    public function __construct(
        private SellerCatalogueImportService $importService,
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
     * Show import upload interface.
     */
    public function index()
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();

        if (!$user->isAdmin()) {
            if (!$user->hasSellerPermission('excel.upload') && !$user->hasSellerPermission('excel.templates')) {
                abort(403, 'Permission denied: excel.upload or excel.templates required.');
            }
        }

        $recentImports = SellerCatalogueImport::where('company_id', $company->id)
            ->with('user')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        return view('seller.catalogue.import', compact('company', 'recentImports'));
    }

    /**
     * Download the standardized import template.
     */
    public function template(Request $request): BinaryFileResponse
    {
        $company = $this->resolveCompanyOrAbort();
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
     * Handle Stage 1 file upload & validation.
     */
    public function validateUpload(Request $request)
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();

        $request->validate([
            'file' => 'required|file|max:20480',
        ]);

        $import = $this->importService->uploadAndValidate(
            $company,
            $user,
            $request->file('file')
        );

        return redirect()->route('seller.catalogue.import.preview', $import->id)
            ->with('success', 'File parsed and validated successfully.');
    }

    /**
     * Review validation preview before confirmation (Stage 1 Review).
     */
    public function preview(int $id)
    {
        $company = $this->resolveCompanyOrAbort();
        $import = SellerCatalogueImport::where('company_id', $company->id)->findOrFail($id);

        $summary = $import->validation_summary['summary'] ?? [];
        $rows = $import->validation_summary['rows'] ?? [];

        return view('seller.catalogue.import-preview', compact('company', 'import', 'summary', 'rows'));
    }

    /**
     * Confirm and execute import (Stage 2).
     */
    public function confirm(Request $request, int $id)
    {
        $company = $this->resolveCompanyOrAbort();
        $import = SellerCatalogueImport::where('company_id', $company->id)->findOrFail($id);

        $this->importService->executeImport($import, true);

        return redirect()->route('seller.catalogue.import.history')
            ->with('success', "Import #{$import->id} executed successfully. Catalogue records have been updated.");
    }

    /**
     * View full import history.
     */
    public function history()
    {
        $company = $this->resolveCompanyOrAbort();

        $imports = SellerCatalogueImport::where('company_id', $company->id)
            ->with('user')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('seller.catalogue.import-history', compact('company', 'imports'));
    }

    /**
     * Download error report CSV.
     */
    public function errors(int $id): BinaryFileResponse
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();

        $import = SellerCatalogueImport::where('company_id', $company->id)->findOrFail($id);

        return $this->importService->getErrorReportResponse($import, $user);
    }
}
