<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\V2\BaseApiController;
use App\Models\AgentIncentiveLedger;
use App\Models\AgentPayout;
use App\Services\AgentIncentiveCalculationService;
use App\Services\AgentOrderEligibilityService;
use App\Services\AgentPayoutService;
use App\Services\BuyerAttributionService;
use App\Services\FieldAgentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentPortalApiController extends BaseApiController
{
    public function __construct(
        protected FieldAgentService $agentService,
        protected BuyerAttributionService $attributionService,
        protected AgentOrderEligibilityService $eligibilityService,
        protected AgentIncentiveCalculationService $calculationService,
        protected AgentPayoutService $payoutService
    ) {
    }

    /**
     * GET /api/v2/agent/profile
     * Authenticated logged-in Field Sales Agent profile & operational status.
     * Note: Agent identity is resolved strictly from auth session, never from request params.
     */
    public function profile(Request $request): JsonResponse
    {
        $agent = $this->agentService->resolveAgentForUser($request->user());
        if (!$agent) {
            return $this->sendError('Field sales agent profile not found.', [], 403);
        }

        return $this->sendResponse([
            'agent' => $agent->toAgentSelfArray(),
            'policy_notice' => [
                'incentive_funding_model'             => config('marketplace_agents.incentive_funding_model'),
                'eligible_order_tracking_implemented' => (bool)config('marketplace_agents.eligible_order_tracking_implemented', false),
                'message'                             => 'Order eligibility tracking and incentive payout calculation are pending formal business approval (Phase 6.2).',
            ],
        ], 'Agent profile retrieved successfully.');
    }

    /**
     * GET /api/v2/agent/buyers
     * Paginated list of buyers/dealers attributed strictly to the logged-in agent.
     * Never exposes other agents' buyers, confidential seller prices, or internal admin notes.
     */
    public function buyers(Request $request): JsonResponse
    {
        $agent = $this->agentService->resolveAgentForUser($request->user());
        if (!$agent) {
            return $this->sendError('Field sales agent profile not found.', [], 403);
        }

        $paginator = $this->attributionService->getAgentAssignedBuyers(
            $agent,
            $request->only(['status', 'search']),
            (int)$request->get('per_page', 20)
        );

        return response()->json([
            'status'  => true,
            'code'    => 200,
            'message' => 'Assigned buyers retrieved successfully.',
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'has_more'     => $paginator->hasMorePages(),
            ],
        ], 200);
    }

    /**
     * GET /api/v2/agent/buyers/{customerId}
     * Permitted basic information for a single buyer attributed to the logged-in agent.
     * Prevents IDOR and cross-agent access with strict ownership validation.
     */
    public function showBuyer(Request $request, int $customerId): JsonResponse
    {
        $agent = $this->agentService->resolveAgentForUser($request->user());
        if (!$agent) {
            return $this->sendError('Field sales agent profile not found.', [], 403);
        }

        try {
            $buyerData = $this->attributionService->getAssignedBuyerForAgent($agent, $customerId);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendError('Forbidden: You do not have access to this buyer account.', [], 403);
        }

        return $this->sendResponse($buyerData, 'Assigned buyer details retrieved successfully.');
    }

    /**
     * GET /api/v2/agent/incentives
     * Scoped summary & entries strictly for the logged-in Field Sales Agent.
     * Excludes confidential internal admin notes, other agents' data, seller prices,
     * and hides unapproved incentive amounts unless disclosure policy is enabled.
     */
    public function incentives(Request $request): JsonResponse
    {
        $agent = $this->agentService->resolveAgentForUser($request->user());
        if (!$agent) {
            return $this->sendError('Field sales agent profile not found.', [], 403);
        }

        $summary = $this->calculationService->getAgentIncentiveSummary($agent->id);
        $disclosed = (bool)($summary['financials_disclosed'] ?? false);

        $filters = $request->only(['status', 'customer_id', 'date_from', 'date_to', 'search']);
        $filters['field_agent_id'] = $agent->id; // Enforce strict tenant boundary

        $paginator = $this->calculationService->listLedgerEntries($filters, (int)$request->get('per_page', 20));
        $entries = collect($paginator->items())->map(function (AgentIncentiveLedger $entry) use ($disclosed) {
            return [
                'ledger_ref'              => $entry->ledger_ref,
                'entry_type'              => $entry->entry_type,
                'order_no'                => $entry->parentOrder?->orderno,
                'buyer_name'              => $entry->customer?->name,
                'eligible_order_sequence' => $entry->eligible_order_sequence,
                'rule_code'               => $entry->rule_code,
                'rule_version'            => $entry->rule_version,
                'status'                  => $entry->status,
                'calculated_amount'       => $disclosed && in_array($entry->status, [AgentIncentiveLedger::STATUS_APPROVED, AgentIncentiveLedger::STATUS_PAID], true)
                    ? (float)$entry->calculated_incentive_amount
                    : null,
                'payout_reference'        => $entry->payout_reference,
                'created_at'              => $entry->created_at?->toIso8601String(),
            ];
        })->values()->all();

        $trackedOrders = $this->eligibilityService->listEligibleOrders(
            array_merge($request->only(['customer_id', 'eligibility_status', 'search']), ['field_agent_id' => $agent->id]),
            (int)$request->get('per_page', 20)
        );

        return response()->json([
            'status' => true,
            'code'   => 200,
            'message'=> 'Agent incentive summary retrieved successfully.',
            'data'   => [
                'summary'        => $summary,
                'ledger_entries' => $entries,
                'tracked_orders' => collect($trackedOrders->items())->map->toAgentSafeArray()->values()->all(),
            ],
            'meta'   => [
                'current_page' => $trackedOrders->currentPage(),
                'last_page'    => $trackedOrders->lastPage(),
                'total'        => $trackedOrders->total(),
            ],
        ]);
    }

    /**
     * GET /api/v2/agent/payouts
     * Strictly scoped payout history for the logged-in Field Sales Agent (masked destination only).
     */
    public function payouts(Request $request): JsonResponse
    {
        $agent = $this->agentService->resolveAgentForUser($request->user());
        if (!$agent) {
            return $this->sendError('Field sales agent profile not found.', [], 403);
        }

        $filters = array_merge($request->only(['status', 'date_from', 'date_to']), ['field_agent_id' => $agent->id]);
        $paginator = $this->payoutService->listPayouts($filters, (int)$request->get('per_page', 20));
        $summary = $this->calculationService->getAgentIncentiveSummary($agent->id);

        return response()->json([
            'status'  => true,
            'code'    => 200,
            'message' => 'Agent payout history retrieved successfully.',
            'summary' => $summary,
            'data'    => collect($paginator->items())->map(fn(AgentPayout $p) => $p->toAgentSafeArray())->values()->all(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    /**
     * GET /api/v2/agent/payouts/{id}
     * Single payout detail strictly belonging to authenticated Field Agent (403 on cross-agent IDOR).
     */
    public function showPayout(Request $request, int $id): JsonResponse
    {
        $agent = $this->agentService->resolveAgentForUser($request->user());
        if (!$agent) {
            return $this->sendError('Field sales agent profile not found.', [], 403);
        }

        $payout = AgentPayout::with(['items.ledgerEntry.parentOrder:id,orderno'])->findOrFail($id);
        if ((int)$payout->field_agent_id !== (int)$agent->id) {
            return $this->sendError('Forbidden: You cannot view another agent\'s payout record.', [], 403);
        }

        $data = $payout->toAgentSafeArray();
        $data['items'] = $payout->items->map(fn($item) => [
            'ledger_ref'              => $item->ledgerEntry?->ledger_ref,
            'order_no'                => $item->ledgerEntry?->parentOrder?->orderno,
            'eligible_order_sequence' => $item->eligible_order_sequence,
            'item_net_payable_amount' => (float)$item->item_net_payable_amount,
        ])->values()->all();

        return $this->sendResponse($data, 'Agent payout details retrieved.');
    }
}

