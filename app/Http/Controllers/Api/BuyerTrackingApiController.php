<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SellerFulfilment;
use App\Services\SellerFulfilmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuyerTrackingApiController extends Controller
{
    public function __construct(
        protected SellerFulfilmentService $fulfilmentService
    ) {
    }

    /**
     * GET /api/v2/buyer/orders/{orderId}/tracking
     * Retrieve aggregated multi-seller tracking view for buyer.
     */
    public function tracking(Request $request, int $orderId): JsonResponse
    {
        $order = Order::findOrFail($orderId);
        $user = $request->user();
        $customerId = $user ? (int)$user->id : (int)$request->input('customer_id', $order->customer_id);

        if ($order->customer_id != $customerId && (!$user || !str_contains($user->email ?? '', 'admin'))) {
            return response()->json(['status' => false, 'message' => 'Unauthorized access to order tracking.'], 403);
        }

        $summary = $this->fulfilmentService->getParentOrderLogisticsSummary($order);

        return response()->json([
            'status' => true,
            'data'   => $summary,
        ]);
    }

    /**
     * POST /api/v2/buyer/fulfilment/{fulfilmentId}/acknowledge
     * Buyer acknowledges receipt of goods for a seller order.
     */
    public function acknowledge(Request $request, int $fulfilmentId): JsonResponse
    {
        $fulfilment = SellerFulfilment::findOrFail($fulfilmentId);
        $user = $request->user();
        $customerId = $user ? (int)$user->id : (int)$request->input('customer_id', $fulfilment->customer_id);
        $customerName = $user ? ($user->name ?? $user->email) : 'Buyer';

        if ($fulfilment->customer_id != $customerId && (!$user || !str_contains($user->email ?? '', 'admin'))) {
            return response()->json(['status' => false, 'message' => 'Unauthorized access to fulfilment receipt.'], 403);
        }

        $updated = $this->fulfilmentService->acknowledgeReceipt(
            $fulfilment,
            $customerId,
            $customerName,
            $request->input('notes')
        );

        return response()->json([
            'status'  => true,
            'message' => 'Receipt acknowledged successfully.',
            'data'    => $updated,
        ]);
    }

    /**
     * POST /api/v2/buyer/fulfilment/{fulfilmentId}/dispute
     * Buyer reports shortage or damage.
     */
    public function reportDispute(Request $request, int $fulfilmentId): JsonResponse
    {
        $validated = $request->validate([
            'dispute_type' => 'required|string|in:shortage_reported,damage_reported,disputed',
            'reason'       => 'required|string|min:5|max:1000',
        ]);

        $fulfilment = SellerFulfilment::findOrFail($fulfilmentId);
        $user = $request->user();
        $customerId = $user ? (int)$user->id : (int)$request->input('customer_id', $fulfilment->customer_id);
        $customerName = $user ? ($user->name ?? $user->email) : 'Buyer';

        if ($fulfilment->customer_id != $customerId && (!$user || !str_contains($user->email ?? '', 'admin'))) {
            return response()->json(['status' => false, 'message' => 'Unauthorized access to fulfilment dispute.'], 403);
        }

        $updated = $this->fulfilmentService->reportDispute(
            $fulfilment,
            $validated['dispute_type'],
            $validated['reason'],
            $customerId,
            $customerName
        );

        return response()->json([
            'status'  => true,
            'message' => 'Issue reported to seller and marketplace operations.',
            'data'    => $updated,
        ]);
    }
}
