<?php

namespace App\Http\Controllers;

use App\Services\BuyerAttributionService;
use App\Services\FieldAgentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgentPortalWebController extends Controller
{
    public function __construct(
        protected FieldAgentService $agentService,
        protected BuyerAttributionService $attributionService
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

        return view('agent.buyer_show', compact('agentProfile', 'buyerData'));
    }
}

