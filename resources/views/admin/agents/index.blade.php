@extends('layout')
@section('title', 'Field Sales Agents Master')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Field Operations</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.agents.index') }}"><i class="bx bx-home-alt"></i> Agents</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Field Sales Agent Master</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('admin.attributions.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fa-solid fa-link me-1"></i> Buyer Attributions
                </a>
                <a href="{{ route('admin.agents.create') }}" class="btn btn-sm btn-primary">
                    <i class="fa-solid fa-user-plus me-1"></i> Register New Agent
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 bg-success alert-dismissible fade show py-2 text-white">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Commercial Governance Notice -->
        <div class="alert alert-info border-0 bg-light-info py-2 mb-3">
            <div class="small text-dark">
                <strong>Phase 6.1 Governance:</strong> Field agents are linked directly to buyer/dealer accounts (never manufacturers/sellers). Incentive calculation, eligible-order ledger, and payouts are disabled in this phase pending formal business approval.
            </div>
        </div>

        <!-- KPI Metrics -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-3">
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-primary">
                    <div class="card-body">
                        <p class="mb-0 text-secondary">Total Field Agents</p>
                        <h4 class="my-1 text-primary">{{ $metrics['total_agents'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-success">
                    <div class="card-body">
                        <p class="mb-0 text-secondary">Active & Attributable</p>
                        <h4 class="my-1 text-success">{{ $metrics['active_agents'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-warning">
                    <div class="card-body">
                        <p class="mb-0 text-secondary">Pending Activation</p>
                        <h4 class="my-1 text-warning">{{ $metrics['pending_agents'] }}</h4>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-danger">
                    <div class="card-body">
                        <p class="mb-0 text-secondary">Inactive / Suspended</p>
                        <h4 class="my-1 text-danger">{{ $metrics['inactive_agents'] }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.agents.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Search (Agent ID, Name, Mobile, Territory)</label>
                        <input type="text" name="search" class="form-control form-control-sm" value="{{ $filters['search'] ?? '' }}" placeholder="e.g. FSA-2026 or Jaipur">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            @foreach(config('marketplace_agents.agent_statuses') as $key => $label)
                                <option value="{{ $key }}" {{ ($filters['status'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Territory / Cluster</label>
                        <input type="text" name="territory" class="form-control form-control-sm" value="{{ $filters['territory'] ?? '' }}" placeholder="City / Territory">
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                        <a href="{{ route('admin.agents.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Agents Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Agent ID</th>
                                <th>Name & Contact</th>
                                <th>Territory</th>
                                <th>Status</th>
                                <th>KYC / Contract</th>
                                <th>Active Buyers</th>
                                <th>Joining Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($agents as $agent)
                            <tr>
                                <td><span class="fw-bold text-primary">{{ $agent->agent_code }}</span></td>
                                <td>
                                    <div class="fw-bold">{{ $agent->name }}</div>
                                    <div class="small text-muted">{{ $agent->mobile }} @if($agent->email)&bull; {{ $agent->email }}@endif</div>
                                </td>
                                <td>
                                    <div>{{ $agent->territory }}</div>
                                    @if($agent->territory_city || $agent->territory_state)
                                        <div class="small text-muted">{{ implode(', ', array_filter([$agent->territory_city, $agent->territory_state])) }}</div>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $badgeClass = match($agent->status) {
                                            'active' => 'bg-success',
                                            'pending' => 'bg-warning text-dark',
                                            'suspended' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ ucfirst($agent->status) }}</span>
                                </td>
                                <td>
                                    <div class="small">KYC: <span class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $agent->kyc_status)) }}</span></div>
                                    <div class="small text-muted">Contract: {{ ucfirst(str_replace('_', ' ', $agent->contract_status)) }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $agent->active_attributions_count }} Active</span>
                                </td>
                                <td class="small">{{ $agent->joining_date ? $agent->joining_date->format('d M Y') : 'N/A' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.agents.show', $agent->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    <a href="{{ route('admin.agents.edit', $agent->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No field sales agents found matching your criteria.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($agents->hasPages())
                <div class="card-footer bg-white">{{ $agents->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

