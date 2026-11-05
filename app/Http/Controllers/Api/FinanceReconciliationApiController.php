<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transection;
use App\Services\PaymentGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceReconciliationApiController extends Controller
{
    public function __construct(
        protected PaymentGatewayService $paymentService
    ) {
    }

    /**
     * GET /api/v2/finance/payments
     * List transactions with finance reconciliation filters, pagination, and summary metrics.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Transection::query()->with(['order', 'customer']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('gateway')) {
            $query->where('gateway_name', $request->input('gateway'));
        }

        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->input('verification_status'));
        }

        if ($request->filled('reconciliation_status')) {
            $query->where('reconciliation_status', $request->input('reconciliation_status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('payment_ref', 'LIKE', "%{$search}%")
                  ->orWhere('order_no', 'LIKE', "%{$search}%")
                  ->orWhere('gateway_order_id', 'LIKE', "%{$search}%")
                  ->orWhere('gateway_payment_id', 'LIKE', "%{$search}%")
                  ->orWhere('txnid', 'LIKE', "%{$search}%");
            });
        }

        // Summary metrics calculation before pagination
        $statsQuery = clone $query;
        $totalVolume = (clone $statsQuery)->whereIn('status', ['successful', 'success', 'captured'])->sum('amount');
        $totalCount = (clone $statsQuery)->count();
        $matchedCount = (clone $statsQuery)->where('reconciliation_status', PaymentGatewayService::RECON_MATCHED)->count();
        $mismatchCount = (clone $statsQuery)->where('reconciliation_status', PaymentGatewayService::RECON_MISMATCH)->count();
        $pendingCount = (clone $statsQuery)->where('reconciliation_status', PaymentGatewayService::RECON_PENDING)->count();
        $manualReviewCount = (clone $statsQuery)->where('reconciliation_status', PaymentGatewayService::RECON_MANUAL_REVIEW)->count();
        $tamperedCount = (clone $statsQuery)->where('verification_status', PaymentGatewayService::VERIFY_TAMPERED)->count();

        $perPage = (int) $request->input('per_page', 15);
        $transactions = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'status' => true,
            'summary' => [
                'total_volume'        => round((float)$totalVolume, 2),
                'total_count'         => $totalCount,
                'matched_count'       => $matchedCount,
                'mismatch_count'      => $mismatchCount,
                'pending_count'       => $pendingCount,
                'manual_review_count' => $manualReviewCount,
                'tampered_count'      => $tamperedCount,
            ],
            'data' => $transactions->items(),
            'pagination' => [
                'total'        => $transactions->total(),
                'per_page'     => $transactions->perPage(),
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/v2/finance/payments/{id}
     * Retrieve full transaction details, related orders, and audit trail.
     */
    public function show(int $id): JsonResponse
    {
        $transaction = Transection::with([
            'order.customer',
            'order.childOrders',
            'parentOrder',
            'customer',
            'auditLogs' => fn ($q) => $q->orderBy('id', 'desc'),
        ])->findOrFail($id);

        return response()->json([
            'status' => true,
            'data'   => [
                'transaction'  => $transaction,
                'order'        => $transaction->order,
                'child_orders' => $transaction->order?->childOrders ?? [],
                'audit_logs'   => $transaction->auditLogs,
            ],
        ]);
    }

    /**
     * POST /api/v2/finance/payments/{id}/reconcile
     * Finance officer manual reconciliation override.
     */
    public function reconcile(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reconciliation_status' => 'required|string|in:matched,mismatch,manual_review',
            'notes'                 => 'required|string|min:5|max:1000',
        ]);

        $transaction = Transection::findOrFail($id);

        $user = $request->user();
        $userId = $user ? (int)$user->id : 1;
        $userName = $user ? ($user->name ?? $user->email) : 'Finance Admin';

        $updatedTxn = $this->paymentService->manualReconciliation(
            $transaction,
            $validated['reconciliation_status'],
            $validated['notes'],
            $userId,
            $userName
        );

        return response()->json([
            'status'  => true,
            'message' => 'Manual reconciliation status updated successfully.',
            'data'    => $updatedTxn,
        ]);
    }
}

