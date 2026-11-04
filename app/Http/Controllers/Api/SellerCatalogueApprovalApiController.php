<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductCatalogueApprovalService;
use App\Services\SellerPermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SellerCatalogueApprovalApiController extends Controller
{
    public function __construct(
        private ProductCatalogueApprovalService $approvalService,
        private SellerPermissionService $permissionService
    ) {
    }

    private function resolveCompanyOrAbort($user)
    {
        $company = $this->permissionService->resolveCompany($user);
        if (!$company) {
            abort(403, 'No seller company account associated with authenticated user.');
        }
        return $company;
    }

    private function findSellerProduct(int $id, $company): Product
    {
        $product = Product::where('id', $id)->first();
        if (!$product) {
            abort(404, "Product #{$id} not found.");
        }

        $this->approvalService->ensureProductOwnership($product, $company);
        return $product;
    }

    /**
     * POST /api/v2/seller/catalogue/products/{id}/validate
     * Stage 1: Validate product readiness before submission.
     */
    public function validateProduct(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $company = $this->resolveCompanyOrAbort($user);
        $product = $this->findSellerProduct($id, $company);

        $result = $this->approvalService->validateProduct($product, $company, $user);

        return response()->json([
            'status'  => true,
            'message' => $result->isPassed()
                ? 'Product passed all catalogue validation checks.'
                : 'Product has validation errors or warnings.',
            'data'    => [
                'validation_id'   => $result->id,
                'product_id'      => $product->id,
                'approval_status' => $product->approval_status,
                'status'          => $result->status,
                'is_passed'       => $result->isPassed(),
                'has_blocking_errors' => $result->hasBlockingErrors(),
                'error_count'     => $result->error_count,
                'warning_count'   => $result->warning_count,
                'passed_checks'   => $result->passed_checks,
                'failed_checks'   => $result->failed_checks,
                'warnings'        => $result->warnings,
                'can_submit'      => $result->isPassed() && $product->canBeSubmitted(),
            ],
        ], 200);
    }

    /**
     * POST /api/v2/seller/catalogue/products/{id}/submit
     * Stage 2: Submit product for Admin Review.
     */
    public function submit(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $company = $this->resolveCompanyOrAbort($user);
        $product = $this->findSellerProduct($id, $company);

        $notes = $request->input('notes');

        try {
            $data = $this->approvalService->submitForReview($product, $company, $user, $notes);

            return response()->json([
                'status'  => true,
                'message' => "Product '{$product->name}' successfully submitted for administrator review.",
                'data'    => $data,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Catalogue submission blocked due to validation errors.',
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    /**
     * GET /api/v2/seller/catalogue/products/{id}/submission-status
     * Get the current submission/approval status of a product.
     */
    public function submissionStatus(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $company = $this->resolveCompanyOrAbort($user);
        $product = $this->findSellerProduct($id, $company);

        $latestValidation = $product->latestValidationResult;

        return response()->json([
            'status' => true,
            'data'   => [
                'product_id'       => $product->id,
                'product_name'     => $product->name,
                'approval_status'  => $product->approval_status,
                'submitted_at'     => $product->submitted_at?->toIso8601String(),
                'reviewed_at'      => $product->reviewed_at?->toIso8601String(),
                'last_validated_at'=> $product->last_validated_at?->toIso8601String(),
                'rejection_reason' => $product->rejection_reason,
                'correction_notes' => $product->correction_notes,
                'can_submit'       => $product->canBeSubmitted(),
                'latest_validation'=> $latestValidation ? [
                    'id'            => $latestValidation->id,
                    'status'        => $latestValidation->status,
                    'is_passed'     => $latestValidation->isPassed(),
                    'error_count'   => $latestValidation->error_count,
                    'warning_count' => $latestValidation->warning_count,
                    'failed_checks' => $latestValidation->failed_checks,
                    'warnings'      => $latestValidation->warnings,
                ] : null,
            ],
        ], 200);
    }

    /**
     * GET /api/v2/seller/catalogue/products/{id}/review-feedback
     * Get review rejection reason or correction notes with history.
     */
    public function reviewFeedback(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $company = $this->resolveCompanyOrAbort($user);
        $product = $this->findSellerProduct($id, $company);

        $latestHistory = $product->reviewHistories()
            ->whereIn('action', ['rejected', 'corrections_requested', 'approved'])
            ->latest('id')
            ->first();

        return response()->json([
            'status' => true,
            'data'   => [
                'product_id'       => $product->id,
                'product_name'     => $product->name,
                'approval_status'  => $product->approval_status,
                'rejection_reason' => $product->rejection_reason,
                'correction_notes' => $product->correction_notes,
                'reviewed_at'      => $product->reviewed_at?->toIso8601String(),
                'latest_review'    => $latestHistory ? [
                    'action'    => $latestHistory->action,
                    'notes'     => $latestHistory->notes,
                    'date'      => $latestHistory->created_at?->toIso8601String(),
                ] : null,
            ],
        ], 200);
    }

    /**
     * POST /api/v2/seller/catalogue/products/{id}/resubmit
     * Resubmit a corrected product for admin review.
     */
    public function resubmit(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $company = $this->resolveCompanyOrAbort($user);
        $product = $this->findSellerProduct($id, $company);

        $notes = $request->input('notes');

        try {
            $data = $this->approvalService->resubmit($product, $company, $user, $notes);

            return response()->json([
                'status'  => true,
                'message' => "Product '{$product->name}' has been successfully resubmitted for admin review.",
                'data'    => $data,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Resubmission blocked due to validation or status errors.',
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    /**
     * GET /api/v2/seller/catalogue/products/{id}/review-history
     * Get review history audit trail for seller.
     */
    public function reviewHistory(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $company = $this->resolveCompanyOrAbort($user);
        $product = $this->findSellerProduct($id, $company);

        $histories = $product->reviewHistories()
            ->with('reviewer:id,name')
            ->latest('id')
            ->get()
            ->map(fn($h) => [
                'id'              => $h->id,
                'action'          => $h->action,
                'previous_status' => $h->previous_status,
                'new_status'      => $h->new_status,
                'notes'           => $h->notes,
                'reviewer_name'   => $h->reviewer?->name ?? 'System',
                'created_at'      => $h->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'status' => true,
            'data'   => [
                'product_id' => $product->id,
                'history'    => $histories,
            ],
        ], 200);
    }
}
