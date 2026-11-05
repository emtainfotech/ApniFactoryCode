<?php

namespace App\Http\Controllers;

use App\Models\FinancialLedger;
use App\Models\SellerSettlement;
use App\Services\FinancialLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialSettlementWebController extends Controller
{
    public function __construct(
        protected FinancialLedgerService $ledgerService
    ) {
    }

    /**
     * Admin view of seller settlements and payout queue.
     */
    public function settlementsIndex(Request $request)
    {
        $query = $this->ledgerService->getSettlementsQuery($request->all());

        $metrics = [
            'total_payable'     => (clone $query)->sum('net_seller_payable'),
            'total_commissions' => (clone $query)->sum('commission_amount'),
            'pending_count'     => SellerSettlement::where('settlement_status', SellerSettlement::STATUS_PENDING)->count(),
            'on_hold_count'     => SellerSettlement::where('settlement_status', SellerSettlement::STATUS_ON_HOLD)->count(),
            'eligible_count'    => SellerSettlement::where('settlement_status', SellerSettlement::STATUS_ELIGIBLE)->count(),
            'completed_count'   => SellerSettlement::where('settlement_status', SellerSettlement::STATUS_COMPLETED)->count(),
        ];

        $settlements = $query->paginate(20)->withQueryString();

        return view('admin.finance.settlements.index', compact('settlements', 'metrics'));
    }

    /**
     * Admin update settlement status.
     */
    public function updateSettlementStatus(Request $request, int $id)
    {
        $request->validate([
            'status'           => 'required|string|in:pending,on_hold,eligible,processing,completed,cancelled',
            'payout_reference' => 'nullable|string|max:100',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $settlement = SellerSettlement::findOrFail($id);
        $user = Auth::user();
        $adminId = $user ? (int)$user->id : 1;

        $this->ledgerService->updateSettlementStatus(
            $settlement,
            $request->input('status'),
            $request->input('payout_reference'),
            $adminId,
            $request->input('notes')
        );

        return redirect()->back()->with('success', "Settlement #{$settlement->settlement_ref} updated to {$request->input('status')}.");
    }

    /**
     * Admin view of double-entry financial ledger.
     */
    public function ledgerIndex(Request $request)
    {
        $query = FinancialLedger::query()->with(['seller', 'company', 'order']);

        if ($request->filled('seller_user_id')) {
            $query->where('seller_user_id', $request->input('seller_user_id'));
        }
        if ($request->filled('entry_type')) {
            $query->where('entry_type', $request->input('entry_type'));
        }
        if ($request->filled('direction')) {
            $query->where('direction', $request->input('direction'));
        }

        $metrics = [
            'total_credits' => (clone $query)->where('direction', FinancialLedger::DIRECTION_CREDIT)->sum('amount'),
            'total_debits'  => (clone $query)->where('direction', FinancialLedger::DIRECTION_DEBIT)->sum('amount'),
            'entry_count'   => (clone $query)->count(),
        ];

        $entries = $query->orderBy('id', 'desc')->paginate(25)->withQueryString();

        return view('admin.finance.ledger.index', compact('entries', 'metrics'));
    }

    /**
     * Seller-isolated settlements view for seller portal.
     */
    public function sellerSettlements(Request $request)
    {
        $user = Auth::user();
        $sellerId = (int) $user->id;

        $settlements = SellerSettlement::where('seller_user_id', $sellerId)
            ->with('order')
            ->orderBy('id', 'desc')
            ->paginate(20);

        $totalPayable = SellerSettlement::where('seller_user_id', $sellerId)->sum('net_seller_payable');
        $pendingPayable = SellerSettlement::where('seller_user_id', $sellerId)
            ->whereIn('settlement_status', ['pending', 'on_hold', 'eligible'])
            ->sum('net_seller_payable');

        return view('seller.finance.settlements', compact('settlements', 'totalPayable', 'pendingPayable'));
    }
}

