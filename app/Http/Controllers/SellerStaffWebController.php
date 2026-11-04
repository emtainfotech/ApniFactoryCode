<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\SellerPermission;
use App\Models\SellerPermissionAuditLog;
use App\Models\SellerRole;
use App\Models\SellerStaff;
use App\Services\SellerPermissionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SellerStaffWebController extends Controller
{
    public function __construct(private SellerPermissionService $service)
    {
        $this->middleware('auth');
    }

    private function resolveCompanyOrAbort(): Company
    {
        $company = $this->service->resolveCompany(auth()->user());
        if (!$company) {
            abort(403, 'No seller company found.');
        }
        return $company;
    }

    /**
     * Staff listing page.
     */
    public function index()
    {
        $company = $this->resolveCompanyOrAbort();
        $staff = SellerStaff::with(['user', 'role'])
            ->where('company_id', $company->id)
            ->get();
        $roles = SellerRole::all();

        return view('seller.staff.index', compact('company', 'staff', 'roles'));
    }

    /**
     * Show invite form.
     */
    public function create()
    {
        $company = $this->resolveCompanyOrAbort();
        if (!$this->service->canManageStaff(auth()->user(), $company->id)) {
            abort(403, 'You do not have permission to invite staff.');
        }
        $roles = SellerRole::all();
        return view('seller.staff.create', compact('company', 'roles'));
    }

    /**
     * Store new staff member.
     */
    public function store(Request $request)
    {
        $company = $this->resolveCompanyOrAbort();

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'role'     => ['required', Rule::exists('seller_roles', 'name')],
            'password' => 'required|string|min:8|confirmed',
        ]);

        $this->service->inviteStaff(auth()->user(), $company->id, $validated);

        return redirect()->route('seller.staff.index')
            ->with('success', 'Staff member invited successfully.');
    }

    /**
     * Show staff member detail / permission editor.
     */
    public function show(int $id)
    {
        $company = $this->resolveCompanyOrAbort();
        $staff = SellerStaff::with(['user', 'role', 'overridePermissions.permission'])
            ->where('company_id', $company->id)
            ->findOrFail($id);
        $allPermissions = SellerPermission::orderBy('module')->orderBy('action')->get()->groupBy('module');
        $roles = SellerRole::all();
        $effectivePermissions = $staff->effectivePermissions();

        return view('seller.staff.show', compact('company', 'staff', 'allPermissions', 'roles', 'effectivePermissions'));
    }

    /**
     * Assign role.
     */
    public function assignRole(Request $request, int $id)
    {
        $company = $this->resolveCompanyOrAbort();
        $staff = SellerStaff::where('company_id', $company->id)->findOrFail($id);
        $validated = $request->validate(['role' => ['required', Rule::exists('seller_roles', 'name')]]);
        $this->service->assignRole(auth()->user(), $staff->id, $validated['role']);

        return back()->with('success', 'Role updated successfully.');
    }

    /**
     * Update individual permissions.
     */
    public function updatePermissions(Request $request, int $id)
    {
        $company = $this->resolveCompanyOrAbort();
        $staff = SellerStaff::where('company_id', $company->id)->findOrFail($id);

        $allPerms = SellerPermission::pluck('name')->toArray();
        $permissions = [];
        foreach ($allPerms as $perm) {
            $inputKey = 'perm_' . str_replace('.', '_', $perm);
            if ($request->has($inputKey)) {
                $permissions[$perm] = $request->boolean($inputKey);
            }
        }

        $this->service->updateStaffPermissions(auth()->user(), $staff->id, $permissions);

        return back()->with('success', 'Permissions updated successfully.');
    }

    /**
     * Deactivate staff member.
     */
    public function deactivate(int $id)
    {
        $company = $this->resolveCompanyOrAbort();
        $staff = SellerStaff::where('company_id', $company->id)->findOrFail($id);
        $this->service->deactivateStaff(auth()->user(), $staff->id);

        return back()->with('success', 'Staff member deactivated.');
    }

    /**
     * Activate staff member.
     */
    public function activate(int $id)
    {
        $company = $this->resolveCompanyOrAbort();
        $staff = SellerStaff::where('company_id', $company->id)->findOrFail($id);
        $this->service->activateStaff(auth()->user(), $staff->id);

        return back()->with('success', 'Staff member activated.');
    }

    /**
     * Activity log for a staff member.
     */
    public function activity(int $id)
    {
        $company = $this->resolveCompanyOrAbort();
        $staff = SellerStaff::with('user')->where('company_id', $company->id)->findOrFail($id);
        $logs = SellerPermissionAuditLog::where('company_id', $company->id)
            ->where('target_user_id', $staff->user_id)
            ->with('actingUser')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('seller.staff.activity', compact('company', 'staff', 'logs'));
    }
}

