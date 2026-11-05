<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FulfilmentClaim;
use App\Services\BuyerDisputeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerClaimApiController extends Controller
{
    public function __construct(
        protected BuyerDisputeService $disputeService
    ) {
    }

    /**
     * GET /api/v2/seller/claims
     * List all claims filed against the authenticated seller's consignments.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $sellerUserId = $user ? (int)$user->id : (int)$request->input('seller_id', 0);

        if (!$sellerUserId) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $status = $request->input('status');
        $claims = $this->disputeService->getSellerClaims($sellerUserId, $status);

        return response()->json([
            'status' => true,
            'data'   => $claims,
        ]);
    }

    /**
     * GET /api/v2/seller/claims/{id}
     * Retrieve single claim details for the seller (enforces tenant isolation).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $claim = FulfilmentClaim::with(['items', 'evidence', 'history', 'sellerFulfilment'])->findOrFail($id);
        $user = $request->user();
        $sellerUserId = $user ? (int)$user->id : (int)$request->input('seller_id', 0);
        $isAdmin = $user && str_contains($user->email ?? '', 'admin');

        if (!$isAdmin && $claim->seller_user_id !== $sellerUserId) {
            return response()->json(['status' => false, 'message' => 'Unauthorized access to claim.'], 403);
        }

        unset($claim->admin_notes); // Strip confidential admin notes

        return response()->json([
            'status' => true,
            'data'   => $claim,
        ]);
    }

    /**
     * POST /api/v2/seller/claims/{id}/respond
     * Seller submits written response remarks and counter-evidence.
     */
    public function respond(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'response'   => 'required|string|min:5|max:2000',
            'evidence'   => 'nullable|array|max:5',
            'evidence.*' => 'file|mimes:jpeg,jpg,png,webp,pdf|max:5120',
        ]);

        $claim = FulfilmentClaim::findOrFail($id);
        $user = $request->user();
        $sellerUserId = $user ? (int)$user->id : (int)$request->input('seller_id', $claim->seller_user_id);
        $sellerName = $user ? ($user->name ?? $user->email) : 'Seller';
        $isAdmin = $user && str_contains($user->email ?? '', 'admin');

        if (!$isAdmin && $claim->seller_user_id !== $sellerUserId) {
            return response()->json(['status' => false, 'message' => 'Unauthorized. You can only respond to your own seller claims.'], 403);
        }

        $evidenceFiles = $request->file('evidence') ?? [];
        $updated = $this->disputeService->submitSellerResponse(
            $claim,
            $validated['response'],
            $sellerUserId,
            $sellerName,
            $evidenceFiles
        );

        unset($updated->admin_notes);

        return response()->json([
            'status'  => true,
            'message' => 'Response recorded successfully. Operations and buyer have been informed.',
            'data'    => $updated,
        ]);
    }
}

