<?php

namespace App\Http\Controllers;

use App\Models\PriceRevision;
use App\Services\DynamicPriceRevisionService;
use Illuminate\Http\Request;

class AdminPriceRevisionWebController extends Controller
{
    public function __construct(private DynamicPriceRevisionService $pricingService)
    {
        $this->middleware('auth');
    }

    private function checkAdmin(): void
    {
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized. Platform Admin access required.');
        }
    }

    /**
     * Admin review queue for price revisions requiring approval.
     */
    public function queue(Request $request)
    {
        $this->checkAdmin();

        $query = PriceRevision::with(['company', 'creator'])
            ->where('status', 'pending_approval')
            ->orderBy('id', 'asc');

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $revisions = $query->paginate(20);

        return view('admin.pricing.revisions.queue', compact('revisions'));
    }

    /**
     * Review specific revision details.
     */
    public function show($id)
    {
        $this->checkAdmin();

        $revision = PriceRevision::with(['company', 'creator', 'approver', 'items.product', 'items.variant'])
            ->where('id', $id)
            ->firstOrFail();

        return view('admin.pricing.revisions.show', compact('revision'));
    }

    /**
     * Approve price revision.
     */
    public function approve(Request $request, $id)
    {
        $this->checkAdmin();

        $revision = PriceRevision::where('id', $id)->firstOrFail();

        try {
            $this->pricingService->approveRevision($revision, auth()->user());
            return redirect()->route('admin.pricing.revisions.queue')
                ->with('success', "Price revision {$revision->revision_code} approved successfully.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject price revision with reason.
     */
    public function reject(Request $request, $id)
    {
        $this->checkAdmin();

        $request->validate([
            'reason' => 'required|string|min:5|max:1000',
        ]);

        $revision = PriceRevision::where('id', $id)->firstOrFail();

        try {
            $this->pricingService->rejectRevision($revision, auth()->user(), $request->reason);
            return redirect()->route('admin.pricing.revisions.queue')
                ->with('success', "Price revision {$revision->revision_code} rejected.");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}

