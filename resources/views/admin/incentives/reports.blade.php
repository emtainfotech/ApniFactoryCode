@extends('layout')
@section('title', 'Reconciled Agent Incentive Reports')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Field Agent Incentives</div>
            <div class="ps-3"><ol class="breadcrumb mb-0 p-0"><li class="breadcrumb-item active">Ledger-Reconciled Reports (Platform Expense Only)</li></ol></div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.incentives.reports') }}" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Group Report By</label>
                        <select name="group_by" class="form-select form-select-sm">
                            <option value="agent" {{ ($filters['group_by'] ?? 'agent') === 'agent' ? 'selected' : '' }}>Field Agent</option>
                            <option value="buyer" {{ ($filters['group_by'] ?? '') === 'buyer' ? 'selected' : '' }}>Buyer / Dealer</option>
                            <option value="territory" {{ ($filters['group_by'] ?? '') === 'territory' ? 'selected' : '' }}>Territory</option>
                            <option value="sequence" {{ ($filters['group_by'] ?? '') === 'sequence' ? 'selected' : '' }}>Eligible Sequence (1/3..3/3)</option>
                            <option value="period" {{ ($filters['group_by'] ?? '') === 'period' ? 'selected' : '' }}>Period (Month)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Field Agent</label>
                        <select name="field_agent_id" class="form-select form-select-sm">
                            <option value="">All Agents</option>
                            @foreach($agents as $ag)<option value="{{ $ag->id }}" {{ ($filters['field_agent_id'] ?? '') == $ag->id ? 'selected' : '' }}>{{ $ag->agent_code }} — {{ $ag->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-2"><label class="form-label small fw-bold">From Date</label><input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] ?? '' }}"></div>
                    <div class="col-md-2"><label class="form-label small fw-bold">To Date</label><input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] ?? '' }}"></div>
                    <div class="col-md-2"><button type="submit" class="btn btn-sm btn-primary w-100">Run Report</button></div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Breakdown by {{ ucfirst($report['group_by']) }}</h6>
                <span class="badge bg-light text-dark border">Reconciliation Checksum Diff: ₹{{ number_format($report['summary']['reconciled_checksum_diff'], 2) }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Group</th>
                            <th class="text-end">Eligible Count</th>
                            <th class="text-end">Earned (₹)</th>
                            <th class="text-end">Pending (₹)</th>
                            <th class="text-end">Approved (₹)</th>
                            <th class="text-end">Paid (₹)</th>
                            <th class="text-end">Reversals (₹)</th>
                            <th class="text-end">Adjustments (₹)</th>
                            <th class="text-end">Net Ledger (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report['rows'] as $r)
                        <tr>
                            <td class="fw-bold">{{ $r['group_label'] }}</td>
                            <td class="text-end">{{ $r['eligible_orders_count'] }}</td>
                            <td class="text-end">₹{{ number_format($r['earned_amount'], 2) }}</td>
                            <td class="text-end">₹{{ number_format($r['pending_amount'], 2) }}</td>
                            <td class="text-end">₹{{ number_format($r['approved_amount'], 2) }}</td>
                            <td class="text-end">₹{{ number_format($r['paid_amount'], 2) }}</td>
                            <td class="text-end text-danger">₹{{ number_format($r['reversals_amount'], 2) }}</td>
                            <td class="text-end">₹{{ number_format($r['adjustments_amount'], 2) }}</td>
                            <td class="text-end fw-bold">₹{{ number_format($r['net_ledger_amount'], 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center py-4 text-muted">No ledger entries recorded for the selected filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

