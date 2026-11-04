<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SellerPermission;
use App\Models\SellerPermissionAuditLog;
use App\Models\SellerRole;
use App\Models\SellerStaff;
use App\Services\SellerPermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SellerStaffController extends Controller
{
    public function __construct(private SellerPermissionService $service)
    {
    }

    /**
     * GET /api/v2/seller/staff
     * List staff members for the authenticated seller's company.
     */
    public function index(Request $request): JsonResponse
    {
        $user    = $request->user();
        $company = $this->service->resolveCompany($user);

        if (!$company) {
            return response()->json(['status' => false, 'message' => 'No company found.'], 403);
        }

        $staff = SellerStaff::with(['user', 'role'])
            ->where('company_id', $company->id)
            ->get()
            ->map(fn($s) => $this->formatStaff($s));

        return response()->json(['status' => true, 'data' => $staff]);
    }

    /**
     * POST /api/v2/seller/staff/invite
     * Invite/create a new staff member.
     */
    public function invite(Request $request): JsonResponse
    {
        $user    = $request->user();
        $company = $this->service->resolveCompany($user);

        if (!$company) {
            return response()->json(['status' => false, 'message' => 'No company found.'], 403);
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'role'     => ['required', Rule::exists('seller_roles', 'name')],
            'password' => 'nullable|string|min:8',
        ]);

        $staff = $this->service->inviteStaff($user, $company->id, $validated);

        return response()->json([
            'status'  => true,
            'message' => 'Staff member invited successfully.',
            'data'    => $this->formatStaff($staff->load(['user', 'role'])),
        ], 201);
    }

    /**
     * GET /api/v2/seller/staff/{id}
     * View a specific staff member.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user    = $request->user();
        $company = $this->service->resolveCompany($user);

        if (!$company) {
            return response()->json(['status' => false, 'message' => 'No company found.'], 403);
        }

        // IDOR prevention: always filter by company_id from authenticated session
        $staff = SellerStaff::with(['user', 'role', 'overridePermissions.permission'])
            ->where('company_id', $company->id)
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data'   => array_merge($this->formatStaff($staff), [
                'effective_permissions' => $staff->effectivePermissions(),
            ]),
        ]);
    }

    /**
     * PATCH /api/v2/seller/staff/{id}/role
     * Assign a role to a staff member.
     */
    public function assignRole(Request $request, int $id): JsonResponse
    {
        $user    = $request->user();
        $company = $this->service->resolveCompany($user);

        if (!$company) {
            return response()->json(['status' => false, 'message' => 'No company found.'], 403);
        }

        $validated = $request->validate([
            'role' => ['required', Rule::exists('seller_roles', 'name')],
        ]);

        // IDOR prevention
        $staff = SellerStaff::where('company_id', $company->id)->findOrFail($id);
        $updated = $this->service->assignRole($user, $staff->id, $validated['role']);

        return response()->json([
            'status'  => true,
            'message' => 'Role assigned successfully.',
            'data'    => $this->formatStaff($updated->load(['user', 'role'])),
        ]);
    }

    /**
     * PATCH /api/v2/seller/staff/{id}/permissions
     * Grant or deny individual permissions.
     */
    public function updatePermissions(Request $request, int $id): JsonResponse
    {
        $user    = $request->user();
        $company = $this->service->resolveCompany($user);

        if (!$company) {
            return response()->json(['status' => false, 'message' => 'No company found.'], 403);
        }

        $validated = $request->validate([
            'permissions'   => 'required|array',
            'permissions.*' => 'boolean',
        ]);

        // IDOR prevention
        $staff = SellerStaff::where('company_id', $company->id)->findOrFail($id);
        $this->service->updateStaffPermissions($user, $staff->id, $validated['permissions']);

        return response()->json(['status' => true, 'message' => 'Permissions updated successfully.']);
    }

    /**
     * DELETE /api/v2/seller/staff/{id}
     * Deactivate a staff member.
     */
    public function deactivate(Request $request, int $id): JsonResponse
    {
        $user    = $request->user();
        $company = $this->service->resolveCompany($user);

        if (!$company) {
            return response()->json(['status' => false, 'message' => 'No company found.'], 403);
        }

        $staff = SellerStaff::where('company_id', $company->id)->findOrFail($id);
        $this->service->deactivateStaff($user, $staff->id);

        return response()->json(['status' => true, 'message' => 'Staff member deactivated.']);
    }

    /**
     * PATCH /api/v2/seller/staff/{id}/activate
     * Reactivate a staff member.
     */
    public function activate(Request $request, int $id): JsonResponse
    {
        $user    = $request->user();
        $company = $this->service->resolveCompany($user);

        if (!$company) {
            return response()->json(['status' => false, 'message' => 'No company found.'], 403);
        }

        $staff = SellerStaff::where('company_id', $company->id)->findOrFail($id);
        $this->service->activateStaff($user, $staff->id);

        return response()->json(['status' => true, 'message' => 'Staff member activated.']);
    }

    /**
     * GET /api/v2/seller/staff/{id}/activity
     * View activity log for a staff member.
     */
    public function activity(Request $request, int $id): JsonResponse
    {
        $user    = $request->user();
        $company = $this->service->resolveCompany($user);

        if (!$company) {
            return response()->json(['status' => false, 'message' => 'No company found.'], 403);
        }

        $staff = SellerStaff::where('company_id', $company->id)->findOrFail($id);

        $logs = SellerPermissionAuditLog::where('company_id', $company->id)
            ->where('target_user_id', $staff->user_id)
            ->with('actingUser')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return response()->json(['status' => true, 'data' => $logs]);
    }

    /**
     * GET /api/v2/seller/permissions/matrix
     * Return the permission matrix for the authenticated user in their company.
     */
    public function permissionMatrix(Request $request): JsonResponse
    {
        $user    = $request->user();
        $company = $this->service->resolveCompany($user);

        if (!$company) {
            return response()->json(['status' => false, 'message' => 'No company found.'], 403);
        }

        $allPermissions = SellerPermission::orderBy('module')->orderBy('action')->get()
            ->groupBy('module')
            ->map(fn($group) => $group->map(fn($p) => [
                'name'    => $p->name,
                'label'   => $p->label,
                'granted' => $user->hasSellerPermission($p->name),
            ]));

        $allRoles = SellerRole::with('permissions')->get()->map(fn($r) => [
            'name'        => $r->name,
            'label'       => $r->label,
            'permissions' => $r->permissions->pluck('name'),
        ]);

        return response()->json([
            'status' => true,
            'data'   => [
                'my_permissions' => $allPermissions,
                'roles'          => $allRoles,
            ],
        ]);
    }

    /**
     * GET /api/v2/seller/roles
     * List all available seller roles.
     */
    public function roles(): JsonResponse
    {
        $roles = SellerRole::with('permissions')->get()->map(fn($r) => [
            'name'        => $r->name,
            'label'       => $r->label,
            'description' => $r->description,
            'is_system'   => $r->is_system,
            'permissions' => $r->permissions->pluck('name'),
        ]);

        return response()->json(['status' => true, 'data' => $roles]);
    }

    private function formatStaff(SellerStaff $staff): array
    {
        return [
            'id'             => $staff->id,
            'user_id'        => $staff->user_id,
            'name'           => $staff->user->name ?? null,
            'email'          => $staff->user->email ?? null,
            'role'           => $staff->role?->name,
            'role_label'     => $staff->role?->label,
            'is_active'      => $staff->is_active,
            'activated_at'   => $staff->activated_at,
            'deactivated_at' => $staff->deactivated_at,
            'created_at'     => $staff->created_at,
        ];
    }
}

