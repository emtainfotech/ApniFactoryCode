<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\V2\BaseApiController;
use App\Models\AgentIncentiveAuditLog;
use App\Models\AgentIncentiveLedger;
use App\Models\AgentPayout;
use App\Models\AgentPayoutReconciliationException;
use App\Services\AgentPayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminAgentPayoutApiController extends BaseApiController
{
    public function __construct(
        protected AgentPayoutService $payoutService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['field_agent_id', 'status', 'reconciliation_status', 'territory', 'date_from', 'date_to', 'search']);
        $paginator = $this->payoutService->listPayouts($filters, (int)$request->get('per_page', 20));

        // Transform to ensure encrypted raw destination secrets stay hidden while showing masked destination summary
        $rows = collect($paginator->items())->map(function (AgentPayout $p) {
            $arr = $p->toArray();
            $arr['destination_summary'] = $p->maskedDestinationSummary();
            return $arr;
        })->values()->all();

        return response()->json([
            'status'  => true,
            'code'    => 200,
            'summary' => $this->payoutService->getFinanceDashboardSummary($filters),
            'data'    => $rows,
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $filters = $request->only(['field_agent_id', 'territory', 'date_from', 'date_to']);
        $summary = $this->payoutService->getFinanceDashboardSummary($filters);

        return $this->sendResponse($summary, 'Authoritative finance incentive & payout summary retrieved.');
    }

    public function show(int $id): JsonResponse
    {
        $payout = AgentPayout::with([
            'agent:id,agent_code,name,territory,kyc_status,contract_status,status',
            'items.ledgerEntry.parentOrder:id,orderno',
            'exceptions',
            'initiator:id,name,email',
            'authorizer:id,name,email',
            'reconciler:id,name,email',
        ])->findOrFail($id);

        $data = $payout->toArray();
        $data['destination_summary'] = $payout->maskedDestinationSummary();
        $data['admin_notes'] = $payout->admin_notes; // explicitly permitted for Admin
        $data['audit_logs'] = AgentIncentiveAuditLog::where('entity_type', 'payout')
            ->where('entity_id', $payout->id)
            ->orderByDesc('id')
            ->get();

        return $this->sendResponse($data, 'Agent payout details retrieved.');
    }

    /**
     * POST /api/v2/admin/payouts/readiness
     * Validate Stage 3 payout readiness on selected incentive ledger IDs.
     */
    public function checkReadiness(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ledger_ids'           => 'required|array|min:1',
            'ledger_ids.*'         => 'integer|exists:agent_incentive_ledgers,id',
            'withholding_tax_rate' => 'nullable|numeric',
        ]);

        $requestedRate = isset($validated['withholding_tax_rate']) ? (float)$validated['withholding_tax_rate'] : null;
        $entries = AgentIncentiveLedger::whereIn('id', array_unique($validated['ledger_ids']))->get();

        $results = [];
        $allReady = true;

        foreach ($entries as $entry) {
            $check = $this->payoutService->checkLedgerEntryReadiness($entry, $requestedRate);
            if (!$check['ready']) {
                $allReady = false;
            }
            $results[] = array_merge(['ledger_id' => $entry->id], $check);
        }

        return $this->sendResponse([
            'all_ready' => $allReady,
            'items'     => $results,
        ], $allReady ? 'All selected incentives are payout-ready.' : 'One or more entries failed payout readiness checks.');
    }

    /**
     * POST /api/v2/admin/payouts
     * Assemble a payout batch/record (enters draft_awaiting_policy if execution is disabled).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'field_agent_id'       => 'required|integer|exists:field_agents,id',
            'ledger_ids'           => 'required|array|min:1',
            'ledger_ids.*'         => 'integer|exists:agent_incentive_ledgers,id',
            'payment_method'       => 'nullable|string|in:manual_bank_transfer,manual_upi,approved_bank_api,unconfigured_awaiting_policy',
            'withholding_tax_rate' => 'nullable|numeric',
            'idempotency_key'      => 'nullable|string|max:120',
            'admin_notes'          => 'nullable|string|max:1000',
        ]);

        try {
            $payout = $this->payoutService->createPayoutBatch(
                (int)$validated['field_agent_id'],
                $validated['ledger_ids'],
                $request->user(),
                $validated
            );

            $data = $payout->toArray();
            $data['destination_summary'] = $payout->maskedDestinationSummary();

            return $this->sendResponse($data, "Payout batch {$payout->payout_ref} created cleanly.", 201);
        } catch (ValidationException $e) {
            return $this->sendError('Payout creation rejected.', $e->errors(), 422);
        }
    }

    public function authorizeBatch(Request $request, int $id): JsonResponse
    {
        $payout = AgentPayout::findOrFail($id);
        try {
            $updated = $this->payoutService->authorizePayout($payout, $request->user(), $request->input('notes'));
            return $this->sendResponse($updated, 'Payout batch authorized.');
        } catch (ValidationException $e) {
            return $this->sendError('Payout authorization blocked.', $e->errors(), 422);
        }
    }

    public function initiate(Request $request, int $id): JsonResponse
    {
        $payout = AgentPayout::findOrFail($id);
        try {
            $updated = $this->payoutService->recordPaymentInitiation($payout, $request->user(), $request->all());
            return $this->sendResponse($updated, 'Payout initiation recorded (pending external confirmation).');
        } catch (ValidationException $e) {
            return $this->sendError('Payout initiation blocked.', $e->errors(), 422);
        }
    }

    public function confirm(Request $request, int $id): JsonResponse
    {
        $payout = AgentPayout::findOrFail($id);
        try {
            $updated = $this->payoutService->confirmPayoutPayment($payout, $request->all(), $request->user());
            return $this->sendResponse($updated, 'Payout confirmed and reconciled cleanly.');
        } catch (ValidationException $e) {
            return $this->sendError('Payout confirmation rejected.', $e->errors(), 422);
        }
    }

    public function fail(Request $request, int $id): JsonResponse
    {
        $payout = AgentPayout::findOrFail($id);
        $validated = $request->validate([
            'failure_reason'           => 'required|string|max:500',
            'external_transaction_ref' => 'nullable|string|max:120',
        ]);

        try {
            $updated = $this->payoutService->recordPayoutFailure(
                $payout,
                $request->user(),
                $validated['failure_reason'],
                $validated['external_transaction_ref'] ?? null
            );
            return $this->sendResponse($updated, 'Payout failure recorded and exception logged for reconciliation.');
        } catch (ValidationException $e) {
            return $this->sendError('Cannot mark payout failed.', $e->errors(), 422);
        }
    }

    public function exceptions(Request $request): JsonResponse
    {
        $query = AgentPayoutReconciliationException::with([
            'payout:id,payout_ref,status,net_payable_amount',
            'ledgerEntry:id,ledger_ref,calculated_incentive_amount,status',
            'agent:id,agent_code,name',
        ])->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('exception_type')) {
            $query->where('exception_type', $request->input('exception_type'));
        }

        $paginator = $query->paginate(min(max(1, (int)$request->get('per_page', 20)), 100));

        return response()->json([
            'status' => true,
            'code'   => 200,
            'data'   => $paginator->items(),
            'meta'   => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function recordUnmatchedRef(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'external_reference' => 'required|string|max:120',
            'reported_amount'    => 'required|numeric|min:0',
            'notes'              => 'nullable|string|max:1000',
            'field_agent_id'     => 'nullable|integer|exists:field_agents,id',
        ]);

        $ex = $this->payoutService->recordUnmatchedExternalReference(
            $validated['external_reference'],
            (float)$validated['reported_amount'],
            $request->user(),
            $validated['notes'] ?? null,
            isset($validated['field_agent_id']) ? (int)$validated['field_agent_id'] : null
        );

        return $this->sendResponse($ex, 'Unmatched external reference recorded for finance reconciliation.', 201);
    }

    public function resolveException(Request $request, int $id): JsonResponse
    {
        $exception = AgentPayoutReconciliationException::findOrFail($id);
        $validated = $request->validate([
            'status'           => 'required|string|in:under_review,resolved,dismissed',
            'resolution_notes' => 'required|string|max:1000',
        ]);

        $updated = $this->payoutService->resolveException(
            $exception,
            $validated['status'],
            $validated['resolution_notes'],
            $request->user()
        );

        return $this->sendResponse($updated, 'Reconciliation exception updated cleanly.');
    }
}

