<?php

namespace App\Http\Controllers;

use App\Models\TaxMaster;
use App\Services\TaxMasterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaxMasterWebController extends Controller
{
    public function __construct(
        protected TaxMasterService $taxMasterService
    ) {
    }

    /**
     * Display Tax Master listing and approval queue.
     */
    public function index(Request $request)
    {
        $query = TaxMaster::query()->with(['creator', 'approver']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('hsn_code', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->input('approval_status'));
        }

        $metrics = [
            'total_count'    => TaxMaster::count(),
            'approved_count' => TaxMaster::where('approval_status', TaxMaster::STATUS_APPROVED)->count(),
            'pending_count'  => TaxMaster::where('approval_status', TaxMaster::STATUS_PENDING)->count(),
            'rejected_count' => TaxMaster::where('approval_status', TaxMaster::STATUS_REJECTED)->count(),
        ];

        $records = $query->orderBy('hsn_code')->orderBy('version', 'desc')->paginate(20)->withQueryString();

        return view('admin.finance.tax_master.index', compact('records', 'metrics'));
    }

    /**
     * Store new Tax Master classification from web portal.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'hsn_code'         => 'required|string|max:16',
            'description'      => 'required|string|max:255',
            'gst_rate'         => 'required|numeric|min:0|max:100',
            'type'             => 'required|string|in:goods,services',
            'effective_from'   => 'required|date',
            'effective_to'     => 'nullable|date|after:effective_from',
            'source_reference' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $userId = $user ? (int)$user->id : 1;
        $role = ($user && str_contains($user->email, 'admin')) ? 'admin' : 'seller';
        $actorName = $user ? ($user->name ?? $user->email) : 'Admin';

        $this->taxMasterService->createRecord($validated, $userId, $role, $actorName);

        return redirect()->back()->with('success', "Tax classification for HSN {$validated['hsn_code']} added successfully.");
    }

    /**
     * Approve pending tax classification.
     */
    public function approve(Request $request, int $id)
    {
        $record = TaxMaster::findOrFail($id);
        $user = Auth::user();
        $adminId = $user ? (int)$user->id : 1;
        $actorName = $user ? ($user->name ?? $user->email) : 'Admin';

        $this->taxMasterService->approveRecord($record, $adminId, $actorName, $request->input('notes'));

        return redirect()->back()->with('success', "HSN {$record->hsn_code} classification approved and activated.");
    }

    /**
     * Reject pending tax classification.
     */
    public function reject(Request $request, int $id)
    {
        $request->validate(['reason' => 'required|string|min:3']);
        $record = TaxMaster::findOrFail($id);
        $user = Auth::user();
        $adminId = $user ? (int)$user->id : 1;
        $actorName = $user ? ($user->name ?? $user->email) : 'Admin';

        $this->taxMasterService->rejectRecord($record, $request->input('reason'), $adminId, $actorName);

        return redirect()->back()->with('error', "HSN {$record->hsn_code} classification rejected.");
    }
}

