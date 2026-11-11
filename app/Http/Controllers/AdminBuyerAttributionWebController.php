<?php

namespace App\Http\Controllers;

use App\Models\BuyerAgentAttribution;
use App\Models\Customer;
use App\Models\FieldAgent;
use App\Services\BuyerAttributionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminBuyerAttributionWebController extends Controller
{
    public function __construct(
        protected BuyerAttributionService $attributionService
    ) {
        $this->middleware(['auth', 'admin.catalogue']);
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'approval_status', 'field_agent_id', 'search']);
        $attributions = $this->attributionService->listAttributions($filters, 20);
        $metrics = $this->attributionService->getAdminAttributionMetrics();

        $activeAgents = FieldAgent::where('status', FieldAgent::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'agent_code', 'name', 'territory']);
        $allAgents = FieldAgent::orderBy('name')->get(['id', 'agent_code', 'name', 'status']);
        $customers = Customer::with('activeAttribution.agent')
            ->orderBy('name')
            ->get(['id', 'name', 'mobile', 'location', 'status']);

        return view('admin.attributions.index', compact(
            'attributions',
            'metrics',
            'activeAgents',
            'allAgents',
            'customers',
            'filters'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id'        => 'required|integer|exists:customers,id',
            'field_agent_id'     => 'required|integer|exists:field_agents,id',
            'attribution_source' => ['required', Rule::in(['onboarding', 'activation', 'admin_linking'])],
            'evidence_reference' => 'nullable|string|max:255',
            'remarks'            => 'nullable|string|max:1000',
            'admin_notes'        => 'nullable|string|max:2000',
            'require_approval'   => 'nullable|boolean',
        ]);
        $validated['require_approval'] = $request->boolean('require_approval');

        $attribution = $this->attributionService->createAttribution($validated, auth()->user());

        return redirect()->route('admin.attributions.show', $attribution->id)
            ->with('success', "Attribution #{$attribution->attribution_ref} recorded successfully.");
    }

    public function show(int $id): View
    {
        $attribution = BuyerAgentAttribution::with([
            'customer',
            'agent',
            'attributedBy:id,name,email',
            'approvedBy:id,name,email',
            'rejectedBy:id,name,email',
            'supersededBy.agent',
            'reassignedFrom.agent',
            'history',
        ])->findOrFail($id);

        $eligibleOrderStatus = $this->attributionService->checkBuyerEligibleOrderStatus($attribution->customer_id);
        $activeAgents = FieldAgent::where('status', FieldAgent::STATUS_ACTIVE)
            ->where('id', '!=', $attribution->field_agent_id)
            ->orderBy('name')
            ->get(['id', 'agent_code', 'name', 'territory']);

        return view('admin.attributions.show', compact('attribution', 'eligibleOrderStatus', 'activeAgents'));
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $attribution = BuyerAgentAttribution::findOrFail($id);
        $validated = $request->validate(['notes' => 'nullable|string|max:1000']);

        $this->attributionService->approveAttribution($attribution, auth()->user(), $validated['notes'] ?? null);

        return redirect()->route('admin.attributions.show', $attribution->id)
            ->with('success', "Attribution #{$attribution->attribution_ref} approved and activated.");
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $attribution = BuyerAgentAttribution::findOrFail($id);
        $validated = $request->validate(['reason' => 'required|string|min:5|max:1000']);

        $this->attributionService->rejectAttribution($attribution, auth()->user(), $validated['reason']);

        return redirect()->route('admin.attributions.show', $attribution->id)
            ->with('success', "Attribution #{$attribution->attribution_ref} rejected.");
    }

    public function reassign(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id'            => 'required|integer|exists:customers,id',
            'new_field_agent_id'     => 'required|integer|exists:field_agents,id',
            'reason'                 => 'required|string|min:5|max:1000',
            'evidence_reference'     => 'nullable|string|max:255',
            'admin_notes'            => 'nullable|string|max:2000',
            'is_exception_approved'  => 'nullable|boolean',
            'exception_approval_ref' => 'nullable|string|max:120',
        ]);
        $validated['is_exception_approved'] = $request->boolean('is_exception_approved');

        $customer = Customer::findOrFail((int)$validated['customer_id']);
        $newAgent = FieldAgent::findOrFail((int)$validated['new_field_agent_id']);

        $replacement = $this->attributionService->reassignAttribution($customer, $newAgent, auth()->user(), $validated);

        return redirect()->route('admin.attributions.show', $replacement->id)
            ->with('success', "Buyer '{$customer->name}' reassigned to Agent '{$newAgent->agent_code}'.");
    }
}

