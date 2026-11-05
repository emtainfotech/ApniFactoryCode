<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialLedger;
use App\Models\SellerSettlement;
use App\Services\FinancialLedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialSettlementApiController extends Controller
{
    public function __construct(
        protected FinancialLedgerService $ledgerService
    ) {
    }

    /**
     * GET /api/v2/finance/ledger
     * Finance/admin view of all ledger entries with summary metrics.
     */
    public function financeLedger(Request $request): JsonResponse
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

        $totalCredits = (clone $query)->where('direction', FinancialLedger::DIRECTION_CREDIT)->sum('amount');
        $totalDebits  = (clone $query)->where('direction', FinancialLedger::DIRECTION_DEBIT)->sum('amount');

        $entries = $query->orderBy('id', 'desc')->paginate(25);

        return response()->json([
            'status'  => true,
            'summary' => [
                'total_credits' => round((float)$totalCredits, 2),
                'total_debits'  => round((float)$totalDebits, 2),
                'net_flow'      => round((float)($totalCredits - $totalDebits), 2),
            ],
            'data'    => $entries->items(),
            'pagination' => [
                'total'        => $entries->total(),
                'per_page'     => $entries->perPage(),
                'current_page' => $entries->currentPage(),
                'last_page'    => $entries->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/v2/finance/settlements
     * Finance/admin settlement queue.
     */
    public function financeSettlements(Request $request): JsonResponse
    {
        $query = $this->ledgerService->getSettlementsQuery($request->all());

        $totalPayable = (clone $query)->sum('net_seller_payable');
        $totalCommissions = (clone $query)->sum('commission_amount');
        $settlements = $query->paginate(25);

        return response()->json([
            'status'  => true,
            'summary' => [
                'total_payable'     => round((float)$totalPayable, 2),
                'total_commissions' => round((float)$totalCommissions, 2),
            ],
            'data'    => $settlements->items(),
            'pagination' => [
                'total'        => $settlements->total(),
                'per_page'     => $settlements->perPage(),
                'current_page' => $settlements->currentPage(),
                'last_page'    => $settlements->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/v2/finance/settlements/{id}/status
     * Admin update settlement status (hold, release, complete).
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status'           => 'required|string|in:pending,on_hold,eligible,processing,completed,cancelled',
            'payout_reference' => 'nullable|string|max:100',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $settlement = SellerSettlement::findOrFail($id);
        $user = $request->user();
        $adminId = $user ? (int)$user->id : 1;

        $updated = $this->ledgerService->updateSettlementStatus(
            $settlement,
            $validated['status'],
            $validated['payout_reference'] ?? null,
            $adminId,
            $validated['notes'] ?? null
        );

        return response()->json([
            'status'  => true,
            'message' => 'Settlement status updated successfully.',
            'data'    => $updated,
        ]);
    }

    /**
     * GET /api/v2/seller/ledger
     * Multi-tenant isolated ledger for authenticated seller.
     */
    public function sellerLedger(Request $request): JsonResponse
    {
        $user = $request->user();
        $sellerId = $user ? (int)$user->id : (int)$request->input('seller_id', 1);

        $query = $this->ledgerService->getSellerLedgerQuery($sellerId, $request->all());
        $entries = $query->paginate(20);

        $balance = FinancialLedger::where('seller_user_id', $sellerId)
            ->orderBy('id', 'desc')
            ->value('running_balance') ?? 0.00;

        return response()->json([
            'status'  => true,
            'balance' => (float) $balance,
            'data'    => $entries->items(),
            'pagination' => [
                'total'        => $entries->total(),
                'per_page'     => $entries->perPage(),
                'current_page' => $entries->currentPage(),
                'last_page'    => $entries->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/v2/seller/settlements
     * Multi-tenant isolated settlements for authenticated seller.
     */
    public function sellerSettlements(Request $request): JsonResponse
    {
        $user = $request->user();
        $sellerId = $user ? (int)$user->id : (int)$request->input('seller_id', 1);

        $settlements = SellerSettlement::where('seller_user_id', $sellerId)
            ->with('order')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return response()->json([
            'status'     => true,
            'data'       => $settlements->items(),
            'pagination' => [
                'total'        => $settlements->total(),
                'per_page'     => $settlements->perPage(),
                'current_page' => $settlements->currentPage(),
                'last_page'    => $settlements->lastPage(),
            ],
        ]);
    }
}

