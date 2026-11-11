@extends('layout')
@section('title', 'Apni Factory-Funded Incentive Ledger')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Field Agent Incentives</div>
            <div class="ps-3"><ol class="breadcrumb mb-0 p-0"><li class="breadcrumb-item active">Immutable Incentive Ledger (Apni Factory Expense — Zero Seller Deductions)</li></ol></div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('admin.incentives.payouts.index') }}" class="btn btn-sm btn-outline-success">Agent Payouts</a>
                <a href="{{ route('admin.incentives.reports.export') }}" class="btn btn-sm btn-outline-primary">Export CSV</a>
                <a href="{{ route('admin.incentives.reports') }}" class="btn btn-sm btn-outline-info">Reconciled Reports</a>
            </div>
        </div>

        @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger py-2"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

        @php $s = $report['summary']; @endphp
        <div class="row g-2 mb-3">
            <div class="col-md-2"><div class="card mb-0"><div class="card-body py-2"><small class="text-muted">Earned</small><h5 class="mb-0">₹{{ number_format($s['earned_total'], 2) }}</h5></div></div></div>
            <div class="col-md-2"><div class="card mb-0"><div class="card-body py-2"><small class="text-muted">Pending Approval</small><h5 class="mb-0 text-warning">₹{{ number_format($s['pending_approval_total'], 2) }}</h5></div></div></div>
            <div class="col-md-2"><div class="card mb-0"><div class="card-body py-2"><small class="text-muted">Approved</small><h5 class="mb-0 text-success">₹{{ number_format($s['approved_total'], 2) }}</h5></div></div></div>
            <div class="col-md-2"><div class="card mb-0"><div class="card-body py-2"><small class="text-muted">Paid (Deferred 6.3)</small><h5 class="mb-0">₹{{ number_format($s['paid_total'], 2) }}</h5></div></div></div>
            <div class="col-md-2"><div class="card mb-0"><div class="card-body py-2"><small class="text-muted">Reversals &amp; Adj</small><h5 class="mb-0 text-danger">₹{{ number_format($s['reversals_total'] + $s['adjustments_total'], 2) }}</h5></div></div></div>
            <div class="col-md-2"><div class="card mb-0 border-primary"><div class="card-body py-2"><small class="text-muted">Net Liability</small><h5 class="mb-0 text-primary">₹{{ number_format($s['net_incentive_liability'], 2) }}</h5></div></div></div>
        </div>

        <div class="card">
            <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold">Immutable Ledger Entries ({{ $entries->total() }})</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ledger Ref</th>
                                <th>Type</th>
                                <th>Order &amp; Seq</th>
                                <th>Agent &amp; Buyer</th>
                                <th>Rule Version</th>
                                <th>Base</th>
                                <th>Amount (₹)</th>
                                <th>Status</th>
                                <th class="text-end">Finance Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($entries as $row)
                            <tr>
                                <td class="fw-bold">{{ $row->ledger_ref }}@if($row->referenceLedger)<br><small class="text-muted">Ref: {{ $row->referenceLedger->ledger_ref }}</small>@endif</td>
                                <td><span class="badge {{ $row->entry_type === 'earned' ? 'bg-primary' : ($row->entry_type === 'reversal' ? 'bg-danger' : 'bg-info text-dark') }}">{{ strtoupper($row->entry_type) }}</span></td>
                                <td class="small">{{ $row->parentOrder?->orderno }}<br>Slot <strong>{{ $row->eligible_order_sequence }}/3</strong></td>
                                <td class="small">{{ $row->agent?->agent_code }}<br><span class="text-muted">{{ $row->customer?->name }}</span></td>
                                <td class="small">{{ $row->rule_code }} v{{ $row->rule_version }}</td>
                                <td class="small">₹{{ number_format($row->calculation_base_amount, 2) }}</td>
                                <td class="fw-bold {{ $row->calculated_incentive_amount < 0 ? 'text-danger' : 'text-success' }}">₹{{ number_format($row->calculated_incentive_amount, 2) }}</td>
                                <td><span class="badge bg-secondary">{{ ucwords(str_replace('_', ' ', $row->status)) }}</span></td>
                                <td class="text-end">
                                    @if($row->entry_type === 'earned' && in_array($row->status, ['calculated_pending_approval', 'pending_review', 'on_hold'], true))
                                        <form method="POST" action="{{ route('admin.incentives.ledger.approve', $row->id) }}" class="d-inline">@csrf
                                            <button class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                    @endif
                                    @if($row->entry_type === 'earned' && in_array($row->status, ['calculated_pending_approval', 'pending_review'], true))
                                        <form method="POST" action="{{ route('admin.incentives.ledger.hold', $row->id) }}" class="d-inline">@csrf
                                            <input type="hidden" name="reason" value="Held for finance verification">
                                            <button class="btn btn-sm btn-warning">Hold</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.incentives.ledger.reject', $row->id) }}" class="d-inline">@csrf
                                            <input type="hidden" name="reason" value="Declined on finance review">
                                            <button class="btn btn-sm btn-outline-secondary">Reject</button>
                                        </form>
                                    @endif
                                    @if($row->entry_type === 'earned' && !in_array($row->status, ['reversed','paid','rejected'], true))
                                        <form method="POST" action="{{ route('admin.incentives.ledger.reverse', $row->id) }}" class="d-inline">@csrf
                                            <input type="hidden" name="reason" value="Authorized manual reversal">
                                            <button class="btn btn-sm btn-outline-danger">Reverse</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="9" class="text-center py-4 text-muted">No incentive ledger entries posted yet (expected when funding policy is awaiting written sign-off).</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

