<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\V2\BaseApiController;
use App\Models\FieldAgent;
use App\Services\FieldAgentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminFieldAgentApiController extends BaseApiController
{
    public function __construct(
        protected FieldAgentService $agentService
    ) {
    }

    protected function formatAgentForAdmin(FieldAgent $agent, bool $includeConfidential = false): array
    {
        $base = [
            'id'                        => $agent->id,
            'user_id'                   => $agent->user_id,
            'agent_code'                => $agent->agent_code,
            'name'                      => $agent->name,
            'email'                     => $agent->email,
            'mobile'                    => $agent->mobile,
            'alternate_mobile'          => $agent->alternate_mobile,
            'territory'                 => $agent->territory,
            'territory_city'            => $agent->territory_city,
            'territory_state'           => $agent->territory_state,
            'status'                    => $agent->status,
            'joining_date'              => $agent->joining_date ? $agent->joining_date->toDateString() : null,
            'supervisor_user_id'        => $agent->supervisor_user_id,
            'supervisor_name'           => $agent->supervisor_name ?? $agent->supervisor?->name,
            'kyc_status'                => $agent->kyc_status,
            'contract_status'           => $agent->contract_status,
            'active_attributions_count' => $agent->active_attributions_count ?? null,
            'attributions_count'        => $agent->attributions_count ?? null,
            'payout_summary'            => $agent->maskedPayoutSummary(),
            'created_by'                => $agent->createdBy ? $agent->createdBy->only(['id', 'name', 'email']) : null,
            'created_at'                => $agent->created_at ? $agent->created_at->toIso8601String() : null,
            'updated_at'                => $agent->updated_at ? $agent->updated_at->toIso8601String() : null,
        ];

        if ($includeConfidential) {
            $base['kyc_reference'] = $agent->kyc_reference;
            $base['payout_details'] = $agent->payout_details;
            $base['notes'] = $agent->notes;
            $base['deactivated_at'] = $agent->deactivated_at ? $agent->deactivated_at->toIso8601String() : null;
            $base['deactivation_reason'] = $agent->deactivation_reason;
        }

        return $base;
    }

    /**
     * GET /api/v2/admin/agents
     * Search & filter Field Sales Agents.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return $this->sendError('Forbidden: Administrator access required.', [], 403);
        }

        $paginator = $this->agentService->listAgents(
            $request->only(['status', 'territory', 'kyc_status', 'search']),
            (int)$request->get('per_page', 20)
        );

        $items = collect($paginator->items())->map(fn($agent) => $this->formatAgentForAdmin($agent, false));

        return response()->json([
            'status'  => true,
            'code'    => 200,
            'message' => 'Field sales agents retrieved successfully.',
            'metrics' => $this->agentService->getAdminMetrics(),
            'data'    => $items,
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
     * POST /api/v2/admin/agents
     * Create a new Field Sales Agent master record.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return $this->sendError('Forbidden: Administrator access required.', [], 403);
        }

        $validated = $request->validate([
            'agent_code'                      => 'nullable|string|max:40',
            'name'                            => 'required|string|max:255',
            'email'                           => 'nullable|email|max:255',
            'mobile'                          => 'required|string|min:8|max:20',
            'alternate_mobile'                => 'nullable|string|max:20',
            'territory'                       => 'required|string|max:255',
            'territory_city'                  => 'nullable|string|max:100',
            'territory_state'                 => 'nullable|string|max:100',
            'status'                          => ['nullable', Rule::in(array_keys(config('marketplace_agents.agent_statuses')))],
            'joining_date'                    => 'nullable|date',
            'user_id'                         => 'nullable|integer|exists:users,id',
            'create_user_account'             => 'nullable|boolean',
            'password'                        => 'nullable|string|min:8',
            'supervisor_user_id'              => 'nullable|integer|exists:users,id',
            'supervisor_name'                 => 'nullable|string|max:255',
            'kyc_status'                      => ['nullable', Rule::in(array_keys(config('marketplace_agents.kyc_statuses')))],
            'contract_status'                 => ['nullable', Rule::in(array_keys(config('marketplace_agents.contract_statuses')))],
            'kyc_reference'                   => 'nullable|string|max:120',
            'notes'                           => 'nullable|string|max:2000',
            'reason'                          => 'nullable|string|max:500',
            'payout_details'                  => 'nullable|array',
            'payout_details.account_holder_name' => 'nullable|string|max:255',
            'payout_details.bank_name'        => 'nullable|string|max:255',
            'payout_details.account_number'   => 'nullable|string|max:64',
            'payout_details.ifsc_code'        => 'nullable|string|max:20',
            'payout_details.upi_id'           => 'nullable|string|max:120',
        ]);

        $agent = $this->agentService->createAgent($validated, $user);

        return $this->sendResponse(
            $this->formatAgentForAdmin($agent, true),
            "Field sales agent '{$agent->agent_code}' created successfully.",
            201
        );
    }

    /**
     * GET /api/v2/admin/agents/{id}
     * View full agent details, assigned buyers, and status history (Admin Only).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return $this->sendError('Forbidden: Administrator access required.', [], 403);
        }

        $agent = FieldAgent::withCount(['activeAttributions', 'attributions'])
            ->with([
                'supervisor:id,name,email',
                'createdBy:id,name,email',
                'statusHistory',
                'activeAttributions.customer:id,name,location,mobile,email,status',
            ])
            ->findOrFail($id);

        $data = $this->formatAgentForAdmin($agent, true);
        $data['assigned_buyers'] = $agent->activeAttributions->map(fn($attr) => [
            'attribution_id'     => $attr->id,
            'attribution_ref'    => $attr->attribution_ref,
            'customer_id'        => $attr->customer_id,
            'customer_name'      => $attr->customer?->name,
            'customer_location'  => $attr->customer?->location,
            'customer_mobile'    => $attr->customer?->mobile,
            'status'             => $attr->status,
            'attributed_at'      => $attr->attributed_at?->toIso8601String(),
        ]);
        $data['status_history'] = $agent->statusHistory;

        return $this->sendResponse($data, 'Field sales agent details retrieved successfully.');
    }

    /**
     * PUT /api/v2/admin/agents/{id}
     * Update Field Sales Agent profile.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return $this->sendError('Forbidden: Administrator access required.', [], 403);
        }

        $agent = FieldAgent::findOrFail($id);

        $validated = $request->validate([
            'agent_code'                      => 'nullable|string|max:40',
            'name'                            => 'sometimes|required|string|max:255',
            'email'                           => 'nullable|email|max:255',
            'mobile'                          => 'sometimes|required|string|min:8|max:20',
            'alternate_mobile'                => 'nullable|string|max:20',
            'territory'                       => 'sometimes|required|string|max:255',
            'territory_city'                  => 'nullable|string|max:100',
            'territory_state'                 => 'nullable|string|max:100',
            'joining_date'                    => 'nullable|date',
            'supervisor_user_id'              => 'nullable|integer|exists:users,id',
            'supervisor_name'                 => 'nullable|string|max:255',
            'kyc_status'                      => ['nullable', Rule::in(array_keys(config('marketplace_agents.kyc_statuses')))],
            'contract_status'                 => ['nullable', Rule::in(array_keys(config('marketplace_agents.contract_statuses')))],
            'kyc_reference'                   => 'nullable|string|max:120',
            'notes'                           => 'nullable|string|max:2000',
            'reason'                          => 'nullable|string|max:500',
            'payout_details'                  => 'nullable|array',
            'payout_details.account_holder_name' => 'nullable|string|max:255',
            'payout_details.bank_name'        => 'nullable|string|max:255',
            'payout_details.account_number'   => 'nullable|string|max:64',
            'payout_details.ifsc_code'        => 'nullable|string|max:20',
            'payout_details.upi_id'           => 'nullable|string|max:120',
        ]);

        $updated = $this->agentService->updateAgent($agent, $validated, $user, $validated['reason'] ?? null);

        return $this->sendResponse(
            $this->formatAgentForAdmin($updated, true),
            'Field sales agent updated successfully.'
        );
    }

    /**
     * PATCH /api/v2/admin/agents/{id}/status
     * Change Field Sales Agent operational status with reason and audit trail.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return $this->sendError('Forbidden: Administrator access required.', [], 403);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(config('marketplace_agents.agent_statuses')))],
            'reason' => 'nullable|string|max:500',
        ]);

        $agent = FieldAgent::findOrFail($id);
        $updated = $this->agentService->changeStatus($agent, $validated['status'], $user, $validated['reason'] ?? null);

        return $this->sendResponse(
            $this->formatAgentForAdmin($updated, true),
            "Field agent status changed to '{$updated->status}'."
        );
    }
}

