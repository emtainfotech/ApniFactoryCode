<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\V2\BaseApiController;
use App\Services\BuyerAttributionService;
use App\Services\FieldAgentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentPortalApiController extends BaseApiController
{
    public function __construct(
        protected FieldAgentService $agentService,
        protected BuyerAttributionService $attributionService
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
}

