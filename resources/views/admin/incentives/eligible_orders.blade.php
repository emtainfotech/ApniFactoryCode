@extends('layout')
@section('title', 'Agent Eligible Orders (0/3..3/3)')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Field Agent Incentives</div>
            <div class="ps-3">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item active">Eligible Order Tracking (Scheme Cap: First 3 Orders per Buyer)</li>
                </ol>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('admin.incentives.rules.index') }}" class="btn btn-sm btn-outline-primary">Rule Engine</a>
                <a href="{{ route('admin.incentives.ledger.index') }}" class="btn btn-sm btn-outline-success">Incentive Ledger</a>
                <a href="{{ route('admin.incentives.reports') }}" class="btn btn-sm btn-outline-info">Reconciled Reports</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success py-2">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger py-2"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        @if(!($reportSummary['funding_policy_approved'] ?? false))
        <div class="alert alert-warning border-start border-4 border-warning py-2 small">
            <strong>Commercial Governance Lock Active:</strong> Non-financial order eligibility tracking (0/3..3/3) is active across sellers, while payable financial liabilities remain <strong>BLOCKED</strong> pending written business &amp; finance sign-off of the Section 12 Apni Factory-funded policy. Seller settlement proceeds remain strictly untouched.
        </div>
        @endif

        <!-- KPI Strip -->
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card border-start border-4 border-success mb-0"><div class="card-body py-2">
                    <small class="text-muted">Eligible Orders (Counted 1-3)</small>
                    <h4 class="mb-0 fw-bold text-success">{{ $reportSummary['total_eligible_orders'] }}</h4>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card border-start border-4 border-warning mb-0"><div class="card-body py-2">
                    <small class="text-muted">Pending Eligibility / Review</small>
                    <h4 class="mb-0 fw-bold text-warning">{{ $reportSummary['total_pending_orders'] }}</h4>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card border-start border-4 border-primary mb-0"><div class="card-body py-2">
                    <small class="text-muted">Earned Ledger Total</small>
                    <h4 class="mb-0 fw-bold">₹{{ number_format($reportSummary['earned_total'], 2) }}</h4>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card border-start border-4 border-info mb-0"><div class="card-body py-2">
                    <small class="text-muted">Approved Total (Payable Held)</small>
                    <h4 class="mb-0 fw-bold">₹{{ number_format($reportSummary['approved_total'], 2) }}</h4>
                </div></div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.incentives.eligible-orders') }}" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Search Order / Ref / Buyer</label>
                        <input type="text" name="search" class="form-control form-control-sm" value="{{ $filters['search'] ?? '' }}" placeholder="ORD-..., ELIG-..., buyer, agent">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Field Agent</label>
                        <select name="field_agent_id" class="form-select form-select-sm">
                            <option value="">All Agents</option>
                            @foreach($agents as $ag)
                                <option value="{{ $ag->id }}" {{ ($filters['field_agent_id'] ?? '') == $ag->id ? 'selected' : '' }}>{{ $ag->agent_code }} — {{ $ag->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Territory</label>
                        <input type="text" name="territory" class="form-control form-control-sm" value="{{ $filters['territory'] ?? '' }}" placeholder="City/Zone">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Eligibility Status</label>
                        <select name="eligibility_status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            @foreach(['eligible','pending_eligibility','pending_review','ineligible_unpaid','ineligible_cancelled','ineligible_rejected','ineligible_refunded','ineligible_limit_exceeded','reversed_refunded','reversed_cancelled'] as $st)
                                <option value="{{ $st }}" {{ ($filters['eligibility_status'] ?? '') === $st ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $st)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                        <a href="{{ route('admin.incentives.eligible-orders') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        @if(isset($failures) && ($failures->isNotEmpty() || ($deadLetterCount ?? 0) > 0))
        <div class="card border-danger mb-3">
            <div class="card-header bg-light text-danger d-flex justify-content-between align-items-center py-2">
                <span class="fw-bold small">Dead-Letter &amp; Failed Event Recovery Queue ({{ $failures->count() }} failed sync / {{ $deadLetterCount ?? 0 }} failed queue jobs)</span>
            </div>
            @if($failures->isNotEmpty())
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Event Key</th><th>Type</th><th>Order ID</th><th>Attempts</th><th>Error</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                        @foreach($failures as $fl)
                        <tr>
                            <td class="small">{{ $fl->event_key }}</td>
                            <td class="small">{{ $fl->event_type }}</td>
                            <td class="small">#{{ $fl->parent_order_id ?? $fl->order_id }}</td>
                            <td class="small">{{ $fl->attempts }}</td>
                            <td class="small text-danger">{{ \Illuminate\Support\Str::limit($fl->error_message, 80) }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.incentives.failures.retry', $fl->id) }}" class="d-inline">@csrf
                                    <button class="btn btn-sm btn-outline-danger">Retry Safely</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endif

        <!-- Tracked Orders Table -->
        <div class="card">
            <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold">Parent Checkout Orders &amp; Buyer Slot Allocation ({{ $orders->total() }})</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ref / Order #</th>
                                <th>Buyer / Dealer</th>
                                <th>Attributed Agent</th>
                                <th>Sellers (Parent Unit)</th>
                                <th>Slot</th>
                                <th>Eligibility Status</th>
                                <th>Incentive Generation</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $item)
                            <tr>
                                <td>
                                    <div class="fw-bold text-primary">{{ $item->orderno }}</div>
                                    <small class="text-muted">{{ $item->eligibility_ref }}</small>
                                </td>
                                <td>{{ $item->customer?->name ?? 'Customer #'.$item->customer_id }}</td>
                                <td>
                                    <span class="fw-bold">{{ $item->agent?->agent_code }}</span><br>
                                    <small class="text-muted">{{ $item->agent?->territory }}</small>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $item->seller_count }} seller(s)</span></td>
                                <td>
                                    @if($item->sequence_number)
                                        <span class="badge bg-dark">{{ $item->sequence_number }}/3</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $item->eligibility_status === 'eligible' ? 'bg-success' : (str_starts_with($item->eligibility_status, 'pending') ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                        {{ ucwords(str_replace('_', ' ', $item->eligibility_status)) }}
                                    </span>
                                </td>
                                <td class="small">{{ ucwords(str_replace('_', ' ', $item->incentive_generation_status)) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.incentives.eligible-orders.show', $item->id) }}" class="btn btn-sm btn-outline-primary">Inspect</a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center py-4 text-muted">No tracked agent orders found matching filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($orders->hasPages())
                <div class="card-footer">{{ $orders->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

