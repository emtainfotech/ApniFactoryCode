<?php

namespace App\Http\Controllers;

use App\Models\AgentEligibleOrder;
use App\Models\AgentIncentiveAuditLog;
use App\Models\AgentIncentiveLedger;
use App\Models\AgentIncentiveProcessingLog;
use App\Models\AgentIncentiveRule;
use App\Models\Customer;
use App\Models\FieldAgent;
use App\Services\AgentIncentiveCalculationService;
use App\Services\AgentIncentiveRuleService;
use App\Services\AgentOrderEligibilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminAgentIncentiveWebController extends Controller
{
    public function __construct(
        protected AgentOrderEligibilityService $eligibilityService,
        protected AgentIncentiveRuleService $ruleService,
        protected AgentIncentiveCalculationService $calculationService
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
            ->orderByDesc('id')->limit(10)->get();

        $reportSummary = $this->calculationService->getLedgerReconciledReport($filters)['summary'];

        return view('admin.incentives.eligible_orders', compact('orders', 'filters', 'agents', 'customers', 'failures', 'reportSummary'));
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
}

