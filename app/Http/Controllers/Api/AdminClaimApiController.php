<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FulfilmentClaim;
use App\Services\BuyerDisputeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminClaimApiController extends Controller
{
    public function __construct(
        protected BuyerDisputeService $disputeService
    ) {
    }

    /**
     * GET /api/v2/admin/claims
     * Central operations dispute overview with metrics & filters.
     */
    public function index(Request $request): JsonResponse
    {
        $claims = $this->disputeService->getAdminClaims($request->all());
        $metrics = $this->disputeService->getAdminMetrics();

        return response()->json([
            'status'  => true,
            'metrics' => $metrics,
            'data'    => $claims,
        ]);
    }

    /**
     * GET /api/v2/admin/claims/{id}
     * Inspect full claim details including admin notes & audit log.
     */
    public function show(int $id): JsonResponse
    {
        $claim = FulfilmentClaim::with([
            'items',
            'evidence',
            'history',
            'sellerFulfilment.company',
            'order',
            'parentOrder',
            'seller',
            'customer',
            'resolvedBy',
        ])->findOrFail($id);

        return response()->json([
            'status' => true,
            'data'   => $claim,
        ]);
    }

    /**
     * POST /api/v2/admin/claims/{id}/status
     * Admin/Operations transitions claim status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status'             => 'required|string|in:under_review,resolved_settled,rejected,cancelled',
            'admin_notes'        => 'nullable|string|max:2000',
            'resolution_summary' => 'nullable|string|max:2000',
        ]);

        $claim = FulfilmentClaim::findOrFail($id);
        $user = $request->user();
        $adminUserId = $user ? (int)$user->id : 1;
        $adminName = $user ? ($user->name ?? $user->email) : 'Operations Admin';

        $updated = $this->disputeService->updateClaimStatusByAdmin(
            $claim,
            $validated['status'],
            $adminUserId,
            $adminName,
            $validated['admin_notes'] ?? null,
            $validated['resolution_summary'] ?? null
        );

        return response()->json([
            'status'  => true,
            'message' => "Claim status successfully updated to '{$validated['status']}'.",
            'data'    => $updated,
        ]);
    }
}

