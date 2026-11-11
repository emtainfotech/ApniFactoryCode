<?php

namespace App\Http\Controllers;

use App\Services\AgentIncentiveCalculationService;
use App\Services\AgentOrderEligibilityService;
use App\Services\BuyerAttributionService;
use App\Services\FieldAgentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgentPortalWebController extends Controller
{
    public function __construct(
        protected FieldAgentService $agentService,
        protected BuyerAttributionService $attributionService,
        protected AgentOrderEligibilityService $eligibilityService,
        protected AgentIncentiveCalculationService $calculationService
    ) {
        $this->middleware(['auth', 'field.agent']);
    }

    public function dashboard(Request $request): View
    {
        $agent = $this->agentService->resolveAgentForUser(auth()->user());
        if (!$agent) {
            abort(403, 'Field sales agent profile not found.');
        }

        $filters = $request->only(['status', 'search']);
        $buyers = $this->attributionService->getAgentAssignedBuyers($agent, $filters, 20);
        $agentProfile = $agent->toAgentSelfArray();

        return view('agent.dashboard', compact('agent', 'agentProfile', 'buyers', 'filters'));
    }

    public function showBuyer(int $customerId): View
    {
        $agent = $this->agentService->resolveAgentForUser(auth()->user());
        if (!$agent) {
            abort(403, 'Field sales agent profile not found.');
        }

        try {
            $buyerData = $this->attributionService->getAssignedBuyerForAgent($agent, $customerId);
        } catch (\Illuminate\Validation\ValidationException $e) {
            abort(403, 'Forbidden: Buyer account is not attributed to your profile.');
        }

        $agentProfile = $agent->toAgentSelfArray();
        $incentiveSummary = $this->calculationService->getAgentIncentiveSummary($agent->id);

        return view('agent.buyer_show', compact('agentProfile', 'buyerData', 'incentiveSummary'));
    }

    public function incentives(Request $request): View
    {
        $agent = $this->agentService->resolveAgentForUser(auth()->user());
        if (!$agent) {
            abort(403, 'Field sales agent profile not found.');
        }

        $agentProfile = $agent->toAgentSelfArray();
        $incentiveSummary = $this->calculationService->getAgentIncentiveSummary($agent->id);
        $filters = array_merge($request->only(['eligibility_status', 'search']), ['field_agent_id' => $agent->id]);
        $trackedOrders = $this->eligibilityService->listEligibleOrders($filters, 20)->withQueryString();

        return view('agent.incentives', compact('agent', 'agentProfile', 'incentiveSummary', 'trackedOrders', 'filters'));
    }
}

