<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\V2\BaseApiController;
use App\Models\Customer;
use App\Services\AgentOrderEligibilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuyerIncentiveProgressApiController extends BaseApiController
{
    public function __construct(
        protected AgentOrderEligibilityService $eligibilityService
    ) {
    }

    /**
     * GET /api/v2/buyer/incentive-progress
     * Buyer-facing non-financial eligible-order slot progress (0/3..3/3).
     * Never exposes internal agent payout secrets, other buyers' data, or seller records.
     */
    public function progress(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->sendError('Unauthenticated.', [], 401);
        }

        // Prevent pure Sellers or pure Field Agents from misusing buyer progress endpoint
        if (!$user->isAdmin() && ($user->isFieldAgent() || $user->isSellerOwner() || $user->sellerStaff()->exists())) {
            return $this->sendError('Forbidden: Buyer account required.', [], 403);
        }

        $resolvedCustomer = $user instanceof Customer
            ? $user
            : (Customer::where('email', $user->email)->first() ?? Customer::find($user->id));

        $ownCustomerId = $resolvedCustomer ? (int)$resolvedCustomer->id : (int)$user->id;
        $requestedCustomerId = $request->filled('customer_id') ? (int)$request->input('customer_id') : null;

        if ($requestedCustomerId && !$user->isAdmin() && $requestedCustomerId !== $ownCustomerId) {
            return $this->sendError('Forbidden: You cannot view another buyer\'s order progress.', [], 403);
        }

        $targetCustomerId = ($user->isAdmin() && $requestedCustomerId) ? $requestedCustomerId : $ownCustomerId;
        $progress = $this->eligibilityService->getBuyerEligibilityProgress($targetCustomerId);

        // Strip internal incentive block/rule statuses when exposing to buyers
        $safeOrders = collect($progress['orders'] ?? [])->map(function (array $o) {
            return [
                'order_no'           => $o['order_no'],
                'eligibility_status' => $o['eligibility_status'],
                'sequence_number'    => $o['sequence_number'],
                'seller_count'       => $o['seller_count'],
                'eligible_at'        => $o['eligible_at'],
            ];
        })->values()->all();

        return $this->sendResponse([
            'customer_id'        => $targetCustomerId,
            'available'          => $progress['available'],
            'eligible_count'     => $progress['eligible_count'],
            'pending_count'      => $progress['pending_count'],
            'remaining_slots'    => $progress['remaining_slots'] ?? 0,
            'scheme_order_limit' => $progress['scheme_order_limit'],
            'display'            => $progress['display'],
            'orders'             => $safeOrders,
        ], 'Buyer eligible-order progress retrieved successfully.');
    }
}

