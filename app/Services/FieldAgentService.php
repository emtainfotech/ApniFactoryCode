<?php

namespace App\Services;

use App\Models\FieldAgent;
use App\Models\FieldAgentStatusHistory;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FieldAgentService
{
    protected function ensureAdmin(User $actor): void
    {
        if (!$actor->isAdmin()) {
            throw ValidationException::withMessages([
                'authorization' => ['Forbidden: Administrator authorization is required to manage field sales agents.'],
            ]);
        }
    }

    /**
     * Create a new Field Sales Agent profile using existing project auth conventions.
     */
    public function createAgent(array $data, User $admin): FieldAgent
    {
        $this->ensureAdmin($admin);

        $agentCode = !empty($data['agent_code'])
            ? strtoupper(trim((string)$data['agent_code']))
            : 'FSA-' . date('Y') . '-' . strtoupper(Str::random(5));

        if (FieldAgent::where('agent_code', $agentCode)->exists()) {
            throw ValidationException::withMessages([
                'agent_code' => ["Agent ID '{$agentCode}' already exists. Duplicate agent identifiers are not permitted."],
            ]);
        }

        $mobile = trim((string)$data['mobile']);
        if (FieldAgent::where('mobile', $mobile)->exists()) {
            throw ValidationException::withMessages([
                'mobile' => ["Mobile number '{$mobile}' is already registered to another field agent."],
            ]);
        }

        $email = !empty($data['email']) ? strtolower(trim((string)$data['email'])) : null;
        if ($email && FieldAgent::where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => ["Email '{$email}' is already registered to another field agent."],
            ]);
        }

        $status = $data['status'] ?? FieldAgent::STATUS_ACTIVE;
        if (!array_key_exists($status, config('marketplace_agents.agent_statuses', []))) {
            throw ValidationException::withMessages([
                'status' => ["Invalid agent status '{$status}'."],
            ]);
        }

        return DB::transaction(function () use ($data, $admin, $agentCode, $mobile, $email, $status) {
            $userId = $data['user_id'] ?? null;

            // Optionally link or provision a login user in the unified `users` auth table
            if (!$userId && !empty($data['create_user_account']) && $email) {
                $existingUser = User::where('email', $email)->first();
                if ($existingUser) {
                    if ($existingUser->isFieldAgent()) {
                        throw ValidationException::withMessages([
                            'email' => ['This user account is already linked to another field agent profile.'],
                        ]);
                    }
                    $userId = $existingUser->id;
                } else {
                    $newUser = User::create([
                        'name'     => trim((string)$data['name']),
                        'email'    => $email,
                        'password' => Hash::make($data['password'] ?? Str::random(16)),
                    ]);
                    $userId = $newUser->id;
                }
            }

            if ($userId && FieldAgent::where('user_id', $userId)->exists()) {
                throw ValidationException::withMessages([
                    'user_id' => ['Selected user account is already linked to another field agent.'],
                ]);
            }

            $payoutDetails = $this->sanitizePayoutDetails($data['payout_details'] ?? null);

            $agent = FieldAgent::create([
                'user_id'            => $userId,
                'agent_code'         => $agentCode,
                'name'               => trim((string)$data['name']),
                'email'              => $email,
                'mobile'             => $mobile,
                'alternate_mobile'   => !empty($data['alternate_mobile']) ? trim((string)$data['alternate_mobile']) : null,
                'territory'          => trim((string)$data['territory']),
                'territory_city'     => !empty($data['territory_city']) ? trim((string)$data['territory_city']) : null,
                'territory_state'    => !empty($data['territory_state']) ? trim((string)$data['territory_state']) : null,
                'status'             => $status,
                'joining_date'       => $data['joining_date'] ?? now()->toDateString(),
                'supervisor_user_id' => $data['supervisor_user_id'] ?? null,
                'supervisor_name'    => !empty($data['supervisor_name']) ? trim((string)$data['supervisor_name']) : null,
                'payout_details'     => $payoutDetails,
                'kyc_status'         => $data['kyc_status'] ?? FieldAgent::KYC_NOT_SUBMITTED,
                'contract_status'    => $data['contract_status'] ?? 'not_signed',
                'kyc_reference'      => $data['kyc_reference'] ?? null,
                'notes'              => $data['notes'] ?? null,
                'deactivated_at'     => in_array($status, [FieldAgent::STATUS_INACTIVE, FieldAgent::STATUS_SUSPENDED], true) ? now() : null,
                'created_by_user_id' => $admin->id,
                'updated_by_user_id' => $admin->id,
            ]);

            FieldAgentStatusHistory::logAction(
                $agent,
                'created',
                $admin,
                null,
                $agent->status,
                $data['reason'] ?? 'Field sales agent profile created.',
                null,
                [
                    'agent_code'      => $agent->agent_code,
                    'territory'       => $agent->territory,
                    'status'          => $agent->status,
                    'kyc_status'      => $agent->kyc_status,
                    'contract_status' => $agent->contract_status,
                ]
            );

            return $agent->refresh();
        });
    }

    /**
     * Update an existing Field Sales Agent profile cleanly with full audit logging.
     */
    public function updateAgent(FieldAgent $agent, array $data, User $admin, ?string $reason = null): FieldAgent
    {
        $this->ensureAdmin($admin);

        if (!empty($data['agent_code'])) {
            $newCode = strtoupper(trim((string)$data['agent_code']));
            if (FieldAgent::where('agent_code', $newCode)->where('id', '!=', $agent->id)->exists()) {
                throw ValidationException::withMessages([
                    'agent_code' => ["Agent ID '{$newCode}' is already assigned to another agent."],
                ]);
            }
            $data['agent_code'] = $newCode;
        }

        if (!empty($data['mobile'])) {
            $newMobile = trim((string)$data['mobile']);
            if (FieldAgent::where('mobile', $newMobile)->where('id', '!=', $agent->id)->exists()) {
                throw ValidationException::withMessages([
                    'mobile' => ["Mobile number '{$newMobile}' is already assigned to another agent."],
                ]);
            }
            $data['mobile'] = $newMobile;
        }

        if (!empty($data['email'])) {
            $newEmail = strtolower(trim((string)$data['email']));
            if (FieldAgent::where('email', $newEmail)->where('id', '!=', $agent->id)->exists()) {
                throw ValidationException::withMessages([
                    'email' => ["Email '{$newEmail}' is already assigned to another agent."],
                ]);
            }
            $data['email'] = $newEmail;
        }

        return DB::transaction(function () use ($agent, $data, $admin, $reason) {
            $prevStatus = $agent->status;
            $prevSnapshot = [
                'name'            => $agent->name,
                'mobile'          => $agent->mobile,
                'territory'       => $agent->territory,
                'status'          => $agent->status,
                'kyc_status'      => $agent->kyc_status,
                'contract_status' => $agent->contract_status,
            ];

            $updatePayload = array_intersect_key($data, array_flip([
                'agent_code',
                'name',
                'email',
                'mobile',
                'alternate_mobile',
                'territory',
                'territory_city',
                'territory_state',
                'joining_date',
                'supervisor_user_id',
                'supervisor_name',
                'kyc_status',
                'contract_status',
                'kyc_reference',
                'notes',
            ]));

            if (array_key_exists('payout_details', $data)) {
                $updatePayload['payout_details'] = $this->sanitizePayoutDetails($data['payout_details']);
            }

            if (!empty($data['status']) && $data['status'] !== $prevStatus) {
                $updatePayload['status'] = $data['status'];
                if (in_array($data['status'], [FieldAgent::STATUS_INACTIVE, FieldAgent::STATUS_SUSPENDED], true)) {
                    $updatePayload['deactivated_at'] = now();
                    $updatePayload['deactivation_reason'] = $reason ?? $data['deactivation_reason'] ?? null;
                } elseif ($data['status'] === FieldAgent::STATUS_ACTIVE) {
                    $updatePayload['deactivated_at'] = null;
                    $updatePayload['deactivation_reason'] = null;
                }
            }

            $updatePayload['updated_by_user_id'] = $admin->id;
            $agent->update($updatePayload);

            $action = ($agent->status !== $prevStatus)
                ? ($agent->status === FieldAgent::STATUS_ACTIVE ? 'activated' : 'status_changed')
                : 'updated';

            FieldAgentStatusHistory::logAction(
                $agent,
                $action,
                $admin,
                $prevStatus,
                $agent->status,
                $reason ?? 'Agent profile updated by administrator.',
                $prevSnapshot,
                [
                    'name'            => $agent->name,
                    'mobile'          => $agent->mobile,
                    'territory'       => $agent->territory,
                    'status'          => $agent->status,
                    'kyc_status'      => $agent->kyc_status,
                    'contract_status' => $agent->contract_status,
                ]
            );

            return $agent->refresh();
        });
    }

    /**
     * Activate, deactivate, or suspend a Field Sales Agent while preserving attribution history.
     */
    public function changeStatus(FieldAgent $agent, string $newStatus, User $admin, ?string $reason = null): FieldAgent
    {
        $this->ensureAdmin($admin);

        if (!array_key_exists($newStatus, config('marketplace_agents.agent_statuses', []))) {
            throw ValidationException::withMessages([
                'status' => ["Unsupported agent status '{$newStatus}'."],
            ]);
        }

        if ($agent->status === $newStatus) {
            return $agent;
        }

        return DB::transaction(function () use ($agent, $newStatus, $admin, $reason) {
            $prevStatus = $agent->status;
            $isInactive = in_array($newStatus, [FieldAgent::STATUS_INACTIVE, FieldAgent::STATUS_SUSPENDED], true);

            $agent->update([
                'status'              => $newStatus,
                'deactivated_at'      => $isInactive ? now() : null,
                'deactivation_reason' => $isInactive ? $reason : null,
                'updated_by_user_id'  => $admin->id,
            ]);

            $actionName = match ($newStatus) {
                FieldAgent::STATUS_ACTIVE    => 'activated',
                FieldAgent::STATUS_INACTIVE  => 'deactivated',
                FieldAgent::STATUS_SUSPENDED => 'suspended',
                default                      => 'status_changed',
            };

            FieldAgentStatusHistory::logAction(
                $agent,
                $actionName,
                $admin,
                $prevStatus,
                $newStatus,
                $reason ?? "Agent status changed to {$newStatus}."
            );

            return $agent->refresh();
        });
    }

    /**
     * Filter and search agents for Admin panel / API.
     */
    public function listAgents(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = FieldAgent::query()
            ->withCount(['activeAttributions', 'attributions'])
            ->with(['supervisor:id,name,email', 'createdBy:id,name,email'])
            ->orderBy('id', 'desc');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['kyc_status'])) {
            $query->where('kyc_status', $filters['kyc_status']);
        }

        if (!empty($filters['territory'])) {
            $territory = trim((string)$filters['territory']);
            $query->where(function ($q) use ($territory) {
                $q->where('territory', 'like', "%{$territory}%")
                  ->orWhere('territory_city', 'like', "%{$territory}%")
                  ->orWhere('territory_state', 'like', "%{$territory}%");
            });
        }

        if (!empty($filters['search'])) {
            $search = trim((string)$filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('agent_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('territory', 'like', "%{$search}%");
            });
        }

        return $query->paginate(min(max(1, $perPage), 100));
    }

    /**
     * Resolve authenticated logged-in agent strictly by user_id.
     */
    public function resolveAgentForUser(User $user): ?FieldAgent
    {
        return FieldAgent::where('user_id', $user->id)->first();
    }

    /**
     * Aggregate summary KPI metrics for Admin console.
     */
    public function getAdminMetrics(): array
    {
        return [
            'total_agents'    => FieldAgent::count(),
            'active_agents'   => FieldAgent::where('status', FieldAgent::STATUS_ACTIVE)->count(),
            'inactive_agents' => FieldAgent::whereIn('status', [FieldAgent::STATUS_INACTIVE, FieldAgent::STATUS_SUSPENDED])->count(),
            'pending_agents'  => FieldAgent::where('status', FieldAgent::STATUS_PENDING)->count(),
        ];
    }

    protected function sanitizePayoutDetails(?array $details): ?array
    {
        if (empty($details)) {
            return null;
        }

        return array_filter([
            'account_holder_name' => isset($details['account_holder_name']) ? trim((string)$details['account_holder_name']) : null,
            'bank_name'           => isset($details['bank_name']) ? trim((string)$details['bank_name']) : null,
            'account_number'      => isset($details['account_number']) ? trim((string)$details['account_number']) : null,
            'ifsc_code'           => isset($details['ifsc_code']) ? strtoupper(trim((string)$details['ifsc_code'])) : null,
            'upi_id'              => isset($details['upi_id']) ? trim((string)$details['upi_id']) : null,
        ], fn ($v) => $v !== null && $v !== '');
    }
}

