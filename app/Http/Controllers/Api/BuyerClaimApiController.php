<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FulfilmentClaim;
use App\Models\SellerFulfilment;
use App\Services\BuyerDisputeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuyerClaimApiController extends Controller
{
    public function __construct(
        protected BuyerDisputeService $disputeService
    ) {
    }

    /**
     * GET /api/v2/buyer/claims
     * List all claims submitted by the authenticated buyer.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $customerId = $user ? (int)$user->id : (int)$request->input('customer_id', 0);

        if (!$customerId) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $parentOrderId = $request->filled('parent_order_id') ? (int)$request->input('parent_order_id') : null;
        $claims = $this->disputeService->getBuyerClaims($customerId, $parentOrderId);

        return response()->json([
            'status' => true,
            'data'   => $claims,
        ]);
    }

    /**
     * GET /api/v2/buyer/claims/{id}
     * Retrieve single claim details for the buyer.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $claim = FulfilmentClaim::with(['items', 'evidence', 'history', 'sellerFulfilment.company'])->findOrFail($id);
        $user = $request->user();
        $customerId = $user ? (int)$user->id : (int)$request->input('customer_id', 0);
        $isAdmin = $user && str_contains($user->email ?? '', 'admin');

        if (!$isAdmin && $claim->customer_id !== $customerId) {
            return response()->json(['status' => false, 'message' => 'Unauthorized access to claim.'], 403);
        }

        unset($claim->admin_notes); // Strip confidential admin notes

        return response()->json([
            'status' => true,
            'data'   => $claim,
        ]);
    }

    /**
     * POST /api/v2/buyer/fulfilment/{fulfilmentId}/claim
     * Submit a shortage, damage, or discrepancy claim for a seller order.
     */
    public function store(Request $request, int $fulfilmentId): JsonResponse
    {
        $validated = $request->validate([
            'claim_type'               => 'required|string|in:shortage,damage,wrong_item,other',
            'description'              => 'required|string|min:10|max:2000',
            'items'                    => 'nullable|array',
            'items.*.order_detail_id'  => 'nullable|integer',
            'items.*.product_name'     => 'nullable|string|max:255',
            'items.*.sku'              => 'nullable|string|max:100',
            'items.*.ordered_qty'      => 'nullable|integer|min:0',
            'items.*.received_qty'     => 'nullable|integer|min:0',
            'items.*.affected_qty'     => 'required_with:items|integer|min:1',
            'items.*.condition_notes'  => 'nullable|string|max:500',
            'evidence'                 => 'nullable|array|max:5',
            'evidence.*'               => 'file|mimes:jpeg,jpg,png,webp,pdf|max:5120',
            'idempotency_key'          => 'nullable|string|max:100',
        ]);

        $fulfilment = SellerFulfilment::findOrFail($fulfilmentId);
        $user = $request->user();
        $customerId = $user ? (int)$user->id : (int)$request->input('customer_id', $fulfilment->customer_id);
        $customerName = $user ? ($user->name ?? $user->email) : 'Buyer';
        $isAdmin = $user && str_contains($user->email ?? '', 'admin');

        if (!$isAdmin && $fulfilment->customer_id !== $customerId) {
            return response()->json(['status' => false, 'message' => 'Unauthorized. You can only lodge claims for your own orders.'], 403);
        }

        $evidenceFiles = $request->file('evidence') ?? [];
        $claim = $this->disputeService->submitClaim(
            $fulfilment,
            $validated['claim_type'],
            $validated['description'],
            $customerId,
            $customerName,
            $validated['items'] ?? [],
            $evidenceFiles,
            $request->input('idempotency_key')
        );

        unset($claim->admin_notes);

        return response()->json([
            'status'  => true,
            'message' => "Dispute claim #{$claim->claim_ref} lodged successfully. Operations and seller have been alerted.",
            'data'    => $claim,
        ], 201);
    }
}

