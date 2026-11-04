<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductCatalogueApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminCatalogueReviewApiController extends Controller
{
    public function __construct(
        private ProductCatalogueApprovalService $approvalService
    ) {
    }

    /**
     * GET /api/v2/admin/catalogue/review-queue
     * List review items with filters, status breakdown, and pagination.
     */
    public function queue(Request $request): JsonResponse
    {
        $filters = $request->only([
            'status',
            'category_id',
            'brand_id',
            'company_id',
            'date_from',
            'date_to',
            'search',
            'sort_by',
            'sort_dir',
        ]);

        $perPage = max(1, min(100, (int)$request->input('per_page', 15)));
        $paginator = $this->approvalService->getReviewQueue($filters, $perPage);

        // Status counts for queue dashboard tabs
        $statusCounts = [
            'pending_review'        => Product::where('approval_status', 'pending_review')->count(),
            'resubmitted'           => Product::where('approval_status', 'resubmitted')->count(),
            'corrections_requested' => Product::where('approval_status', 'corrections_requested')->count(),
            'rejected'              => Product::where('approval_status', 'rejected')->count(),
            'approved'              => Product::where('approval_status', 'approved')->count(),
        ];

        return response()->json([
            'status' => true,
            'data'   => [
                'counts'       => $statusCounts,
                'items'        => $paginator->items(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ], 200);
    }

    /**
     * GET /api/v2/admin/catalogue/review/{id}
     * Full inspection details for a product under review.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $details = $this->approvalService->getReviewDetails($product);

        return response()->json([
            'status' => true,
            'data'   => $details,
        ], 200);
    }

    /**
     * POST /api/v2/admin/catalogue/review/{id}/approve
     * Approve product, publish to marketplace, activate seller listings.
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $notes   = $request->input('notes');

        try {
            $updated = $this->approvalService->approveProduct($product, $request->user(), $notes);

            return response()->json([
                'status'  => true,
                'message' => "Product '{$updated->name}' has been approved and published to the marketplace.",
                'data'    => [
                    'product_id'      => $updated->id,
                    'approval_status' => $updated->approval_status,
                    'status'          => $updated->status,
                    'reviewed_at'     => $updated->reviewed_at?->toIso8601String(),
                    'reviewed_by'     => $updated->reviewed_by,
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Product cannot be approved due to validation errors.',
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    /**
     * POST /api/v2/admin/catalogue/review/{id}/reject
     * Reject product with mandatory rejection reason.
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|min:3|max:1000',
        ]);

        $product = Product::findOrFail($id);

        try {
            $updated = $this->approvalService->rejectProduct($product, $request->user(), $request->input('reason'));

            return response()->json([
                'status'  => true,
                'message' => "Product '{$updated->name}' has been rejected.",
                'data'    => [
                    'product_id'       => $updated->id,
                    'approval_status'  => $updated->approval_status,
                    'status'           => $updated->status,
                    'rejection_reason' => $updated->rejection_reason,
                    'reviewed_at'      => $updated->reviewed_at?->toIso8601String(),
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Rejection failed.',
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    /**
     * POST /api/v2/admin/catalogue/review/{id}/request-corrections
     * Request specific corrections from seller with instructions.
     */
    public function requestCorrections(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'instructions' => 'required|string|min:3|max:1000',
        ]);

        $product = Product::findOrFail($id);

        try {
            $updated = $this->approvalService->requestCorrections($product, $request->user(), $request->input('instructions'));

            return response()->json([
                'status'  => true,
                'message' => "Corrections requested for product '{$updated->name}'. Seller has been notified.",
                'data'    => [
                    'product_id'       => $updated->id,
                    'approval_status'  => $updated->approval_status,
                    'status'           => $updated->status,
                    'correction_notes' => $updated->correction_notes,
                    'reviewed_at'      => $updated->reviewed_at?->toIso8601String(),
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Request corrections failed.',
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    /**
     * GET /api/v2/admin/catalogue/review/{id}/history
     * Get complete review and audit trail for a product.
     */
    public function history(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $histories = $product->reviewHistories()
            ->with(['reviewer:id,name,email', 'seller:id,name,email', 'validationResult'])
            ->latest('id')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => [
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'history'      => $histories,
            ],
        ], 200);
    }
}

