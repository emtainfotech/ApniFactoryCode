<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderTaxSnapshot;
use App\Models\TaxMaster;
use App\Services\TaxCalculationService;
use App\Services\TaxMasterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxMasterApiController extends Controller
{
    public function __construct(
        protected TaxMasterService $taxMasterService,
        protected TaxCalculationService $taxCalculationService
    ) {
    }

    /**
     * GET /api/v2/tax/masters
     * Query tax master classification records.
     */
    public function index(Request $request): JsonResponse
    {
        $query = TaxMaster::query();

        if ($request->filled('hsn_code')) {
            $query->where('hsn_code', 'LIKE', '%' . $request->input('hsn_code') . '%');
        }

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->input('approval_status'));
        } elseif (!$request->user() || !str_contains($request->user()->email, 'admin')) {
            // Non-admin users only see approved records
            $query->approved();
        }

        if ($request->filled('date')) {
            $query->effectiveOn($request->input('date'));
        }

        $records = $query->orderBy('hsn_code')->orderBy('version', 'desc')->paginate(25);

        return response()->json([
            'status'     => true,
            'data'       => $records->items(),
            'pagination' => [
                'total'        => $records->total(),
                'per_page'     => $records->perPage(),
                'current_page' => $records->currentPage(),
                'last_page'    => $records->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/v2/tax/masters
     * Create or request a new tax classification record.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hsn_code'         => 'required|string|max:16',
            'description'      => 'required|string|max:255',
            'gst_rate'         => 'required|numeric|min:0|max:100',
            'type'             => 'nullable|string|in:goods,services',
            'effective_from'   => 'required|date',
            'effective_to'     => 'nullable|date|after:effective_from',
            'source_reference' => 'nullable|string|max:255',
            'approval_status'  => 'nullable|string|in:pending,approved',
        ]);

        $user = $request->user();
        $userId = $user ? (int)$user->id : 1;
        $role = ($user && str_contains($user->email, 'admin')) ? 'admin' : 'seller';
        $actorName = $user ? ($user->name ?? $user->email) : 'System';

        $record = $this->taxMasterService->createRecord($validated, $userId, $role, $actorName);

        return response()->json([
            'status'  => true,
            'message' => $record->isApproved() ? 'Tax master record approved and activated.' : 'Tax classification submitted for admin review.',
            'data'    => $record,
        ], 201);
    }

    /**
     * GET /api/v2/tax/masters/{id}
     * Retrieve tax master record details with audit logs.
     */
    public function show(int $id): JsonResponse
    {
        $record = TaxMaster::with(['creator', 'approver', 'auditLogs'])->findOrFail($id);

        return response()->json([
            'status' => true,
            'data'   => $record,
        ]);
    }

    /**
     * POST /api/v2/tax/masters/{id}/approve
     * Admin approval of pending tax record.
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if ($user && !str_contains($user->email, 'admin')) {
            return response()->json(['status' => false, 'message' => 'Unauthorized. Admin privileges required.'], 403);
        }

        $record = TaxMaster::findOrFail($id);
        $adminId = $user ? (int)$user->id : 1;
        $actorName = $user ? ($user->name ?? $user->email) : 'Admin';

        $approved = $this->taxMasterService->approveRecord($record, $adminId, $actorName, $request->input('notes'));

        return response()->json([
            'status'  => true,
            'message' => 'Tax classification approved successfully.',
            'data'    => $approved,
        ]);
    }

    /**
     * POST /api/v2/tax/masters/{id}/reject
     * Admin rejection of tax classification.
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if ($user && !str_contains($user->email, 'admin')) {
            return response()->json(['status' => false, 'message' => 'Unauthorized. Admin privileges required.'], 403);
        }

        $request->validate(['reason' => 'required|string|min:3']);
        $record = TaxMaster::findOrFail($id);
        $adminId = $user ? (int)$user->id : 1;
        $actorName = $user ? ($user->name ?? $user->email) : 'Admin';

        $rejected = $this->taxMasterService->rejectRecord($record, $request->input('reason'), $adminId, $actorName);

        return response()->json([
            'status'  => true,
            'message' => 'Tax classification rejected.',
            'data'    => $rejected,
        ]);
    }

    /**
     * POST /api/v2/tax/calculate
     * Preview calculation breakdown for given items, seller state, and buyer state.
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'seller_state'     => 'required|string|max:100',
            'buyer_state'      => 'required|string|max:100',
            'transaction_date' => 'nullable|date',
            'lines'            => 'required|array|min:1',
            'lines.*.hsn_code' => 'required|string',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.quantity' => 'required|integer|min:1',
            'lines.*.discount_amount' => 'nullable|numeric|min:0',
        ]);

        $calculation = $this->taxCalculationService->calculateOrderTax(
            $validated['lines'],
            $validated['seller_state'],
            $validated['buyer_state'],
            $validated['transaction_date'] ?? null
        );

        return response()->json([
            'status' => true,
            'data'   => $calculation,
        ]);
    }

    /**
     * GET /api/v2/tax/orders/{orderId}/snapshots
     * Retrieve immutable order tax snapshots.
     */
    public function orderSnapshots(int $orderId): JsonResponse
    {
        $snapshots = OrderTaxSnapshot::where('order_id', $orderId)->get();

        return response()->json([
            'status' => true,
            'data'   => $snapshots,
        ]);
    }
}

