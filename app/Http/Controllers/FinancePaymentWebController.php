<?php

namespace App\Http\Controllers;

use App\Models\Transection;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancePaymentWebController extends Controller
{
    public function __construct(
        protected PaymentGatewayService $paymentService
    ) {
    }

    /**
     * Display finance payment reconciliation dashboard.
     */
    public function index(Request $request)
    {
        $query = Transection::query()->with(['order.customer', 'customer']);

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

        $statsQuery = clone $query;
        $metrics = [
            'total_volume'        => (clone $statsQuery)->whereIn('status', ['successful', 'success', 'captured'])->sum('amount'),
            'total_count'         => (clone $statsQuery)->count(),
            'matched_count'       => (clone $statsQuery)->where('reconciliation_status', PaymentGatewayService::RECON_MATCHED)->count(),
            'mismatch_count'      => (clone $statsQuery)->where('reconciliation_status', PaymentGatewayService::RECON_MISMATCH)->count(),
            'pending_count'       => (clone $statsQuery)->where('reconciliation_status', PaymentGatewayService::RECON_PENDING)->count(),
            'manual_review_count' => (clone $statsQuery)->where('reconciliation_status', PaymentGatewayService::RECON_MANUAL_REVIEW)->count(),
            'tampered_count'      => (clone $statsQuery)->where('verification_status', PaymentGatewayService::VERIFY_TAMPERED)->count(),
        ];

        $transactions = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        return view('admin.finance.payments.index', compact('transactions', 'metrics'));
    }

    /**
     * Show detailed transaction and audit trail.
     */
    public function show(int $id)
    {
        $transaction = Transection::with([
            'order.customer',
            'order.childOrders',
            'customer',
            'auditLogs' => fn ($q) => $q->orderBy('id', 'desc'),
        ])->findOrFail($id);

        return view('admin.finance.payments.show', compact('transaction'));
    }

    /**
     * Perform manual reconciliation override.
     */
    public function reconcile(Request $request, int $id)
    {
        $request->validate([
            'reconciliation_status' => 'required|string|in:matched,mismatch,manual_review',
            'notes'                 => 'required|string|min:5|max:1000',
        ]);

        $transaction = Transection::findOrFail($id);
        $user = Auth::user();
        $userId = $user ? (int)$user->id : 1;
        $userName = $user ? ($user->name ?? $user->email) : 'Finance Admin';

        $this->paymentService->manualReconciliation(
            $transaction,
            $request->input('reconciliation_status'),
            $request->input('notes'),
            $userId,
            $userName
        );

        return redirect()->back()->with('success', "Payment {$transaction->payment_ref} reconciliation updated to {$request->input('reconciliation_status')}.");
    }
}

