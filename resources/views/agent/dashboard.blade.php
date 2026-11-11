@extends('layout')
@section('title', 'Field Sales Agent Dashboard')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Field Agent Portal</div>
            <div class="ps-3">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item active">My Assigned Buyers & Profile</li>
                </ol>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 bg-success text-white py-2">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger py-2">
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <!-- Agent Profile Card (Sanitized) -->
        <div class="card mb-3 border-start border-4 border-primary">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-md-4">
                        <span class="text-muted small">Field Sales Agent</span>
                        <h5 class="mb-1 fw-bold">{{ $agentProfile['name'] }}</h5>
                        <span class="badge bg-primary">{{ $agentProfile['agent_code'] }}</span>
                        <span class="badge {{ $agentProfile['status'] === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($agentProfile['status']) }}</span>
                    </div>
                    <div class="col-md-4">
                        <div class="small"><strong>Territory:</strong> {{ $agentProfile['territory'] }}</div>
                        <div class="small"><strong>Contact:</strong> {{ $agentProfile['mobile'] }}</div>
                        <div class="small"><strong>Joining Date:</strong> {{ $agentProfile['joining_date'] ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small"><strong>Supervisor:</strong> {{ $agentProfile['supervisor_name'] ?? 'Direct Operations' }}</div>
                        <div class="small"><strong>KYC Status:</strong> {{ ucfirst(str_replace('_', ' ', $agentProfile['kyc_status'])) }}</div>
                        <div class="small"><strong>Contract Status:</strong> {{ ucfirst(str_replace('_', ' ', $agentProfile['contract_status'])) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Eligibility & Policy Notice -->
        <div class="alert alert-info border-0 bg-light-info py-2 mb-3 small text-dark">
            <strong>Scheme Governance Notice:</strong> Eligible-order counting (first 3 eligible orders) and Apni Factory incentive calculations are scheduled for Phase 6.2 pending formal business sign-off. Confidential seller pricing and settlement ledgers are restricted.
        </div>

        <!-- Assigned Buyer Search -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('agent.dashboard') }}" class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Search Assigned Dealers</label>
                        <input type="text" name="search" class="form-control form-control-sm" value="{{ $filters['search'] ?? '' }}" placeholder="Dealer name, location, or attribution ref">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Attribution Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Permitted</option>
                            <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending Approval</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                        <a href="{{ route('agent.dashboard') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Assigned Buyers Table -->
        <div class="card">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">My Assigned Buyers / Dealers ({{ $buyers->total() }})</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Attribution Ref</th>
                                <th>Dealer / Buyer Name</th>
                                <th>City / Location</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Eligible Orders Progress (Scheme 0/3)</th>
                                <th class="text-end">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($buyers as $item)
                            <tr>
                                <td class="fw-bold text-primary">{{ $item['attribution_ref'] }}</td>
                                <td class="fw-bold">{{ $item['buyer']['name'] }}</td>
                                <td>{{ $item['buyer']['location'] ?? 'N/A' }}</td>
                                <td class="small">{{ $item['buyer']['mobile'] }}</td>
                                <td>
                                    <span class="badge {{ $item['attribution_status'] === 'active' ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ ucfirst($item['attribution_status']) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border" title="Order eligibility rules pending Phase 6.2 approval">
                                        {{ $item['eligible_order_progress']['display'] }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('agent.buyers.show', $item['buyer']['id']) }}" class="btn btn-sm btn-outline-primary">View Dealer</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-users-slash fs-3 d-block mb-2"></i>
                                    No buyers/dealers are currently attributed to your agent account.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($buyers->hasPages())
                <div class="card-footer bg-white">{{ $buyers->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

