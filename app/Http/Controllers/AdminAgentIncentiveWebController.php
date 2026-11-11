<?php

namespace App\Http\Controllers;

use App\Models\AgentEligibleOrder;
use App\Models\AgentIncentiveAuditLog;
use App\Models\AgentIncentiveLedger;
use App\Models\AgentIncentiveProcessingLog;
use App\Models\AgentIncentiveRule;
use App\Models\AgentPayout;
use App\Models\AgentPayoutReconciliationException;
use App\Models\Customer;
use App\Models\FieldAgent;
use App\Services\AgentIncentiveCalculationService;
use App\Services\AgentIncentiveRuleService;
use App\Services\AgentOrderEligibilityService;
use App\Services\AgentPayoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminAgentIncentiveWebController extends Controller
{
    public function __construct(
        protected AgentOrderEligibilityService $eligibilityService,
        protected AgentIncentiveRuleService $ruleService,
        protected AgentIncentiveCalculationService $calculationService,
        protected AgentPayoutService $payoutService
    ) {
        $this->middleware(['auth', 'admin.catalogue']);
    }

    public function eligibleOrders(Request $request): View
    {
        $filters = $request->only(['field_agent_id', 'customer_id', 'eligibility_status', 'territory', 'date_from', 'date_to', 'search']);
        $orders = $this->eligibilityService->listEligibleOrders($filters, 20)->withQueryString();
        $agents = FieldAgent::orderBy('agent_code')->get(['id', 'agent_code', 'name', 'territory']);
        $customers = Customer::orderBy('name')->limit(150)->get(['id', 'name', 'mobile']);
        $failures = AgentIncentiveProcessingLog::where('status', AgentIncentiveProcessingLog::STATUS_FAILED)
            ->orderByDesc('id')->limit(15)->get();
        $deadLetterCount = \Illuminate\Support\Facades\Schema::hasTable('failed_jobs')
            ? \Illuminate\Support\Facades\DB::table('failed_jobs')->count()
            : 0;

        $reportSummary = $this->calculationService->getLedgerReconciledReport($filters)['summary'];

        return view('admin.incentives.eligible_orders', compact('orders', 'filters', 'agents', 'customers', 'failures', 'deadLetterCount', 'reportSummary'));
    }

    public function retryFailure(int $logId): RedirectResponse
    {
        try {
            $this->eligibilityService->retryFailedEvent($logId, auth()->user());
            return redirect()->back()->with('success', "Event log #{$logId} re-processed successfully.");
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }
    }

    public function showEligibleOrder(int $id): View
    {
        $eligibleOrder = AgentEligibleOrder::with([
            'customer',
            'agent',
            'attribution',
            'parentOrder',
            'ledgerEntries.rule',
        ])->findOrFail($id);

        $progress = $this->eligibilityService->getBuyerEligibilityProgress((int)$eligibleOrder->customer_id);
        $auditLogs = AgentIncentiveAuditLog::where('entity_type', 'eligible_order')
            ->where('entity_id', $eligibleOrder->id)
            ->orderByDesc('id')
            ->get();

        return view('admin.incentives.eligible_order_show', compact('eligibleOrder', 'progress', 'auditLogs'));
    }

    public function rules(Request $request): View
    {
        $filters = $request->only(['status', 'calculation_method', 'search']);
        $rules = $this->ruleService->listRules($filters, 20)->withQueryString();
        $fundingApproved = (bool)config('marketplace_agents.funding_policy_approved', false);

        return view('admin.incentives.rules_index', compact('rules', 'filters', 'fundingApproved'));
    }

    public function createRule(): View
    {
        return view('admin.incentives.rules_create');
    }

    public function storeRule(Request $request): RedirectResponse
    {
        try {
            $rule = $this->ruleService->createRule($request->all(), auth()->user());

            return redirect()->route('admin.incentives.rules.show', $rule->id)
                ->with('success', "Draft rule '{$rule->rule_code}' v{$rule->version} created cleanly (inactive pending Business & Finance approvals).");
        } catch (ValidationException $e) {
            return redirect()->back()->withInput()->withErrors($e->errors());
        }
    }

    public function showRule(int $id): View
    {
        $rule = AgentIncentiveRule::with(['creator', 'businessApprover', 'financeApprover', 'parentRule'])->findOrFail($id);
        $auditLogs = AgentIncentiveAuditLog::where('entity_type', 'rule')->where('entity_id', $rule->id)->orderByDesc('id')->get();
        $fundingApproved = (bool)config('marketplace_agents.funding_policy_approved', false);

        return view('admin.incentives.rules_show', compact('rule', 'auditLogs', 'fundingApproved'));
    }

    public function approveRuleBusiness(Request $request, int $id): RedirectResponse
    {
        $rule = AgentIncentiveRule::findOrFail($id);
        try {
            $this->ruleService->recordBusinessApproval(
                $rule,
                auth()->user(),
                (string)$request->input('business_approval_ref', ''),
                $request->input('reason')
            );
            return redirect()->route('admin.incentives.rules.show', $rule->id)
                ->with('success', 'Business approval recorded.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }
    }

    public function approveRuleFinance(Request $request, int $id): RedirectResponse
    {
        $rule = AgentIncentiveRule::findOrFail($id);
        try {
            $this->ruleService->recordFinanceApproval(
                $rule,
                auth()->user(),
                (string)$request->input('finance_approval_ref', ''),
                $request->input('reason')
            );
            return redirect()->route('admin.incentives.rules.show', $rule->id)
                ->with('success', 'Finance approval recorded.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }
    }

    public function activateRule(Request $request, int $id): RedirectResponse
    {
        $rule = AgentIncentiveRule::findOrFail($id);
        try {
            $this->ruleService->activateRule($rule, auth()->user(), $request->input('reason'));
            return redirect()->route('admin.incentives.rules.show', $rule->id)
                ->with('success', "Rule {$rule->rule_code} v{$rule->version} activated.");
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }
    }

    public function deactivateRule(Request $request, int $id): RedirectResponse
    {
        $rule = AgentIncentiveRule::findOrFail($id);
        $request->validate(['reason' => 'required|string|max:500']);
        $this->ruleService->deactivateRule($rule, auth()->user(), (string)$request->input('reason'));

        return redirect()->route('admin.incentives.rules.show', $rule->id)
            ->with('success', 'Rule deactivated.');
    }

    public function ledger(Request $request): View
    {
        $filters = $request->only(['field_agent_id', 'customer_id', 'status', 'entry_type', 'sequence', 'territory', 'date_from', 'date_to', 'search', 'group_by']);
        $entries = $this->calculationService->listLedgerEntries($filters, 20)->withQueryString();
        $report = $this->calculationService->getLedgerReconciledReport($filters);
        $agents = FieldAgent::orderBy('agent_code')->get(['id', 'agent_code', 'name', 'territory']);

        return view('admin.incentives.ledger', compact('entries', 'report', 'filters', 'agents'));
    }

    public function approveLedger(Request $request, int $id): RedirectResponse
    {
        $entry = AgentIncentiveLedger::findOrFail($id);
        try {
            $this->calculationService->approveLedgerEntry($entry, auth()->user(), $request->input('notes'));
            return redirect()->back()->with('success', "Ledger entry #{$entry->ledger_ref} approved.");
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }
    }

    public function reverseLedger(Request $request, int $id): RedirectResponse
    {
        $entry = AgentIncentiveLedger::findOrFail($id);
        $request->validate(['reason' => 'required|string|max:500']);
        try {
            $rev = $this->calculationService->reverseLedgerEntry($entry, auth()->user(), (string)$request->input('reason'));
            return redirect()->back()->with('success', "Immutable reversal entry #{$rev->ledger_ref} posted.");
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }
    }

    public function adjustLedger(Request $request, int $id): RedirectResponse
    {
        $entry = AgentIncentiveLedger::findOrFail($id);
        $request->validate([
            'adjustment_amount' => 'required|numeric',
            'reason'            => 'required|string|max:500',
        ]);
        try {
            $adj = $this->calculationService->adjustLedgerEntry(
                $entry,
                (float)$request->input('adjustment_amount'),
                auth()->user(),
                (string)$request->input('reason')
            );
            return redirect()->back()->with('success', "Immutable adjustment entry #{$adj->ledger_ref} posted.");
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }
    }

    public function reports(Request $request): View
    {
        $filters = $request->only(['field_agent_id', 'customer_id', 'territory', 'status', 'entry_type', 'sequence', 'date_from', 'date_to', 'group_by']);
        $report = $this->calculationService->getLedgerReconciledReport($filters);
        $agents = FieldAgent::orderBy('agent_code')->get(['id', 'agent_code', 'name', 'territory']);

        return view('admin.incentives.reports', compact('report', 'filters', 'agents'));
    }

    public function holdLedger(Request $request, int $id): RedirectResponse
    {
        $entry = AgentIncentiveLedger::findOrFail($id);
        $request->validate(['reason' => 'required|string|max:500']);
        try {
            $this->calculationService->holdLedgerEntry($entry, auth()->user(), (string)$request->input('reason'));
            return redirect()->back()->with('success', "Ledger entry #{$entry->ledger_ref} placed on hold.");
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }
    }

    public function rejectLedger(Request $request, int $id): RedirectResponse
    {
        $entry = AgentIncentiveLedger::findOrFail($id);
        $request->validate(['reason' => 'required|string|max:500']);
        try {
            $this->calculationService->rejectLedgerEntry($entry, auth()->user(), (string)$request->input('reason'));
            return redirect()->back()->with('success', "Ledger entry #{$entry->ledger_ref} rejected.");
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }
    }

    public function exportCsv(Request $request)
    {
        return $this->payoutService->streamReconciledCsvExport(
            $request->only(['field_agent_id', 'status', 'date_from', 'date_to'])
        );
    }

    public function payoutsIndex(Request $request): View
    {
        $filters = $request->only(['field_agent_id', 'status', 'reconciliation_status', 'territory', 'date_from', 'date_to', 'search']);
        $payouts = $this->payoutService->listPayouts($filters, 20)->withQueryString();
        $summary = $this->payoutService->getFinanceDashboardSummary($filters);
        $agents = FieldAgent::orderBy('agent_code')->get(['id', 'agent_code', 'name', 'territory', 'status']);

        $approvedEntries = AgentIncentiveLedger::with(['agent:id,agent_code,name', 'parentOrder:id,orderno'])
            ->where('entry_type', AgentIncentiveLedger::TYPE_EARNED)
            ->where('status', AgentIncentiveLedger::STATUS_APPROVED)
            ->when(!empty($filters['field_agent_id']), fn($q) => $q->where('field_agent_id', (int)$filters['field_agent_id']))
            ->orderByDesc('id')
            ->get()
            ->map(function (AgentIncentiveLedger $e) {
                $e->readiness = $this->payoutService->checkLedgerEntryReadiness($e);
                return $e;
            });

        return view('admin.incentives.payouts_index', compact('payouts', 'summary', 'agents', 'approvedEntries', 'filters'));
    }

    public function payoutsShow(int $id): View
    {
        $payout = AgentPayout::with(['agent', 'items.ledgerEntry.parentOrder', 'exceptions', 'initiator', 'authorizer', 'reconciler'])->findOrFail($id);
        $auditLogs = AgentIncentiveAuditLog::where('entity_type', 'payout')->where('entity_id', $payout->id)->orderByDesc('id')->get();
        $executionEnabled = (bool)config('marketplace_agents.payout_execution_enabled', false);

        return view('admin.incentives.payouts_show', compact('payout', 'auditLogs', 'executionEnabled'));
    }

    public function payoutsCreateBatch(Request $request): RedirectResponse
    {
        $request->validate([
            'field_agent_id' => 'required|integer|exists:field_agents,id',
            'ledger_ids'     => 'required|array|min:1',
        ]);

        try {
            $payout = $this->payoutService->createPayoutBatch(
                (int)$request->input('field_agent_id'),
                (array)$request->input('ledger_ids'),
                auth()->user(),
                $request->only(['payment_method', 'withholding_tax_rate', 'admin_notes'])
            );

            return redirect()->route('admin.incentives.payouts.show', $payout->id)
                ->with('success', "Payout batch {$payout->payout_ref} assembled cleanly ({$payout->status}).");
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }
    }

    public function payoutsAuthorize(Request $request, int $id): RedirectResponse
    {
        $payout = AgentPayout::findOrFail($id);
        try {
            $this->payoutService->authorizePayout($payout, auth()->user(), $request->input('notes'));
            return redirect()->back()->with('success', "Payout {$payout->payout_ref} authorized.");
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }
    }

    public function payoutsInitiate(Request $request, int $id): RedirectResponse
    {
        $payout = AgentPayout::findOrFail($id);
        try {
            $this->payoutService->recordPaymentInitiation($payout, auth()->user(), $request->all());
            return redirect()->back()->with('success', "Payout {$payout->payout_ref} initiation recorded.");
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }
    }

    public function payoutsConfirm(Request $request, int $id): RedirectResponse
    {
        $payout = AgentPayout::findOrFail($id);
        try {
            $this->payoutService->confirmPayoutPayment($payout, $request->all(), auth()->user());
            return redirect()->back()->with('success', "Payout {$payout->payout_ref} confirmed paid & reconciled.");
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }
    }

    public function payoutsFail(Request $request, int $id): RedirectResponse
    {
        $payout = AgentPayout::findOrFail($id);
        $request->validate(['failure_reason' => 'required|string|max:500']);
        try {
            $this->payoutService->recordPayoutFailure($payout, auth()->user(), (string)$request->input('failure_reason'), $request->input('external_transaction_ref'));
            return redirect()->back()->with('success', "Payout {$payout->payout_ref} marked failed & logged for reconciliation.");
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }
    }

    public function reconciliationExceptions(Request $request): View
    {
        $exceptions = AgentPayoutReconciliationException::with(['payout', 'ledgerEntry', 'agent'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->input('status')))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.incentives.exceptions', compact('exceptions'));
    }

    public function resolveException(Request $request, int $id): RedirectResponse
    {
        $ex = AgentPayoutReconciliationException::findOrFail($id);
        $request->validate([
            'status'           => 'required|string|in:under_review,resolved,dismissed',
            'resolution_notes' => 'required|string|max:1000',
        ]);
        $this->payoutService->resolveException($ex, (string)$request->input('status'), (string)$request->input('resolution_notes'), auth()->user());

        return redirect()->back()->with('success', "Exception {$ex->exception_ref} updated.");
    }
}

