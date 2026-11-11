<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\V2\BaseApiController;
use App\Models\BuyerAgentAttribution;
use App\Models\BuyerAttributionHistory;
use App\Models\Customer;
use App\Models\FieldAgent;
use App\Services\BuyerAttributionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminBuyerAttributionApiController extends BaseApiController
{
    public function __construct(
        protected BuyerAttributionService $attributionService
    ) {
    }

    /**
     * GET /api/v2/admin/attributions
     * List buyer-to-agent attributions with filters and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return $this->sendError('Forbidden: Administrator access required.', [], 403);
        }

        $paginator = $this->attributionService->listAttributions(
            $request->only(['status', 'approval_status', 'field_agent_id', 'customer_id', 'search']),
            (int)$request->get('per_page', 20)
        );

        return response()->json([
            'status'  => true,
            'code'    => 200,
            'message' => 'Buyer attributions retrieved successfully.',
            'metrics' => $this->attributionService->getAdminAttributionMetrics(),
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
     * POST /api/v2/admin/attributions
     * Attribute an active Field Sales Agent to a Buyer/Dealer account.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return $this->sendError('Forbidden: Administrator access required.', [], 403);
        }

        $validated = $request->validate([
            'customer_id'        => 'required|integer|exists:customers,id',
            'field_agent_id'     => 'required|integer|exists:field_agents,id',
            'attribution_source' => ['nullable', Rule::in(['onboarding', 'activation', 'admin_linking'])],
            'evidence_reference' => 'nullable|string|max:255',
            'remarks'            => 'nullable|string|max:1000',
            'admin_notes'        => 'nullable|string|max:2000',
            'require_approval'   => 'nullable|boolean',
            'idempotency_key'    => 'nullable|string|max:100',
        ]);

        $attribution = $this->attributionService->createAttribution($validated, $user);

        return $this->sendResponse(
            $attribution,
            "Buyer attribution #{$attribution->attribution_ref} recorded successfully.",
            201
        );
    }

    /**
     * GET /api/v2/admin/attributions/{id}
     * View single attribution details, evidence, and full audit history.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return $this->sendError('Forbidden: Administrator access required.', [], 403);
        }

        $attribution = BuyerAgentAttribution::with([
            'customer:id,name,email,mobile,location,status',
            'agent:id,agent_code,name,email,mobile,territory,status',
            'attributedBy:id,name,email',
            'approvedBy:id,name,email',
            'rejectedBy:id,name,email',
            'supersededBy',
            'reassignedFrom',
            'history',
        ])->findOrFail($id);

        $data = $attribution->toArray();
        $data['admin_notes'] = $attribution->admin_notes; // Admin-authorized field
        $data['eligible_order_status'] = $this->attributionService->checkBuyerEligibleOrderStatus($attribution->customer_id);

        return $this->sendResponse($data, 'Attribution details retrieved successfully.');
    }

    /**
     * POST /api/v2/admin/attributions/{id}/approve
     * Approve a pending attribution.
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return $this->sendError('Forbidden: Administrator access required.', [], 403);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $attribution = BuyerAgentAttribution::findOrFail($id);
        $updated = $this->attributionService->approveAttribution($attribution, $user, $validated['notes'] ?? null);

        return $this->sendResponse($updated, "Attribution #{$updated->attribution_ref} approved.");
    }

    /**
     * POST /api/v2/admin/attributions/{id}/reject
     * Reject a pending attribution.
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return $this->sendError('Forbidden: Administrator access required.', [], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:5|max:1000',
        ]);

        $attribution = BuyerAgentAttribution::findOrFail($id);
        $updated = $this->attributionService->rejectAttribution($attribution, $user, $validated['reason']);

        return $this->sendResponse($updated, "Attribution #{$updated->attribution_ref} rejected.");
    }

    /**
     * POST /api/v2/admin/attributions/reassign
     * Reassign a buyer/dealer to a different active agent (strictly governed by eligible order lock).
     */
    public function reassign(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return $this->sendError('Forbidden: Administrator access required.', [], 403);
        }

        $validated = $request->validate([
            'customer_id'            => 'required|integer|exists:customers,id',
            'new_field_agent_id'     => 'required|integer|exists:field_agents,id',
            'reason'                 => 'required|string|min:5|max:1000',
            'evidence_reference'     => 'nullable|string|max:255',
            'admin_notes'            => 'nullable|string|max:2000',
            'is_exception_approved'  => 'nullable|boolean',
            'exception_approval_ref' => 'nullable|string|max:120',
            'idempotency_key'        => 'nullable|string|max:100',
        ]);

        $customer = Customer::findOrFail((int)$validated['customer_id']);
        $newAgent = FieldAgent::findOrFail((int)$validated['new_field_agent_id']);

        $replacement = $this->attributionService->reassignAttribution($customer, $newAgent, $user, $validated);

        return $this->sendResponse(
            $replacement,
            "Buyer '{$customer->name}' reassigned to Agent '{$newAgent->agent_code}'."
        );
    }

    /**
     * GET /api/v2/admin/customers/{customerId}/attribution-history
     * View full non-destructive attribution timeline and audit history for a buyer.
     */
    public function customerHistory(Request $request, int $customerId): JsonResponse
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return $this->sendError('Forbidden: Administrator access required.', [], 403);
        }

        $customer = Customer::findOrFail($customerId);

        $attributions = BuyerAgentAttribution::where('customer_id', $customer->id)
            ->with(['agent:id,agent_code,name,territory,status', 'attributedBy:id,name', 'approvedBy:id,name'])
            ->orderBy('id', 'desc')
            ->get();

        $auditLogs = BuyerAttributionHistory::where('customer_id', $customer->id)
            ->orderBy('id', 'asc')
            ->get();

        return $this->sendResponse([
            'customer'           => $customer->only(['id', 'name', 'email', 'mobile', 'location', 'status']),
            'attributions'       => $attributions,
            'audit_logs'         => $auditLogs,
            'eligible_order_info'=> $this->attributionService->checkBuyerEligibleOrderStatus($customer->id),
        ], 'Customer attribution history retrieved successfully.');
    }
}

