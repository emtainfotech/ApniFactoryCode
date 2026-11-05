<?php

namespace App\Http\Controllers;

use App\Models\FulfilmentClaim;
use App\Models\User;
use App\Services\BuyerDisputeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminClaimWebController extends Controller
{
    public function __construct(
        protected BuyerDisputeService $disputeService
    ) {
    }

    /**
     * Display central dispute queue for platform operations.
     */
    public function index(Request $request): View
    {
        $claims = $this->disputeService->getAdminClaims($request->all());
        $metrics = $this->disputeService->getAdminMetrics();
        $sellers = User::select('id', 'name', 'email')->get();

        return view('admin.claims.index', compact('claims', 'metrics', 'sellers'));
    }

    /**
     * Inspect single claim, review evidence, internal notes & audit trail.
     */
    public function show(int $id): View
    {
        $claim = FulfilmentClaim::with([
            'items',
            'evidence.uploader',
            'history',
            'sellerFulfilment.company',
            'order.orderdetails',
            'parentOrder',
            'seller',
            'customer',
            'resolvedBy',
        ])->findOrFail($id);

        return view('admin.claims.show', compact('claim'));
    }

    /**
     * Admin/Operations transitions claim status.
     */
    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $claim = FulfilmentClaim::findOrFail($id);
        $user = auth()->user();

        $validated = $request->validate([
            'status'             => 'required|string|in:under_review,resolved_settled,rejected,cancelled',
            'admin_notes'        => 'nullable|string|max:2000',
            'resolution_summary' => 'nullable|string|max:2000',
        ]);

        $adminUserId = $user ? (int)$user->id : 1;
        $adminName = $user ? ($user->name ?? $user->email) : 'Operations Admin';

        $this->disputeService->updateClaimStatusByAdmin(
            $claim,
            $validated['status'],
            $adminUserId,
            $adminName,
            $validated['admin_notes'] ?? null,
            $validated['resolution_summary'] ?? null
        );

        return redirect()->route('admin.claims.show', $claim->id)
            ->with('success', "Claim status updated to '{$validated['status']}' successfully.");
    }
}

