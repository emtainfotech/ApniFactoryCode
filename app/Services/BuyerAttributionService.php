<?php

namespace App\Services;

use App\Jobs\SendAgentAttributionNotificationJob;
use App\Models\BuyerAgentAttribution;
use App\Models\BuyerAttributionHistory;
use App\Models\Customer;
use App\Models\FieldAgent;
use App\Models\Order;
use App\Models\SellerFulfilment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BuyerAttributionService
{
    protected function ensureAdmin(User $actor): void
    {
        if (!$actor->isAdmin()) {
            throw ValidationException::withMessages([
                'authorization' => ['Forbidden: Only authorized administrators can manage buyer attributions.'],
            ]);
        }
    }

    /**
     * Check whether a buyer/dealer has already generated an eligible/paid order.
     * Note: Does NOT mutate any order records and does NOT calculate incentives (Phase 6.2 scope).
     */
    public function checkBuyerEligibleOrderStatus(int $customerId): array
    {
        $paidOrdersCount = Order::where('customer_id', $customerId)
            ->whereRaw("LOWER(COALESCE(payment_status, '')) = 'paid'")
            ->count();

        $deliveredFulfilmentsCount = SellerFulfilment::where('customer_id', $customerId)
            ->whereIn('fulfilment_status', [
                SellerFulfilment::STATUS_BUYER_ACKNOWLEDGED,
                SellerFulfilment::STATUS_DELIVERED,
            ])
            ->count();

        return [
            'has_eligible_order'          => ($paidOrdersCount > 0 || $deliveredFulfilmentsCount > 0),
            'paid_orders_count'           => $paidOrdersCount,
            'delivered_fulfilments_count' => $deliveredFulfilmentsCount,
        ];
    }

    /**
     * Attribute an active Field Sales Agent to a buyer/dealer account.
     * Supports idempotency, conflict prevention, approval workflow, and transaction safety.
     */
    public function createAttribution(array $data, User $admin): BuyerAgentAttribution
    {
        $this->ensureAdmin($admin);

        // 1. Idempotency protection on API retries
        $idempotencyKey = !empty($data['idempotency_key']) ? trim((string)$data['idempotency_key']) : null;
        if ($idempotencyKey) {
            $existingIdem = BuyerAgentAttribution::where('idempotency_key', $idempotencyKey)->first();
            if ($existingIdem) {
                return $existingIdem->load(['customer', 'agent', 'history']);
            }
        }

        // 2. Validate buyer and agent exist
        $customer = Customer::findOrFail((int)$data['customer_id']);
        $agent = FieldAgent::findOrFail((int)$data['field_agent_id']);

        // 3. Inactive / pending / suspended agents cannot receive new attributions
        if (!$agent->isActive()) {
            throw ValidationException::withMessages([
                'field_agent_id' => ["Agent '{$agent->agent_code}' is currently '{$agent->status}'. Only active, authorized agents may receive new buyer attributions."],
            ]);
        }

        $requireApproval = array_key_exists('require_approval', $data)
            ? (bool)$data['require_approval']
            : (bool)config('marketplace_agents.attribution_requires_approval', false);

        try {
            $attribution = DB::transaction(function () use ($customer, $agent, $admin, $data, $idempotencyKey, $requireApproval) {
                // Re-check inside lock to prevent race-condition duplicates
                $existingLock = BuyerAgentAttribution::where('active_customer_lock', $customer->id)
                    ->lockForUpdate()
                    ->first();

                if ($existingLock) {
                    if ((int)$existingLock->field_agent_id === (int)$agent->id) {
                        throw ValidationException::withMessages([
                            'customer_id' => ["Duplicate attribution rejected: Buyer '{$customer->name}' already has an active or pending attribution with Agent '{$agent->agent_code}'."],
                        ]);
                    }

                    // Notify admin conflict channel (best-effort)
                    $this->notifyConflictAttempt($existingLock, $agent, $admin);

                    throw ValidationException::withMessages([
                        'customer_id' => ["Conflicting attribution blocked: Buyer '{$customer->name}' is already attributed to another agent (#{$existingLock->attribution_ref}). Use the authorized reassignment workflow instead."],
                    ]);
                }

                $status = $requireApproval ? BuyerAgentAttribution::STATUS_PENDING : BuyerAgentAttribution::STATUS_ACTIVE;
                $approvalStatus = $requireApproval ? BuyerAgentAttribution::APPROVAL_PENDING : BuyerAgentAttribution::APPROVAL_APPROVED;
                $ref = 'ATTR-' . date('Ymd') . '-' . strtoupper(Str::random(6));

                $record = BuyerAgentAttribution::create([
                    'attribution_ref'       => $ref,
                    'customer_id'           => $customer->id,
                    'field_agent_id'        => $agent->id,
                    'active_customer_lock'  => $customer->id,
                    'status'                => $status,
                    'approval_status'       => $approvalStatus,
                    'attribution_source'    => $data['attribution_source'] ?? BuyerAgentAttribution::SOURCE_ADMIN_LINKING,
                    'evidence_reference'    => $data['evidence_reference'] ?? null,
                    'remarks'               => $data['remarks'] ?? null,
                    'admin_notes'           => $data['admin_notes'] ?? null,
                    'attributed_by_user_id' => $admin->id,
                    'attributed_at'         => now(),
                    'approved_by_user_id'   => $requireApproval ? null : $admin->id,
                    'approved_at'           => $requireApproval ? null : now(),
                    'eligible_order_limit'  => (int)config('marketplace_agents.eligible_orders_limit', 3),
                    'idempotency_key'       => $idempotencyKey,
                ]);

                BuyerAttributionHistory::logAction(
                    $record,
                    $requireApproval ? 'attribution_pending_created' : 'attribution_created',
                    $admin,
                    null,
                    $status,
                    null,
                    $agent->id,
                    $data['remarks'] ?? 'Primary field agent attributed to buyer account.',
                    $data['evidence_reference'] ?? null,
                    [
                        'attribution_ref' => $ref,
                        'approval_status' => $approvalStatus,
                        'source'          => $record->attribution_source,
                    ]
                );

                return $record;
            });
        } catch (QueryException $e) {
            if (str_contains(strtolower($e->getMessage()), 'unique') || str_contains(strtolower($e->getMessage()), 'duplicate')) {
                throw ValidationException::withMessages([
                    'customer_id' => ['Concurrent or duplicate attribution conflict detected for this buyer account.'],
                ]);
            }
            throw $e;
        }

        // Dispatch In-App + Email notification cleanly after DB commit
        $this->dispatchAttributionNotifications($attribution, $requireApproval ? 'pending_approval' : 'approved');

        return $attribution->load(['customer', 'agent', 'attributedBy', 'approvedBy', 'history']);
    }

    /**
     * Approve a pending attribution request.
     */
    public function approveAttribution(BuyerAgentAttribution $attribution, User $admin, ?string $notes = null): BuyerAgentAttribution
    {
        $this->ensureAdmin($admin);

        if ($attribution->approval_status !== BuyerAgentAttribution::APPROVAL_PENDING) {
            throw ValidationException::withMessages([
                'approval_status' => ["Attribution #{$attribution->attribution_ref} is not awaiting approval (current: {$attribution->approval_status})."],
            ]);
        }

        $agent = $attribution->agent()->firstOrFail();
        if (!$agent->isActive()) {
            throw ValidationException::withMessages([
                'field_agent_id' => ["Cannot approve attribution: Agent '{$agent->agent_code}' is currently '{$agent->status}'."],
            ]);
        }

        $updated = DB::transaction(function () use ($attribution, $admin, $notes) {
            $prevStatus = $attribution->status;

            $attribution->update([
                'status'              => BuyerAgentAttribution::STATUS_ACTIVE,
                'approval_status'     => BuyerAgentAttribution::APPROVAL_APPROVED,
                'approved_by_user_id' => $admin->id,
                'approved_at'         => now(),
                'admin_notes'         => $notes !== null ? trim(($attribution->admin_notes ? $attribution->admin_notes . "\n" : '') . $notes) : $attribution->admin_notes,
            ]);

            BuyerAttributionHistory::logAction(
                $attribution,
                'attribution_approved',
                $admin,
                $prevStatus,
                BuyerAgentAttribution::STATUS_ACTIVE,
                null,
                $attribution->field_agent_id,
                $notes ?? 'Pending attribution approved by administrator.',
                $attribution->evidence_reference
            );

            return $attribution->refresh();
        });

        $this->dispatchAttributionNotifications($updated, 'approved');

        return $updated->load(['customer', 'agent', 'history']);
    }

    /**
     * Reject a pending attribution request while preserving the record and history.
     */
    public function rejectAttribution(BuyerAgentAttribution $attribution, User $admin, string $reason): BuyerAgentAttribution
    {
        $this->ensureAdmin($admin);

        if ($attribution->approval_status !== BuyerAgentAttribution::APPROVAL_PENDING) {
            throw ValidationException::withMessages([
                'approval_status' => ["Attribution #{$attribution->attribution_ref} is not pending approval."],
            ]);
        }

        $updated = DB::transaction(function () use ($attribution, $admin, $reason) {
            $prevStatus = $attribution->status;

            $attribution->update([
                'status'               => BuyerAgentAttribution::STATUS_REJECTED,
                'approval_status'      => BuyerAgentAttribution::APPROVAL_REJECTED,
                'active_customer_lock' => null, // Free buyer for valid attribution while preserving row
                'rejected_by_user_id'  => $admin->id,
                'rejected_at'          => now(),
                'rejection_reason'     => $reason,
            ]);

            BuyerAttributionHistory::logAction(
                $attribution,
                'attribution_rejected',
                $admin,
                $prevStatus,
                BuyerAgentAttribution::STATUS_REJECTED,
                $attribution->field_agent_id,
                null,
                $reason,
                $attribution->evidence_reference
            );

            return $attribution->refresh();
        });

        $this->dispatchAttributionNotifications($updated, 'rejected');

        return $updated->load(['customer', 'agent', 'history']);
    }

    /**
     * Authorized documented reassignment workflow.
     * Enforces BRD rule: blocked after the first eligible order unless an approved exception policy exists.
     */
    public function reassignAttribution(Customer $customer, FieldAgent $newAgent, User $admin, array $data): BuyerAgentAttribution
    {
        $this->ensureAdmin($admin);

        // Idempotency guard for retries
        $idempotencyKey = !empty($data['idempotency_key']) ? trim((string)$data['idempotency_key']) : null;
        if ($idempotencyKey) {
            $existingIdem = BuyerAgentAttribution::where('idempotency_key', $idempotencyKey)->first();
            if ($existingIdem) {
                return $existingIdem->load(['customer', 'agent', 'reassignedFrom', 'history']);
            }
        }

        if (!$newAgent->isActive()) {
            throw ValidationException::withMessages([
                'new_field_agent_id' => ["Target agent '{$newAgent->agent_code}' is '{$newAgent->status}'. Reassignment requires an active agent."],
            ]);
        }

        $reason = trim((string)($data['reason'] ?? ''));
        if (empty($reason)) {
            throw ValidationException::withMessages([
                'reason' => ['A documented reassignment reason is required.'],
            ]);
        }

        // Pre-check current attribution and eligible order lock BEFORE opening rollbackable transaction
        // so blocked attempts can still be immutably logged in buyer_attribution_history!
        $current = BuyerAgentAttribution::where('active_customer_lock', $customer->id)->first();
        if (!$current) {
            throw ValidationException::withMessages([
                'customer_id' => ["Buyer '{$customer->name}' has no active attribution to reassign. Create a new attribution instead."],
            ]);
        }

        if ((int)$current->field_agent_id === (int)$newAgent->id) {
            throw ValidationException::withMessages([
                'new_field_agent_id' => ["Buyer '{$customer->name}' is already attributed to Agent '{$newAgent->agent_code}'."],
            ]);
        }

        $eligibility = $this->checkBuyerEligibleOrderStatus($customer->id);
        $exceptionPolicyAllowed = (bool)config('marketplace_agents.allow_reassignment_after_eligible_order_exception', false);
        $isExceptionRequested = !empty($data['is_exception_approved']) && !empty($data['exception_approval_ref']);

        if ($eligibility['has_eligible_order'] && !($exceptionPolicyAllowed && $isExceptionRequested)) {
            BuyerAttributionHistory::logAction(
                $current,
                'reassignment_blocked_eligible_order',
                $admin,
                $current->status,
                $current->status,
                $current->field_agent_id,
                $newAgent->id,
                "Blocked reassignment attempt ({$reason}): Buyer already has paid/eligible order(s).",
                $data['evidence_reference'] ?? null,
                $eligibility
            );

            throw ValidationException::withMessages([
                'eligible_order' => [
                    'Reassignment blocked: This buyer/dealer has already generated an eligible/paid order. Per BRD Section 12, reassignment is prohibited after the first eligible order unless an approved corporate exception policy is enabled and documented.',
                ],
            ]);
        }

        $previousAgent = $current->agent;

        $newAttribution = DB::transaction(function () use ($customer, $newAgent, $admin, $data, $reason, $idempotencyKey, $isExceptionRequested, $eligibility) {
            $lockedCurrent = BuyerAgentAttribution::where('active_customer_lock', $customer->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedCurrent) {
                throw ValidationException::withMessages([
                    'customer_id' => ['Attribution lock state changed concurrently. Please retry.'],
                ]);
            }

            $prevStatus = $lockedCurrent->status;
            $prevAgentId = $lockedCurrent->field_agent_id;

            // Release lock & preserve old record verbatim without overwriting customer/agent link
            $lockedCurrent->update([
                'status'               => BuyerAgentAttribution::STATUS_SUPERSEDED,
                'active_customer_lock' => null,
                'reassignment_reason'  => $reason,
            ]);

            $ref = 'ATTR-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            $replacement = BuyerAgentAttribution::create([
                'attribution_ref'                => $ref,
                'customer_id'                    => $customer->id,
                'field_agent_id'                 => $newAgent->id,
                'active_customer_lock'           => $customer->id,
                'status'                         => BuyerAgentAttribution::STATUS_ACTIVE,
                'approval_status'                => BuyerAgentAttribution::APPROVAL_APPROVED,
                'attribution_source'             => BuyerAgentAttribution::SOURCE_REASSIGNMENT,
                'evidence_reference'             => $data['evidence_reference'] ?? null,
                'remarks'                        => $reason,
                'admin_notes'                    => $data['admin_notes'] ?? null,
                'attributed_by_user_id'          => $admin->id,
                'attributed_at'                  => now(),
                'approved_by_user_id'            => $admin->id,
                'approved_at'                    => now(),
                'reassigned_from_attribution_id' => $lockedCurrent->id,
                'reassignment_reason'            => $reason,
                'is_exception_reassignment'      => $eligibility['has_eligible_order'] && $isExceptionRequested,
                'exception_approval_ref'         => $data['exception_approval_ref'] ?? null,
                'eligible_order_limit'           => (int)config('marketplace_agents.eligible_orders_limit', 3),
                'idempotency_key'                => $idempotencyKey,
            ]);

            $lockedCurrent->update([
                'superseded_by_attribution_id' => $replacement->id,
            ]);

            BuyerAttributionHistory::logAction(
                $lockedCurrent,
                'attribution_superseded',
                $admin,
                $prevStatus,
                BuyerAgentAttribution::STATUS_SUPERSEDED,
                $prevAgentId,
                $newAgent->id,
                $reason,
                $data['evidence_reference'] ?? null,
                ['superseded_by_attribution_id' => $replacement->id]
            );

            BuyerAttributionHistory::logAction(
                $replacement,
                'attribution_reassigned',
                $admin,
                null,
                BuyerAgentAttribution::STATUS_ACTIVE,
                $prevAgentId,
                $newAgent->id,
                $reason,
                $data['evidence_reference'] ?? null,
                [
                    'reassigned_from_attribution_id' => $lockedCurrent->id,
                    'is_exception_reassignment'      => $replacement->is_exception_reassignment,
                    'exception_approval_ref'         => $replacement->exception_approval_ref,
                ]
            );

            return $replacement;
        });

        // Notify new agent & previous agent via In-App + Email
        $this->dispatchAttributionNotifications($newAttribution, 'reassigned_to_new');
        if ($previousAgent && $previousAgent->user_id) {
            SendAgentAttributionNotificationJob::dispatch(
                (int)$previousAgent->user_id,
                'agent',
                "Buyer Attribution Reassigned: #{$current->attribution_ref}",
                "Buyer account '{$customer->name}' has been reassigned to another field representative by administrator approval.",
                [
                    'Attribution Reference' => $current->attribution_ref,
                    'Dealer Name'           => $customer->name,
                    'Reason'                => $reason,
                ],
                route('agent.dashboard'),
                "reassigned_prev_{$current->id}_{$newAttribution->id}"
            );
        }

        return $newAttribution->load(['customer', 'agent', 'reassignedFrom', 'history']);
    }

    /**
     * Paginated list of attributions for Admin panel & API.
     */
    public function listAttributions(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = BuyerAgentAttribution::with([
            'customer:id,name,email,mobile,location,status',
            'agent:id,agent_code,name,territory,status',
            'attributedBy:id,name,email',
            'approvedBy:id,name,email',
        ])->orderBy('id', 'desc');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['approval_status'])) {
            $query->where('approval_status', $filters['approval_status']);
        }

        if (!empty($filters['field_agent_id'])) {
            $query->where('field_agent_id', (int)$filters['field_agent_id']);
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', (int)$filters['customer_id']);
        }

        if (!empty($filters['search'])) {
            $term = trim((string)$filters['search']);
            $query->where(function ($q) use ($term) {
                $q->where('attribution_ref', 'like', "%{$term}%")
                  ->orWhere('evidence_reference', 'like', "%{$term}%")
                  ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$term}%")->orWhere('mobile', 'like', "%{$term}%"))
                  ->orWhereHas('agent', fn($a) => $a->where('agent_code', 'like', "%{$term}%")->orWhere('name', 'like', "%{$term}%"));
            });
        }

        return $query->paginate(min(max(1, $perPage), 100));
    }

    /**
     * Strictly scoped list of buyers attributed to the given FieldAgent.
     * Excludes admin_notes, confidential seller prices, commissions, and unrelated financials.
     */
    public function getAgentAssignedBuyers(FieldAgent $agent, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = BuyerAgentAttribution::where('field_agent_id', $agent->id)
            ->whereIn('status', [BuyerAgentAttribution::STATUS_ACTIVE, BuyerAgentAttribution::STATUS_PENDING])
            ->with(['customer:id,name,location,mobile,email,status,created_at'])
            ->orderBy('id', 'desc');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $term = trim((string)$filters['search']);
            $query->where(function ($q) use ($term) {
                $q->where('attribution_ref', 'like', "%{$term}%")
                  ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$term}%")->orWhere('location', 'like', "%{$term}%"));
            });
        }

        $paginator = $query->paginate(min(max(1, $perPage), 100));
        $paginator->getCollection()->transform(fn(BuyerAgentAttribution $attr) => $this->formatPermittedBuyerForAgent($attr));

        return $paginator;
    }

    /**
     * Retrieve single assigned buyer detail for agent dashboard/API, enforcing strict attribution ownership.
     */
    public function getAssignedBuyerForAgent(FieldAgent $agent, int $customerId): array
    {
        $attr = BuyerAgentAttribution::where('field_agent_id', $agent->id)
            ->where('customer_id', $customerId)
            ->whereIn('status', [BuyerAgentAttribution::STATUS_ACTIVE, BuyerAgentAttribution::STATUS_PENDING])
            ->with(['customer:id,name,location,mobile,email,status,created_at'])
            ->first();

        if (!$attr) {
            throw ValidationException::withMessages([
                'customer' => ['Forbidden: Buyer/dealer account is not assigned to your agent profile.'],
            ]);
        }

        return $this->formatPermittedBuyerForAgent($attr);
    }

    /**
     * Sanitized buyer payload permitted by BRD Section 12 access policy.
     * Shows eligibility progress as unavailable until Phase 6.2 eligibility rules are approved.
     */
    public function formatPermittedBuyerForAgent(BuyerAgentAttribution $attr): array
    {
        $customer = $attr->customer;
        $trackingEnabled = (bool)config('marketplace_agents.eligible_order_tracking_implemented', false);

        return [
            'attribution_id'     => $attr->id,
            'attribution_ref'    => $attr->attribution_ref,
            'attribution_status' => $attr->status,
            'approval_status'    => $attr->approval_status,
            'attribution_source' => $attr->attribution_source,
            'attributed_at'      => $attr->attributed_at ? $attr->attributed_at->toIso8601String() : null,
            'buyer'              => [
                'id'            => $customer?->id,
                'name'          => $customer?->name,
                'location'      => $customer?->location,
                'mobile'        => $customer?->mobile,
                'email'         => $customer?->email,
                'account_status'=> $customer?->status,
                'registered_at' => $customer?->created_at ? $customer->created_at->toIso8601String() : null,
            ],
            // Stage 5: Do NOT fabricate counts if order eligibility has not yet been implemented
            'eligible_order_progress' => [
                'available'           => $trackingEnabled,
                'display'             => $trackingEnabled ? '0/3' : 'Unavailable (Pending Phase 6.2 Eligible-Order Rules)',
                'scheme_order_limit'  => (int)$attr->eligible_order_limit,
            ],
        ];
    }

    public function getAdminAttributionMetrics(): array
    {
        return [
            'total_attributions'    => BuyerAgentAttribution::count(),
            'active_attributions'   => BuyerAgentAttribution::where('status', BuyerAgentAttribution::STATUS_ACTIVE)->count(),
            'pending_approvals'     => BuyerAgentAttribution::where('approval_status', BuyerAgentAttribution::APPROVAL_PENDING)->count(),
            'superseded_reassigned' => BuyerAgentAttribution::where('status', BuyerAgentAttribution::STATUS_SUPERSEDED)->count(),
            'rejected_attributions' => BuyerAgentAttribution::where('status', BuyerAgentAttribution::STATUS_REJECTED)->count(),
        ];
    }

    protected function dispatchAttributionNotifications(BuyerAgentAttribution $attribution, string $eventType): void
    {
        $attribution->loadMissing(['agent', 'customer']);
        $agent = $attribution->agent;
        $customer = $attribution->customer;

        if ($eventType === 'pending_approval') {
            $admins = User::all()->filter(fn(User $u) => $u->isAdmin());
            foreach ($admins as $adminUser) {
                SendAgentAttributionNotificationJob::dispatch(
                    (int)$adminUser->id,
                    'admin',
                    "Pending Buyer Attribution Approval: #{$attribution->attribution_ref}",
                    "Attribution request between Agent {$agent?->agent_code} and Buyer '{$customer?->name}' awaits administrator review.",
                    [
                        'Reference'   => $attribution->attribution_ref,
                        'Agent'       => "{$agent?->name} ({$agent?->agent_code})",
                        'Buyer'       => $customer?->name ?? "Customer #{$attribution->customer_id}",
                        'Territory'   => $agent?->territory ?? 'N/A',
                    ],
                    route('admin.attributions.show', $attribution->id),
                    "pending_{$attribution->id}_{$adminUser->id}"
                );
            }
            return;
        }

        if ($agent && $agent->user_id) {
            if (in_array($eventType, ['approved', 'reassigned_to_new'], true)) {
                SendAgentAttributionNotificationJob::dispatch(
                    (int)$agent->user_id,
                    'agent',
                    "Buyer Attributed to Your Portfolio: #{$attribution->attribution_ref}",
                    "Buyer/Dealer '{$customer?->name}' has been attributed to your Field Sales Agent profile ({$agent->agent_code}).",
                    [
                        'Reference' => $attribution->attribution_ref,
                        'Dealer'    => $customer?->name ?? "Customer #{$attribution->customer_id}",
                        'Location'  => $customer?->location ?? 'N/A',
                        'Source'    => ucfirst(str_replace('_', ' ', $attribution->attribution_source)),
                    ],
                    route('agent.dashboard'),
                    "{$eventType}_{$attribution->id}_{$agent->user_id}"
                );
            } elseif ($eventType === 'rejected') {
                SendAgentAttributionNotificationJob::dispatch(
                    (int)$agent->user_id,
                    'agent',
                    "Attribution Request Declined: #{$attribution->attribution_ref}",
                    "The pending buyer attribution request for '{$customer?->name}' was rejected by administrator review.",
                    [
                        'Reference' => $attribution->attribution_ref,
                        'Dealer'    => $customer?->name ?? "Customer #{$attribution->customer_id}",
                        'Reason'    => $attribution->rejection_reason ?? 'Rejected by policy',
                    ],
                    route('agent.dashboard'),
                    "rejected_{$attribution->id}_{$agent->user_id}"
                );
            }
        }
    }

    protected function notifyConflictAttempt(BuyerAgentAttribution $existing, FieldAgent $attemptedAgent, User $actingAdmin): void
    {
        $admins = User::all()->filter(fn(User $u) => $u->isAdmin() && $u->id !== $actingAdmin->id)->take(3);
        foreach ($admins as $adminUser) {
            SendAgentAttributionNotificationJob::dispatch(
                (int)$adminUser->id,
                'admin',
                "Attribution Conflict Alert: Customer #{$existing->customer_id}",
                "A conflicting attribution attempt was blocked for buyer #{$existing->customer_id} (already linked via {$existing->attribution_ref}).",
                [
                    'Existing Reference' => $existing->attribution_ref,
                    'Attempted Agent'    => $attemptedAgent->agent_code,
                    'Actor'              => $actingAdmin->email,
                ],
                route('admin.attributions.show', $existing->id),
                "conflict_{$existing->id}_{$attemptedAgent->id}_{$adminUser->id}"
            );
        }
    }
}

