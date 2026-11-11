<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\V2\BaseApiController;
use App\Models\AgentEligibleOrder;
use App\Models\AgentIncentiveAuditLog;
use App\Models\AgentIncentiveLedger;
use App\Models\AgentIncentiveProcessingLog;
use App\Models\AgentIncentiveRule;
use App\Models\Order;
use App\Services\AgentIncentiveCalculationService;
use App\Services\AgentIncentiveRuleService;
use App\Services\AgentOrderEligibilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminAgentIncentiveApiController extends BaseApiController
{
    public function __construct(
        protected AgentOrderEligibilityService $eligibilityService,
        protected AgentIncentiveRuleService $ruleService,
        protected AgentIncentiveCalculationService $calculationService
    ) {
    }

    // -------------------------------------------------------------------------
    // ELIGIBLE ORDERS TRACKING
    // -------------------------------------------------------------------------

    public function eligibleOrders(Request $request): JsonResponse
    {
        $paginator = $this->eligibilityService->listEligibleOrders(
            $request->only(['field_agent_id', 'customer_id', 'eligibility_status', 'territory', 'date_from', 'date_to', 'search']),
            (int)$request->get('per_page', 20)
        );

        return response()->json([
            'status'  => true,
            'code'    => 200,
            'message' => 'Eligible orders retrieved successfully.',
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function showEligibleOrder(int $id): JsonResponse
    {
        $record = AgentEligibleOrder::with([
            'customer:id,name,mobile,email,location',
            'agent:id,agent_code,name,territory,status',
            'attribution:id,attribution_ref,status,approval_status,attributed_at',
            'parentOrder:id,orderno,grandtotal,netamount,taxamount,payment_status,paid_at,created_at',
            'ledgerEntries.rule:id,rule_code,version,name',
        ])->findOrFail($id);

        $data = $record->toArray();
        $data['admin_notes'] = $record->admin_notes; // explicitly visible to admin
        $data['audit_logs'] = AgentIncentiveAuditLog::where('entity_type', 'eligible_order')
            ->where('entity_id', $record->id)
            ->orderByDesc('id')
            ->get();

        return $this->sendResponse($data, 'Eligible order details retrieved successfully.');
    }

    public function buyerProgress(int $customerId): JsonResponse
    {
        $progress = $this->eligibilityService->getBuyerEligibilityProgress($customerId);

        return $this->sendResponse($progress, 'Buyer eligible-order progress retrieved successfully.');
    }

    public function evaluateOrder(Request $request, int $orderId): JsonResponse
    {
        $order = Order::findOrFail($orderId);
        $eventType = $request->input('event_type', 'admin_manual_evaluation');
        $eventRef = $request->input('event_ref', 'admin_' . ($request->user()?->id ?? 1) . '_' . now()->format('YmdHisv'));

        $tracked = $this->eligibilityService->handleOrderLifecycleEvent($order, $eventType, $eventRef, [
            'triggered_by_user_id' => $request->user()?->id,
        ]);

        if (!$tracked) {
            return $this->sendResponse([
                'tracked' => false,
                'reason'  => 'Order buyer does not have an approved active field sales agent attribution.',
            ], 'Order evaluated cleanly (no attributed active agent).');
        }

        return $this->sendResponse($tracked->load(['earnedLedgerEntry']), 'Order eligibility evaluated successfully.');
    }

    public function retryFailure(Request $request, int $logId): JsonResponse
    {
        try {
            $result = $this->eligibilityService->retryFailedEvent($logId, $request->user());
            return $this->sendResponse($result, 'Failed event re-processed cleanly.');
        } catch (ValidationException $e) {
            return $this->sendError('Retry failed.', $e->errors(), 422);
        }
    }

    // -------------------------------------------------------------------------
    // INCENTIVE RULES ENGINE
    // -------------------------------------------------------------------------

    public function rules(Request $request): JsonResponse
    {
        $paginator = $this->ruleService->listRules(
            $request->only(['status', 'calculation_method', 'search']),
            (int)$request->get('per_page', 20)
        );

        return response()->json([
            'status' => true,
            'code'   => 200,
            'policy_status' => [
                'funding_policy_approved'      => (bool)config('marketplace_agents.funding_policy_approved', false),
                'incentive_calculation_enabled'=> (bool)config('marketplace_agents.incentive_calculation_enabled', false),
                'incentive_funding_model'      => config('marketplace_agents.incentive_funding_model'),
                'seller_deductions_prohibited' => true,
            ],
            'data'   => $paginator->items(),
            'meta'   => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function storeRule(Request $request): JsonResponse
    {
        try {
            $rule = $this->ruleService->createRule($request->all(), $request->user());
            return $this->sendResponse($rule, 'Draft incentive rule created successfully.', 201);
        } catch (ValidationException $e) {
            return $this->sendError('Invalid incentive rule configuration.', $e->errors(), 422);
        }
    }

    public function showRule(int $id): JsonResponse
    {
        $rule = AgentIncentiveRule::with(['creator:id,name,email', 'businessApprover:id,name,email', 'financeApprover:id,name,email', 'parentRule:id,rule_code,version'])->findOrFail($id);
        $data = $rule->toArray();
        $data['audit_logs'] = AgentIncentiveAuditLog::where('entity_type', 'rule')->where('entity_id', $rule->id)->orderByDesc('id')->get();

        return $this->sendResponse($data, 'Incentive rule details retrieved.');
    }

    public function createRuleVersion(Request $request, int $id): JsonResponse
    {
        $baseRule = AgentIncentiveRule::findOrFail($id);
        try {
            $newVer = $this->ruleService->createNewVersion($baseRule, $request->all(), $request->user());
            return $this->sendResponse($newVer, "Created rule {$newVer->rule_code} v{$newVer->version}.", 201);
        } catch (ValidationException $e) {
            return $this->sendError('Rule versioning failed.', $e->errors(), 422);
        }
    }

    public function approveRuleBusiness(Request $request, int $id): JsonResponse
    {
        $rule = AgentIncentiveRule::findOrFail($id);
        try {
            $updated = $this->ruleService->recordBusinessApproval(
                $rule,
                $request->user(),
                (string)$request->input('business_approval_ref', ''),
                $request->input('reason')
            );
            return $this->sendResponse($updated, 'Business approval recorded successfully.');
        } catch (ValidationException $e) {
            return $this->sendError('Business approval failed.', $e->errors(), 422);
        }
    }

    public function approveRuleFinance(Request $request, int $id): JsonResponse
    {
        $rule = AgentIncentiveRule::findOrFail($id);
        try {
            $updated = $this->ruleService->recordFinanceApproval(
                $rule,
                $request->user(),
                (string)$request->input('finance_approval_ref', ''),
                $request->input('reason')
            );
            return $this->sendResponse($updated, 'Finance approval recorded successfully.');
        } catch (ValidationException $e) {
            return $this->sendError('Finance approval failed.', $e->errors(), 422);
        }
    }

    public function activateRule(Request $request, int $id): JsonResponse
    {
        $rule = AgentIncentiveRule::findOrFail($id);
        try {
            $activated = $this->ruleService->activateRule($rule, $request->user(), $request->input('reason'));
            return $this->sendResponse($activated, "Rule {$activated->rule_code} v{$activated->version} activated.");
        } catch (ValidationException $e) {
            return $this->sendError('Rule activation blocked.', $e->errors(), 422);
        }
    }

    public function deactivateRule(Request $request, int $id): JsonResponse
    {
        $rule = AgentIncentiveRule::findOrFail($id);
        $validated = $request->validate(['reason' => 'required|string|max:500']);
        $updated = $this->ruleService->deactivateRule($rule, $request->user(), $validated['reason']);

        return $this->sendResponse($updated, 'Incentive rule deactivated.');
    }

    /**
     * POST /api/v2/admin/incentives/preview
     * Non-posting simulation/preview for Business & Finance review.
     */
    public function previewCalculation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'rule_id'  => 'nullable|integer|exists:agent_incentive_rules,id',
            'at'       => 'nullable|date',
        ]);

        $order = Order::findOrFail((int)$validated['order_id']);
        $parentOrder = $order->parent_order_id ? Order::findOrFail($order->parent_order_id) : $order;

        $rule = !empty($validated['rule_id'])
            ? AgentIncentiveRule::findOrFail((int)$validated['rule_id'])
            : $this->ruleService->resolveEffectiveRule(!empty($validated['at']) ? Carbon::parse($validated['at']) : now());

        if (!$rule) {
            return $this->sendError('No rule specified or active for preview timestamp.', [], 422);
        }

        try {
            $preview = $this->ruleService->calculateAmountForOrder($parentOrder, $rule);
            $preview['is_simulation_only'] = true;
            $preview['funding_policy_approved'] = (bool)config('marketplace_agents.funding_policy_approved', false);
            $preview['funding_source'] = AgentIncentiveRule::FUNDING_APNI_FACTORY;

            return $this->sendResponse($preview, 'Incentive preview computed cleanly (no ledger entry created).');
        } catch (ValidationException $e) {
            return $this->sendError('Preview calculation failed.', $e->errors(), 422);
        }
    }

    // -------------------------------------------------------------------------
    // IMMUTABLE INCENTIVE LEDGER & REPORTS
    // -------------------------------------------------------------------------

    public function ledger(Request $request): JsonResponse
    {
        $paginator = $this->calculationService->listLedgerEntries(
            $request->only(['field_agent_id', 'customer_id', 'status', 'entry_type', 'sequence', 'territory', 'date_from', 'date_to', 'search']),
            (int)$request->get('per_page', 20)
        );

        return response()->json([
            'status' => true,
            'code'   => 200,
            'data'   => $paginator->items(),
            'meta'   => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function showLedger(int $id): JsonResponse
    {
        $entry = AgentIncentiveLedger::with([
            'customer:id,name,mobile,location',
            'agent:id,agent_code,name,territory',
            'parentOrder:id,orderno,grandtotal,netamount,payment_status',
            'rule:id,rule_code,version,name',
            'referenceLedger:id,ledger_ref,entry_type,calculated_incentive_amount',
            'offsettingEntries:id,ledger_ref,entry_type,calculated_incentive_amount,status,reason,reference_ledger_id,created_at',
        ])->findOrFail($id);

        $data = $entry->toArray();
        $data['admin_notes'] = $entry->admin_notes;
        $data['audit_logs'] = AgentIncentiveAuditLog::where('entity_type', 'ledger')->where('entity_id', $entry->id)->orderByDesc('id')->get();

        return $this->sendResponse($data, 'Incentive ledger entry details retrieved.');
    }

    public function approveLedger(Request $request, int $id): JsonResponse
    {
        $entry = AgentIncentiveLedger::findOrFail($id);
        try {
            $updated = $this->calculationService->approveLedgerEntry($entry, $request->user(), $request->input('notes'));
            return $this->sendResponse($updated, 'Incentive ledger entry approved.');
        } catch (ValidationException $e) {
            return $this->sendError('Ledger approval failed.', $e->errors(), 422);
        }
    }

    public function reverseLedger(Request $request, int $id): JsonResponse
    {
        $entry = AgentIncentiveLedger::findOrFail($id);
        $validated = $request->validate([
            'reason'          => 'required|string|max:500',
            'idempotency_key' => 'nullable|string|max:120',
            'notes'           => 'nullable|string|max:1000',
        ]);

        try {
            $reversal = $this->calculationService->reverseLedgerEntry(
                $entry,
                $request->user(),
                $validated['reason'],
                $validated['idempotency_key'] ?? null,
                $validated['notes'] ?? null
            );
            return $this->sendResponse($reversal, 'Incentive reversal entry posted cleanly.', 201);
        } catch (ValidationException $e) {
            return $this->sendError('Reversal failed.', $e->errors(), 422);
        }
    }

    public function adjustLedger(Request $request, int $id): JsonResponse
    {
        $entry = AgentIncentiveLedger::findOrFail($id);
        $validated = $request->validate([
            'adjustment_amount' => 'required|numeric',
            'reason'            => 'required|string|max:500',
            'idempotency_key'   => 'nullable|string|max:120',
            'notes'             => 'nullable|string|max:1000',
        ]);

        try {
            $adj = $this->calculationService->adjustLedgerEntry(
                $entry,
                (float)$validated['adjustment_amount'],
                $request->user(),
                $validated['reason'],
                $validated['idempotency_key'] ?? null,
                $validated['notes'] ?? null
            );
            return $this->sendResponse($adj, 'Incentive adjustment entry posted cleanly.', 201);
        } catch (ValidationException $e) {
            return $this->sendError('Adjustment failed.', $e->errors(), 422);
        }
    }

    public function reports(Request $request): JsonResponse
    {
        $report = $this->calculationService->getLedgerReconciledReport(
            $request->only(['field_agent_id', 'customer_id', 'territory', 'status', 'entry_type', 'sequence', 'date_from', 'date_to', 'group_by'])
        );

        return $this->sendResponse($report, 'Ledger-reconciled incentive report generated.');
    }
}

