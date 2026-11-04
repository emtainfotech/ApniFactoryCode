<?php

namespace App\Services;

use App\Models\Company;
use App\Models\SellerPermission;
use App\Models\SellerPermissionAuditLog;
use App\Models\SellerRole;
use App\Models\SellerStaff;
use App\Models\SellerStaffPermission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SellerPermissionService
{
    /**
     * Resolve the company for a given authenticated user.
     * Returns null if user has no seller company association.
     */
    public function resolveCompany(User $user): ?Company
    {
        // If user owns a company, return it
        if ($company = $user->company) {
            return $company;
        }

        // If user is a staff member, return their company
        $staff = $user->sellerStaff()->with('company')->first();
        return $staff?->company;
    }

    /**
     * Get the SellerStaff record for a user within a specific company.
     */
    public function getStaff(User $user, int $companyId): ?SellerStaff
    {
        return SellerStaff::where('company_id', $companyId)
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * Check if the acting user can manage staff in the given company.
     * Only seller owners, admins, or staff with staff management permission can manage.
     */
    public function canManageStaff(User $actingUser, int $companyId): bool
    {
        if ($actingUser->isAdmin()) {
            return true;
        }

        // Seller owner (company belongs to this user)
        if ($actingUser->company && $actingUser->company->id === $companyId) {
            return true;
        }

        // Seller admin staff or staff with staff.invite / staff.manage_permissions
        $staff = $this->getStaff($actingUser, $companyId);
        if (!$staff || !$staff->is_active) {
            return false;
        }

        if ($staff->role && in_array($staff->role->name, ['seller_owner', 'seller_admin'])) {
            return true;
        }

        return $staff->hasPermission('staff.invite') || $staff->hasPermission('staff.assign_role');
    }

    /**
     * Invite/create a new staff user and add them to a company.
     */
    public function inviteStaff(
        User $actingUser,
        int $companyId,
        array $data
    ): SellerStaff {
        if (!$this->canManageStaff($actingUser, $companyId)) {
            throw ValidationException::withMessages(['auth' => 'Unauthorized to manage staff for this company.']);
        }

        return DB::transaction(function () use ($actingUser, $companyId, $data) {
            // Find or create user account for the new staff member
            $user = User::where('email', $data['email'])->first();
            if (!$user) {
                $user = User::create([
                    'name'     => $data['name'],
                    'email'    => $data['email'],
                    'password' => Hash::make($data['password'] ?? Str::random(16)),
                ]);
            }

            if (SellerStaff::where('company_id', $companyId)->where('user_id', $user->id)->exists()) {
                throw ValidationException::withMessages(['email' => 'This user is already a staff member of this company.']);
            }

            $roleName = $data['role'] ?? 'readonly_staff';
            $this->preventPrivilegeEscalation($actingUser, $companyId, $roleName);

            $role = SellerRole::where('name', $roleName)->firstOrFail();

            $staff = SellerStaff::create([
                'company_id'    => $companyId,
                'user_id'       => $user->id,
                'seller_role_id'=> $role->id,
                'invited_by'    => $actingUser->id,
                'is_active'     => true,
                'activated_at'  => now(),
            ]);

            SellerPermissionAuditLog::log(
                $companyId, $actingUser->id, 'staff.invited',
                $user->id, $role->name,
                null, ['user_id' => $user->id, 'role' => $role->name]
            );

            return $staff;
        });
    }

    /**
     * Assign a role to a staff member.
     */
    public function assignRole(User $actingUser, int $staffId, string $roleName): SellerStaff
    {
        $staff = SellerStaff::with('role')->findOrFail($staffId);

        if (!$this->canManageStaff($actingUser, $staff->company_id)) {
            abort(403, 'Unauthorized.');
        }

        // Prevent privilege escalation: staff cannot assign roles ≥ their own level
        $this->preventPrivilegeEscalation($actingUser, $staff->company_id, $roleName);

        $role = SellerRole::where('name', $roleName)->firstOrFail();
        $old = $staff->role->name ?? null;
        $staff->update(['seller_role_id' => $role->id]);

        SellerPermissionAuditLog::log(
            $staff->company_id, $actingUser->id, 'role.assigned',
            $staff->user_id, $roleName,
            ['role' => $old], ['role' => $roleName]
        );

        return $staff->fresh();
    }

    /**
     * Grant or deny individual permissions for a staff member.
     */
    public function updateStaffPermissions(User $actingUser, int $staffId, array $permissions): void
    {
        $staff = SellerStaff::findOrFail($staffId);

        if (!$this->canManageStaff($actingUser, $staff->company_id)) {
            abort(403, 'Unauthorized.');
        }

        DB::transaction(function () use ($actingUser, $staff, $permissions) {
            foreach ($permissions as $permName => $granted) {
                $perm = SellerPermission::where('name', $permName)->first();
                if (!$perm) continue;

                $override = SellerStaffPermission::firstOrNew([
                    'seller_staff_id'       => $staff->id,
                    'seller_permission_id'  => $perm->id,
                ]);
                $old = $override->exists ? $override->granted : null;
                $override->granted = (bool) $granted;
                $override->save();

                SellerPermissionAuditLog::log(
                    $staff->company_id, $actingUser->id, 'permission.' . ($granted ? 'granted' : 'denied'),
                    $staff->user_id, $permName,
                    ['granted' => $old], ['granted' => (bool) $granted]
                );
            }
        });
    }

    /**
     * Deactivate a staff member.
     */
    public function deactivateStaff(User $actingUser, int $staffId): SellerStaff
    {
        $staff = SellerStaff::findOrFail($staffId);

        if (!$this->canManageStaff($actingUser, $staff->company_id)) {
            abort(403, 'Unauthorized.');
        }

        $staff->update(['is_active' => false, 'deactivated_at' => now()]);

        SellerPermissionAuditLog::log(
            $staff->company_id, $actingUser->id, 'staff.deactivated',
            $staff->user_id
        );

        return $staff;
    }

    /**
     * Reactivate a staff member.
     */
    public function activateStaff(User $actingUser, int $staffId): SellerStaff
    {
        $staff = SellerStaff::findOrFail($staffId);

        if (!$this->canManageStaff($actingUser, $staff->company_id)) {
            abort(403, 'Unauthorized.');
        }

        $staff->update(['is_active' => true, 'activated_at' => now(), 'deactivated_at' => null]);

        SellerPermissionAuditLog::log(
            $staff->company_id, $actingUser->id, 'staff.activated',
            $staff->user_id
        );

        return $staff;
    }

    /**
     * Prevent privilege escalation.
     * Non-owner staff cannot assign 'seller_owner' or 'seller_admin' roles unless they are seller_admin.
     */
    private function preventPrivilegeEscalation(User $actingUser, int $companyId, string $targetRoleName): void
    {
        if ($actingUser->isAdmin()) {
            return;
        }

        $isOwner = $actingUser->company && $actingUser->company->id === $companyId;
        if ($isOwner) {
            return; // Owner has full authorization
        }

        $actingStaff = $this->getStaff($actingUser, $companyId);
        $actingRoleName = $actingStaff?->role?->name;

        $superRoles = ['seller_owner', 'seller_admin'];
        if (in_array($targetRoleName, $superRoles) && $actingRoleName !== 'seller_admin') {
            abort(403, 'You cannot assign this role.');
        }
    }
}

