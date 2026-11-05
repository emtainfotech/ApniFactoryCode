<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SellerFulfilment;
use App\Services\SellerFulfilmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerFulfilmentApiController extends Controller
{
    public function __construct(
        protected SellerFulfilmentService $fulfilmentService
    ) {
    }

    /**
     * Authorize seller access to the order.
     */
    protected function resolveAndAuthorizeSeller(Request $request, Order $order): array
    {
        $user = $request->user();
        $sellerId = $user ? (int)$user->id : ($request->has('seller_id') ? (int)$request->input('seller_id') : (int)$order->user_id);
        $isAdmin = $user && str_contains($user->email ?? '', 'admin');

        if (!$isAdmin && (int)$order->user_id !== $sellerId) {
            abort(response()->json([
                'status'  => false,
                'message' => 'Unauthorized. You can only manage dispatch details for your own seller orders.',
            ], 403));
        }

        $actorName = $user ? ($user->name ?? $user->email) : 'Seller';
        return [$sellerId, $actorName];
    }

    /**
     * GET /api/v2/seller/fulfilment/{orderId}
     * Retrieve seller fulfilment & dispatch record.
     */
    public function show(Request $request, int $orderId): JsonResponse
    {
        $order = Order::findOrFail($orderId);
        $this->resolveAndAuthorizeSeller($request, $order);

        $fulfilment = $this->fulfilmentService->getOrCreateFulfilment($order);

        return response()->json([
            'status' => true,
            'data'   => $fulfilment->load(['history', 'order']),
        ]);
    }

    /**
     * POST /api/v2/seller/fulfilment/{orderId}/origin
     * Update dispatch origin location (Factory/Warehouse).
     */
    public function updateOrigin(Request $request, int $orderId): JsonResponse
    {
        $validated = $request->validate([
            'type'    => 'nullable|string|in:factory,godown,warehouse',
            'name'    => 'required|string|max:255',
            'address' => 'required|string',
            'city'    => 'nullable|string|max:100',
            'state'   => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:16',
            'contact' => 'nullable|string|max:32',
        ]);

        $order = Order::findOrFail($orderId);
        [$sellerId, $actorName] = $this->resolveAndAuthorizeSeller($request, $order);

        $fulfilment = $this->fulfilmentService->getOrCreateFulfilment($order);
        $updated = $this->fulfilmentService->updateDispatchOrigin($fulfilment, $validated, $sellerId, $actorName);

        return response()->json([
            'status'  => true,
            'message' => 'Dispatch origin updated successfully.',
            'data'    => $updated,
        ]);
    }

    /**
     * POST /api/v2/seller/fulfilment/{orderId}/handover
     * Update delivery / hand-over point (Transport Nagar / Godown).
     */
    public function updateHandover(Request $request, int $orderId): JsonResponse
    {
        $validated = $request->validate([
            'type'         => 'nullable|string|in:transport_nagar,buyer_godown,factory_gate',
            'name'         => 'required|string|max:255',
            'address'      => 'required|string',
            'city'         => 'nullable|string|max:100',
            'state'        => 'nullable|string|max:100',
            'pincode'      => 'nullable|string|max:16',
            'instructions' => 'nullable|string|max:1000',
        ]);

        $order = Order::findOrFail($orderId);
        $user = $request->user();
        $actorId = $user ? (int)$user->id : (int)$request->input('actor_id', $order->user_id);
        $actorRole = ($user && str_contains($user->email ?? '', 'admin')) ? 'admin' : 'seller';
        $actorName = $user ? ($user->name ?? $user->email) : 'User';

        if ($actorRole === 'seller' && (int)$order->user_id !== $actorId) {
            return response()->json(['status' => false, 'message' => 'Unauthorized seller access.'], 403);
        }

        $fulfilment = $this->fulfilmentService->getOrCreateFulfilment($order);
        $updated = $this->fulfilmentService->updateDeliveryHandoverPoint($fulfilment, $validated, $actorId, $actorRole, $actorName);

        return response()->json([
            'status'  => true,
            'message' => 'Delivery / Hand-over point updated successfully.',
            'data'    => $updated,
        ]);
    }

    /**
     * POST /api/v2/seller/fulfilment/{orderId}/transporter
     * Save transporter details.
     */
    public function updateTransporter(Request $request, int $orderId): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'contact'     => 'nullable|string|max:64',
            'tracking_no' => 'nullable|string|max:100',
        ]);

        $order = Order::findOrFail($orderId);
        [$sellerId, $actorName] = $this->resolveAndAuthorizeSeller($request, $order);

        $fulfilment = $this->fulfilmentService->getOrCreateFulfilment($order);
        $updated = $this->fulfilmentService->updateTransporterDetails($fulfilment, $validated, $sellerId, $actorName);

        return response()->json([
            'status'  => true,
            'message' => 'Transporter details saved successfully.',
            'data'    => $updated,
        ]);
    }

    /**
     * POST /api/v2/seller/fulfilment/{orderId}/upload-lr
     * Upload scan/photo of LR / Bilty document.
     * Note: LR upload does NOT mark the order delivered.
     */
    public function uploadLr(Request $request, int $orderId): JsonResponse
    {
        $request->validate([
            'lr_document' => 'required|file|mimes:jpeg,jpg,png,webp,pdf|max:5120',
            'lr_number'   => 'nullable|string|max:100',
        ]);

        $order = Order::findOrFail($orderId);
        [$sellerId, $actorName] = $this->resolveAndAuthorizeSeller($request, $order);

        $fulfilment = $this->fulfilmentService->getOrCreateFulfilment($order);
        $updated = $this->fulfilmentService->uploadLrDocument(
            $fulfilment,
            $request->file('lr_document'),
            $request->input('lr_number'),
            $sellerId,
            $actorName
        );

        return response()->json([
            'status'  => true,
            'message' => 'LR / Bilty document uploaded successfully.',
            'data'    => $updated,
        ]);
    }

    /**
     * POST /api/v2/seller/fulfilment/{orderId}/dispatch
     * Confirm dispatch and transition fulfilment status to 'dispatched'.
     */
    public function dispatchOrder(Request $request, int $orderId): JsonResponse
    {
        $validated = $request->validate([
            'transporter_name'        => 'required|string|max:255',
            'transporter_contact'     => 'nullable|string|max:64',
            'transporter_tracking_no' => 'nullable|string|max:100',
            'lr_number'               => 'nullable|string|max:100',
            'dispatched_at'           => 'nullable|date',
            'package_count'           => 'required|integer|min:1',
            'weight_volume'           => 'nullable|string|max:100',
            'vehicle_number'          => 'nullable|string|max:50',
            'estimated_delivery_date' => 'nullable|date|after_or_equal:today',
        ]);

        $order = Order::findOrFail($orderId);
        [$sellerId, $actorName] = $this->resolveAndAuthorizeSeller($request, $order);

        $fulfilment = $this->fulfilmentService->getOrCreateFulfilment($order);
        $updated = $this->fulfilmentService->recordDispatch($fulfilment, $validated, $sellerId, $actorName);

        return response()->json([
            'status'  => true,
            'message' => 'Order marked as dispatched. Buyer notified.',
            'data'    => $updated,
        ]);
    }

    /**
     * POST /api/v2/seller/fulfilment/{orderId}/status
     * Update permitted processing status (e.g. accepted, processing_packed).
     */
    public function updateStatus(Request $request, int $orderId): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:accepted,processing_packed,dispatched,rejected,cancelled',
            'notes'  => 'nullable|string|max:1000',
        ]);

        $order = Order::findOrFail($orderId);
        [$sellerId, $actorName] = $this->resolveAndAuthorizeSeller($request, $order);

        $fulfilment = $this->fulfilmentService->getOrCreateFulfilment($order);
        $updated = $this->fulfilmentService->updateStatus(
            $fulfilment,
            $validated['status'],
            $sellerId,
            'seller',
            $actorName,
            $validated['notes'] ?? null
        );

        return response()->json([
            'status'  => true,
            'message' => "Order status updated to '{$validated['status']}'.",
            'data'    => $updated,
        ]);
    }
}
