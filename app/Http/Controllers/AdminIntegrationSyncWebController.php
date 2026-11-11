<?php

namespace App\Http\Controllers;

use App\Services\CrossPlatformSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminIntegrationSyncWebController extends Controller
{
    public function __construct(
        protected CrossPlatformSyncService $syncService
    ) {
        $this->middleware('auth');
    }

    protected function ensureAdmin(): void
    {
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            abort(403, 'Administrator privileges required.');
        }
    }

    public function syncDashboard(Request $request): View
    {
        $this->ensureAdmin();
        $filters = $request->only(['target_channel', 'entity_type', 'status', 'search']);
        $summary = $this->syncService->getAdminDashboardSummary();
        $events  = $this->syncService->listEvents($filters, 25)->withQueryString();

        return view('admin.integrations.sync', compact('summary', 'events', 'filters'));
    }

    public function retryEvent(Request $request, int $id): RedirectResponse
    {
        $this->ensureAdmin();
        try {
            $this->syncService->retrySyncEvent($id, auth()->user(), (bool)$request->boolean('reviewed_financial_override'), $request->input('notes'));
            return redirect()->back()->with('success', "Integration sync event #{$id} reprocessed.");
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }
    }

    public function reconciliationDashboard(Request $request): View
    {
        $this->ensureAdmin();
        $filters = $request->only(['status', 'entity_type', 'severity']);
        $summary = $this->syncService->getAdminDashboardSummary();
        $records = $this->syncService->listReconciliationRecords($filters, 25)->withQueryString();

        return view('admin.integrations.reconciliation', compact('summary', 'records', 'filters'));
    }

    public function scanDiscrepancies(): RedirectResponse
    {
        $this->ensureAdmin();
        $scan = $this->syncService->detectCrossChannelDiscrepancies();
        return redirect()->back()->with('success', "Scanned {$scan['scanned_mappings']} mappings; {$scan['diffs_recorded']} discrepancy check(s) logged.");
    }

    public function resolveReconciliation(Request $request, int $id): RedirectResponse
    {
        $this->ensureAdmin();
        $request->validate([
            'action' => 'required|in:reconcile_to_authoritative,mark_investigating,ignore,escalate',
            'notes'  => 'required|string|min:3|max:1000',
        ]);

        try {
            $this->syncService->resolveReconciliation($id, $request->input('action'), $request->input('notes'), auth()->user());
            return redirect()->back()->with('success', "Reconciliation record #{$id} resolved cleanly.");
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }
    }
}

