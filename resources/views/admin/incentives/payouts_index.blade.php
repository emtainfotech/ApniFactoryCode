@extends('layout')
@section('title', 'Agent Payouts & Reconciliation Dashboard')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Finance Control Center</div>
            <div class="ps-3"><ol class="breadcrumb mb-0 p-0"><li class="breadcrumb-item active">Agent Payout Management &amp; Reconciliation</li></ol></div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('admin.incentives.ledger.index') }}" class="btn btn-sm btn-outline-primary">Incentive Ledger</a>
                <a href="{{ route('admin.incentives.exceptions.index') }}" class="btn btn-sm btn-outline-danger">Exceptions ({{ $summary['open_exceptions_count'] ?? 0 }})</a>
                <a href="{{ route('admin.incentives.reports.export') }}" class="btn btn-sm btn-outline-success">Export CSV</a>
            </div>
        </div>

        @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger py-2"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

        @if(!($summary['payout_execution_enabled'] ?? false))
        <div class="alert alert-warning border-start border-4 border-warning py-2 small">
            <strong>Payout Execution Disabled (Governance Gate):</strong> Real bank transfers and payment confirmation remain locked (`mode: {{ $summary['payout_execution_mode'] ?? 'awaiting_policy' }}`) until written executive, finance, and tax/KYC approvals are finalized. Draft batches stay non-executable (`draft_awaiting_policy`). Seller proceeds remain untouched.
        </div>
        @endif

        <!-- Authoritative Ledger KPIs -->
        <div class="row g-2 mb-3">
            <div class="col-md-2"><div class="card mb-0"><div class="card-body py-2"><small class="text-muted">Gross Earned</small><h5 class="mb-0">₹{{ number_format($summary['earned_total'] ?? 0, 2) }}</h5></div></div></div>
            <div class="col-md-2"><div class="card mb-0"><div class="card-body py-2"><small class="text-muted">Pending Approval</small><h5 class="mb-0 text-warning">₹{{ number_format($summary['pending_approval_total'] ?? 0, 2) }}</h5></div></div></div>
            <div class="col-md-2"><div class="card mb-0"><div class="card-body py-2"><small class="text-muted">Approved Unpaid</small><h5 class="mb-0 text-primary">₹{{ number_format($summary['approved_unpaid_total'] ?? 0, 2) }}</h5></div></div></div>
            <div class="col-md-2"><div class="card mb-0"><div class="card-body py-2"><small class="text-muted">Confirmed Paid</small><h5 class="mb-0 text-success">₹{{ number_format($summary['confirmed_paid_total'] ?? 0, 2) }}</h5></div></div></div>
            <div class="col-md-2"><div class="card mb-0"><div class="card-body py-2"><small class="text-muted">Reversals &amp; Adj</small><h5 class="mb-0 text-danger">₹{{ number_format(($summary['reversals_total'] ?? 0) + ($summary['adjustments_total'] ?? 0), 2) }}</h5></div></div></div>
            <div class="col-md-2"><div class="card mb-0 border-danger"><div class="card-body py-2"><small class="text-muted">Unreconciled / Failed</small><h5 class="mb-0">{{ $summary['unreconciled_payouts'] ?? 0 }} / {{ $summary['failed_payouts_count'] ?? 0 }}</h5></div></div></div>
        </div>

        <!-- Payout-Ready Approved Incentives Selector -->
        <div class="card mb-3">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Approved Incentives — Payout Readiness Check</h6>
            </div>
            <div class="card-body p-0">
                <form method="POST" action="{{ route('admin.incentives.payouts.store') }}">@csrf
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Select</th>
                                    <th>Ledger Ref</th>
                                    <th>Agent</th>
                                    <th>Order &amp; Seq</th>
                                    <th>Net Payable (₹)</th>
                                    <th>Readiness Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($approvedEntries as $entry)
                                <tr>
                                    <td><input type="checkbox" name="ledger_ids[]" value="{{ $entry->id }}" {{ !($entry->readiness['ready'] ?? false) ? 'disabled' : '' }}></td>
                                    <td class="fw-bold">{{ $entry->ledger_ref }}</td>
                                    <td class="small">{{ $entry->agent?->agent_code }} — {{ $entry->agent?->name }}</td>
                                    <td class="small">{{ $entry->parentOrder?->orderno }} ({{ $entry->eligible_order_sequence }}/3)</td>
                                    <td class="fw-bold">₹{{ number_format($entry->readiness['net_payable'] ?? $entry->calculated_incentive_amount, 2) }}</td>
                                    <td class="small">
                                        @if($entry->readiness['ready'] ?? false)
                                            <span class="badge bg-success">Payout-Ready</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Blocked</span>
                                            <span class="text-muted">{{ implode(' ', $entry->readiness['reasons'] ?? []) }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center py-3 text-muted">No approved unpaid incentives available for batching.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($approvedEntries->count() > 0)
                    <div class="p-3 bg-light border-top d-flex flex-wrap gap-2 align-items-center">
                        <select name="field_agent_id" class="form-select form-select-sm w-auto" required>
                            <option value="">Select Target Field Agent</option>
                            @foreach($agents as $ag)<option value="{{ $ag->id }}">{{ $ag->agent_code }} — {{ $ag->name }}</option>@endforeach
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">Create Payout Batch</button>
                    </div>
                    @endif
                </form>
            </div>
        </div>

        <!-- Payout Batches History -->
        <div class="card">
            <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold">Payout Records &amp; History ({{ $payouts->total() }})</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Payout Ref</th>
                                <th>Agent</th>
                                <th>Items</th>
                                <th>Net Payable</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>External Ref / Recon</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payouts as $p)
                            <tr>
                                <td class="fw-bold text-primary">{{ $p->payout_ref }}</td>
                                <td class="small">{{ $p->agent?->agent_code }}<br><span class="text-muted">{{ $p->agent?->name }}</span></td>
                                <td>{{ $p->items->count() }}</td>
                                <td class="fw-bold">₹{{ number_format($p->net_payable_amount, 2) }}</td>
                                <td class="small">{{ str_replace('_', ' ', $p->payment_method) }}</td>
                                <td><span class="badge {{ $p->status === 'paid_confirmed' ? 'bg-success' : ($p->status === 'failed' ? 'bg-danger' : 'bg-secondary') }}">{{ ucwords(str_replace('_', ' ', $p->status)) }}</span></td>
                                <td class="small">{{ $p->external_transaction_ref ?? 'Pending' }}<br><span class="badge bg-light text-dark border">{{ $p->reconciliation_status }}</span></td>
                                <td class="text-end"><a href="{{ route('admin.incentives.payouts.show', $p->id) }}" class="btn btn-sm btn-outline-primary">Manage</a></td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center py-4 text-muted">No agent payout batches recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

