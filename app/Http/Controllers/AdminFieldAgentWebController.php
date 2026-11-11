<?php

namespace App\Http\Controllers;

use App\Models\FieldAgent;
use App\Models\User;
use App\Services\FieldAgentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminFieldAgentWebController extends Controller
{
    public function __construct(
        protected FieldAgentService $agentService
    ) {
        $this->middleware(['auth', 'admin.catalogue']);
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'territory', 'kyc_status', 'search']);
        $agents = $this->agentService->listAgents($filters, 20);
        $metrics = $this->agentService->getAdminMetrics();

        return view('admin.agents.index', compact('agents', 'metrics', 'filters'));
    }

    public function create(): View
    {
        $supervisors = User::orderBy('name')->get(['id', 'name', 'email']);
        return view('admin.agents.create', compact('supervisors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'agent_code'            => 'nullable|string|max:40',
            'name'                  => 'required|string|max:255',
            'email'                 => 'nullable|email|max:255',
            'mobile'                => 'required|string|min:8|max:20',
            'alternate_mobile'      => 'nullable|string|max:20',
            'territory'             => 'required|string|max:255',
            'territory_city'        => 'nullable|string|max:100',
            'territory_state'       => 'nullable|string|max:100',
            'status'                => ['required', Rule::in(array_keys(config('marketplace_agents.agent_statuses')))],
            'joining_date'          => 'nullable|date',
            'supervisor_user_id'    => 'nullable|integer|exists:users,id',
            'supervisor_name'       => 'nullable|string|max:255',
            'kyc_status'            => ['required', Rule::in(array_keys(config('marketplace_agents.kyc_statuses')))],
            'contract_status'       => ['required', Rule::in(array_keys(config('marketplace_agents.contract_statuses')))],
            'kyc_reference'         => 'nullable|string|max:120',
            'notes'                 => 'nullable|string|max:2000',
            'create_user_account'   => 'nullable|boolean',
            'password'              => 'nullable|string|min:8',
            'account_holder_name'   => 'nullable|string|max:255',
            'bank_name'             => 'nullable|string|max:255',
            'account_number'        => 'nullable|string|max:64',
            'ifsc_code'             => 'nullable|string|max:20',
            'upi_id'                => 'nullable|string|max:120',
        ]);

        $validated['payout_details'] = [
            'account_holder_name' => $validated['account_holder_name'] ?? null,
            'bank_name'           => $validated['bank_name'] ?? null,
            'account_number'      => $validated['account_number'] ?? null,
            'ifsc_code'           => $validated['ifsc_code'] ?? null,
            'upi_id'              => $validated['upi_id'] ?? null,
        ];
        $validated['create_user_account'] = $request->boolean('create_user_account');

        $agent = $this->agentService->createAgent($validated, auth()->user());

        return redirect()->route('admin.agents.show', $agent->id)
            ->with('success', "Field sales agent '{$agent->agent_code}' created successfully.");
    }

    public function show(int $id): View
    {
        $agent = FieldAgent::withCount(['activeAttributions', 'attributions'])
            ->with([
                'user:id,name,email',
                'supervisor:id,name,email',
                'createdBy:id,name,email',
                'updatedBy:id,name,email',
                'statusHistory',
                'attributions.customer:id,name,mobile,email,location,status',
            ])
            ->findOrFail($id);

        return view('admin.agents.show', compact('agent'));
    }

    public function edit(int $id): View
    {
        $agent = FieldAgent::findOrFail($id);
        $supervisors = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.agents.edit', compact('agent', 'supervisors'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $agent = FieldAgent::findOrFail($id);

        $validated = $request->validate([
            'agent_code'          => 'required|string|max:40',
            'name'                => 'required|string|max:255',
            'email'               => 'nullable|email|max:255',
            'mobile'              => 'required|string|min:8|max:20',
            'alternate_mobile'    => 'nullable|string|max:20',
            'territory'           => 'required|string|max:255',
            'territory_city'      => 'nullable|string|max:100',
            'territory_state'     => 'nullable|string|max:100',
            'joining_date'        => 'nullable|date',
            'supervisor_user_id'  => 'nullable|integer|exists:users,id',
            'supervisor_name'     => 'nullable|string|max:255',
            'kyc_status'          => ['required', Rule::in(array_keys(config('marketplace_agents.kyc_statuses')))],
            'contract_status'     => ['required', Rule::in(array_keys(config('marketplace_agents.contract_statuses')))],
            'kyc_reference'       => 'nullable|string|max:120',
            'notes'               => 'nullable|string|max:2000',
            'reason'              => 'nullable|string|max:500',
            'account_holder_name' => 'nullable|string|max:255',
            'bank_name'           => 'nullable|string|max:255',
            'account_number'      => 'nullable|string|max:64',
            'ifsc_code'           => 'nullable|string|max:20',
            'upi_id'              => 'nullable|string|max:120',
        ]);

        $payoutInput = [
            'account_holder_name' => $validated['account_holder_name'] ?? null,
            'bank_name'           => $validated['bank_name'] ?? null,
            'account_number'      => $validated['account_number'] ?? null,
            'ifsc_code'           => $validated['ifsc_code'] ?? null,
            'upi_id'              => $validated['upi_id'] ?? null,
        ];
        if (!empty(array_filter($payoutInput))) {
            $validated['payout_details'] = $payoutInput;
        }

        $this->agentService->updateAgent($agent, $validated, auth()->user(), $validated['reason'] ?? null);

        return redirect()->route('admin.agents.show', $agent->id)
            ->with('success', "Agent '{$agent->agent_code}' updated successfully.");
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $agent = FieldAgent::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(config('marketplace_agents.agent_statuses')))],
            'reason' => 'nullable|string|max:500',
        ]);

        $this->agentService->changeStatus($agent, $validated['status'], auth()->user(), $validated['reason'] ?? null);

        return redirect()->route('admin.agents.show', $agent->id)
            ->with('success', "Agent status updated to '{$validated['status']}'.");
    }
}

