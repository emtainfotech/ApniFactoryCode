<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use App\Services\ProductCatalogueApprovalService;
use App\Services\SellerPermissionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SellerCatalogueApprovalWebController extends Controller
{
    public function __construct(
        private ProductCatalogueApprovalService $approvalService,
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

    private function findSellerProduct(int $id, Company $company): Product
    {
        $product = Product::where('id', $id)->firstOrFail();
        $this->approvalService->ensureProductOwnership($product, $company);
        return $product;
    }

    /**
     * Display seller's catalogue products and their approval submission statuses.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();

        if (!$user->isAdmin()) {
            if (!$user->hasSellerPermission('products.view') && !$user->hasSellerPermission('products.create_listing') && !$user->hasSellerPermission('products.edit')) {
                abort(403, 'Permission denied: products.view, products.create_listing, or products.edit required.');
            }
        }

        $query = Product::where('user_id', $company->user_id)
            ->with(['category', 'brand', 'variants', 'listings', 'latestValidationResult'])
            ->withCount(['variants', 'listings']);

        // Filter by Approval Status
        $currentStatus = $request->input('status', 'all');
        if (!empty($currentStatus) && $currentStatus !== 'all') {
            $query->where('approval_status', $currentStatus);
        }

        // Search Filter
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'LIKE', "%{$s}%")
                  ->orWhere('title', 'LIKE', "%{$s}%")
                  ->orWhereHas('listings', fn($lq) => $lq->where('seller_sku', 'LIKE', "%{$s}%"));
            });
        }

        $products = $query->latest('id')->paginate(15)->withQueryString();

        // Counters for status pills
        $baseQuery = Product::where('user_id', $company->user_id);
        $counts = [
            'all'                   => (clone $baseQuery)->count(),
            'draft'                 => (clone $baseQuery)->where('approval_status', 'draft')->count(),
            'pending_review'        => (clone $baseQuery)->where('approval_status', 'pending_review')->count(),
            'resubmitted'           => (clone $baseQuery)->where('approval_status', 'resubmitted')->count(),
            'corrections_requested' => (clone $baseQuery)->where('approval_status', 'corrections_requested')->count(),
            'rejected'              => (clone $baseQuery)->where('approval_status', 'rejected')->count(),
            'approved'              => (clone $baseQuery)->where('approval_status', 'approved')->count(),
        ];

        return view('seller.catalogue.submissions', compact('company', 'products', 'counts', 'currentStatus'));
    }

    /**
     * Show detailed submission status, validation feedback, and review audit trail.
     */
    public function show(int $id)
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();
        $product = $this->findSellerProduct($id, $company);

        $details = $this->approvalService->getReviewDetails($product);

        return view('seller.catalogue.submission-detail', array_merge($details, [
            'user'    => $user,
            'company' => $company,
        ]));
    }

    /**
     * Run pre-submission validation manually.
     */
    public function validateProduct(Request $request, int $id)
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();
        $product = $this->findSellerProduct($id, $company);

        $result = $this->approvalService->validateProduct($product, $company, $user);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status'  => true,
                'result'  => $result,
                'message' => $result->isPassed()
                    ? 'Product passed validation and is ready to submit!'
                    : 'Product has validation errors.',
            ]);
        }

        if ($result->hasBlockingErrors()) {
            return back()->with('error', "Validation failed with {$result->error_count} blocking errors. Please correct them before submitting.")
                ->with('validation_result', $result);
        }

        return back()->with('success', "Catalogue validation passed successfully with {$result->warning_count} warning(s). Product is ready to be submitted for admin review.")
            ->with('validation_result', $result);
    }

    /**
     * Submit product for Admin Review.
     */
    public function submit(Request $request, int $id)
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();
        $product = $this->findSellerProduct($id, $company);

        try {
            $this->approvalService->submitForReview($product, $company, $user, $request->input('notes'));

            return redirect()->route('seller.catalogue.submissions')
                ->with('success', "Product '{$product->name}' has been successfully submitted for Administrator Review.");
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->first() ?: 'Product submission was blocked due to validation errors.';
            return back()->with('error', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Resubmit corrected product for Admin Review.
     */
    public function resubmit(Request $request, int $id)
    {
        $user = auth()->user();
        $company = $this->resolveCompanyOrAbort();
        $product = $this->findSellerProduct($id, $company);

        try {
            $this->approvalService->resubmit($product, $company, $user, $request->input('notes'));

            return redirect()->route('seller.catalogue.submissions')
                ->with('success', "Product '{$product->name}' has been resubmitted for admin evaluation.");
        } catch (ValidationException $e) {
            $msg = collect($e->errors())->flatten()->first() ?: 'Resubmission blocked.';
            return back()->with('error', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
