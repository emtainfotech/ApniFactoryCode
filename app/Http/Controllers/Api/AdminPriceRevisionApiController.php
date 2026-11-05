<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PriceRevision;
use App\Services\DynamicPriceRevisionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminPriceRevisionApiController extends Controller
{
    protected DynamicPriceRevisionService $pricingService;

    public function __construct(DynamicPriceRevisionService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * Ensure authenticated user is an administrator.
     */
    protected function checkAdmin(Request $request): ?JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Admin access required.',
            ], 403);
        }
        return null;
    }

    /**
     * GET /api/v2/admin/pricing/revisions/queue
     * List all price revisions awaiting platform admin approval.
     */
    public function queue(Request $request): JsonResponse
    {
        if ($denied = $this->checkAdmin($request)) {
            return $denied;
        }

        $query = PriceRevision::with(['company:id,name,email,mobile', 'creator:id,name,email'])
            ->where('status', 'pending_approval')
            ->orderBy('id', 'asc');

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $perPage = min((int)$request->get('per_page', 15), 50);
        $revisions = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Pending price revisions queue retrieved successfully.',
            'data' => $revisions,
        ]);
    }

    /**
     * GET /api/v2/admin/pricing/revisions/{id}
     * Inspect any price revision details as platform admin.
     */
    public function show(Request $request, $id): JsonResponse
    {
        if ($denied = $this->checkAdmin($request)) {
            return $denied;
        }

        $revision = PriceRevision::with(['company', 'creator', 'approver', 'items.product', 'items.variant'])
            ->where('id', $id)
            ->first();

        if (!$revision) {
            return response()->json([
                'status' => false,
                'message' => 'Price revision not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Price revision details retrieved.',
            'data' => $revision,
        ]);
    }

    /**
     * POST /api/v2/admin/pricing/revisions/{id}/approve
     * Approve a pending price revision.
     */
    public function approve(Request $request, $id): JsonResponse
    {
        if ($denied = $this->checkAdmin($request)) {
            return $denied;
        }

        $revision = PriceRevision::where('id', $id)->first();
        if (!$revision) {
            return response()->json([
                'status' => false,
                'message' => 'Price revision not found.',
            ], 404);
        }

        try {
            $approved = $this->pricingService->approveRevision($revision, $request->user());

            return response()->json([
                'status' => true,
                'message' => "Price revision {$revision->revision_code} approved successfully.",
                'data' => $approved,
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/v2/admin/pricing/revisions/{id}/reject
     * Reject a pending price revision with reason.
     */
    public function reject(Request $request, $id): JsonResponse
    {
        if ($denied = $this->checkAdmin($request)) {
            return $denied;
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|min:5|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'A valid rejection reason is required.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $revision = PriceRevision::where('id', $id)->first();
        if (!$revision) {
            return response()->json([
                'status' => false,
                'message' => 'Price revision not found.',
            ], 404);
        }

        try {
            $rejected = $this->pricingService->rejectRevision($revision, $request->user(), $request->reason);

            return response()->json([
                'status' => true,
                'message' => "Price revision {$revision->revision_code} rejected.",
                'data' => $rejected,
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}

