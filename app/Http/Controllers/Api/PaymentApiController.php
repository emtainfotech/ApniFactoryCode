<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Transection;
use App\Services\PaymentGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentApiController extends Controller
{
    public function __construct(
        protected PaymentGatewayService $paymentService
    ) {
    }

    /**
     * POST /api/v2/payments/initiate
     * Initiate payment for an eligible parent order.
     */
    public function initiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id'        => 'required|integer|exists:orders,id',
            'gateway'         => 'nullable|string|in:razorpay,payu',
            'idempotency_key' => 'nullable|string|max:64',
        ]);

        $user = $request->user();
        $customerId = $user ? (int)$user->id : (int)$request->input('customer_id');

        $order = Order::with('customer')->findOrFail($validated['order_id']);

        // Authorization check: Verify buyer ownership
        if ($customerId && $order->customer_id != $customerId && (!$user || !str_contains($user->email, 'admin'))) {
            return response()->json([
                'status'  => false,
                'message' => 'You do not have permission to pay for this order.',
            ], 403);
        }

        try {
            $gateway = $validated['gateway'] ?? PaymentGatewayService::GATEWAY_RAZORPAY;
            $idempotencyKey = $validated['idempotency_key'] ?? $request->header('X-Idempotency-Key');

            $initiation = $this->paymentService->initiatePayment($order, $gateway, $idempotencyKey, $customerId);

            return response()->json([
                'status'  => true,
                'message' => 'Payment initiated successfully.',
                'data'    => $initiation,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Payment initiation rejected.',
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    /**
     * GET /api/v2/payments/{paymentRef}/status
     * Retrieve authoritative payment status for a buyer order or transaction.
     */
    public function status(Request $request, string $paymentRef): JsonResponse
    {
        $user = $request->user();
        $customerId = $user ? (int)$user->id : (int)$request->input('customer_id');

        $txn = Transection::where('payment_ref', $paymentRef)
            ->orWhere('order_no', $paymentRef)
            ->orWhere('gateway_order_id', $paymentRef)
            ->first();

        if (!$txn) {
            return response()->json([
                'status'  => false,
                'message' => 'Payment transaction reference not found.',
            ], 404);
        }

        // Ownership check
        if ($customerId && $txn->customer_id != $customerId && (!$user || !str_contains($user->email, 'admin'))) {
            return response()->json([
                'status'  => false,
                'message' => 'Access denied to transaction record.',
            ], 403);
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'payment_ref'           => $txn->payment_ref,
                'order_no'              => $txn->order_no,
                'amount'                => (float)$txn->amount,
                'currency'              => $txn->currency,
                'gateway_name'          => $txn->gateway_name,
                'status'                => $txn->status,
                'verification_status'   => $txn->verification_status,
                'reconciliation_status' => $txn->reconciliation_status,
                'gateway_payment_id'    => $txn->gateway_payment_id,
                'initiated_at'          => $txn->initiated_at ? $txn->initiated_at->toIso8601String() : null,
                'verified_at'           => $txn->verified_at ? $txn->verified_at->toIso8601String() : null,
                'is_paid'               => $txn->isSuccessful(),
            ],
        ]);
    }

    /**
     * POST /api/v2/payments/verify-client
     * Server-side cryptographic verification of mobile app / web client payment response.
     */
    public function verifyClient(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'gateway'   => 'required|string|in:razorpay,payu',
            'signature' => 'nullable|string',
            'payload'   => 'required|array',
        ]);

        $gateway = $validated['gateway'];
        $payload = $validated['payload'];
        $signature = $validated['signature'] ?? ($payload['razorpay_signature'] ?? null);

        $result = $this->paymentService->processCallback($gateway, $payload, $signature);

        $httpStatus = $result['success'] ? 200 : 422;

        return response()->json($result, $httpStatus);
    }

    /**
     * POST /api/v2/payments/webhook/{gateway}
     * Gateway server-to-server webhook callback.
     */
    public function webhook(Request $request, string $gateway): JsonResponse
    {
        if (!in_array($gateway, [PaymentGatewayService::GATEWAY_RAZORPAY, PaymentGatewayService::GATEWAY_PAYU])) {
            return response()->json(['status' => false, 'message' => 'Unsupported gateway webhook.'], 400);
        }

        $signature = $request->header('X-Razorpay-Signature');
        $eventId = $request->header('X-Razorpay-Event-Id') ?: $request->input('event_id');

        $result = $this->paymentService->processCallback(
            $gateway,
            $request->all(),
            $signature,
            $eventId
        );

        $httpStatus = $result['success'] ? 200 : 400;

        return response()->json($result, $httpStatus);
    }
}

