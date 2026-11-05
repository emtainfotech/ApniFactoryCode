@extends('layout')
@section('title', 'Commission & Platform Fee Rules')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Administration</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.pricing.revisions.queue') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Commission Rules</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="{{ route('admin.commissions.rules.create') }}" class="btn btn-primary px-4">
                    <i class="bx bx-plus-circle me-1"></i> New Commission Rule
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 bg-success alert-dismissible fade show py-2">
                <div class="d-flex align-items-center">
                    <div class="font-35 text-white"><i class="bx bxs-check-circle"></i></div>
                    <div class="ms-3 text-white">{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show py-2">
                <div class="d-flex align-items-center">
                    <div class="font-35 text-white"><i class="bx bxs-message-square-x"></i></div>
                    <div class="ms-3 text-white">{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Metric Cards -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-3">
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-info">
                    <div class="card-body">
                        <p class="mb-0 text-secondary">Total Commission Rules</p>
                        <h4 class="my-1 text-info">{{ number_format($counts['total']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-success">
                    <div class="card-body">
                        <p class="mb-0 text-secondary">Active Rules</p>
                        <h4 class="my-1 text-success">{{ number_format($counts['active']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-warning">
                    <div class="card-body">
                        <p class="mb-0 text-secondary">Scheduled Rules</p>
                        <h4 class="my-1 text-warning">{{ number_format($counts['scheduled']) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-danger">
                    <div class="card-body">
                        <p class="mb-0 text-secondary">Pending Approval</p>
                        <h4 class="my-1 text-danger">{{ number_format($counts['pending_approval']) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card radius-10">
            <div class="card-header bg-transparent py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="mb-0 font-weight-bold">Centralized Platform Commission Agreements</h6>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <form method="GET" action="{{ route('admin.commissions.rules.index') }}" class="d-inline-flex gap-2">
                            <select name="scope_type" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Scopes</option>
                                <option value="sku" {{ request('scope_type') === 'sku' ? 'selected' : '' }}>SKU-Specific (Priority 1)</option>
                                <option value="seller" {{ request('scope_type') === 'seller' ? 'selected' : '' }}>Seller Agreement (Priority 2)</option>
                                <option value="subcategory" {{ request('scope_type') === 'subcategory' ? 'selected' : '' }}>Subcategory (Priority 3)</option>
                                <option value="category" {{ request('scope_type') === 'category' ? 'selected' : '' }}>Category (Priority 4)</option>
                                <option value="global" {{ request('scope_type') === 'global' ? 'selected' : '' }}>Global Default (Priority 5)</option>
                            </select>
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="pending_approval" {{ request('status') === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                                <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="deactivated" {{ request('status') === 'deactivated' ? 'selected' : '' }}>Deactivated</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Rule Code</th>
                                <th>Rule Name / Agreement</th>
                                <th>Scope Type</th>
                                <th>Scope Target</th>
                                <th>Commission %</th>
                                <th>Fixed Fee</th>
                                <th>Effective Period</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rules as $rule)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.commissions.rules.show', $rule->id) }}" class="fw-bold text-primary">
                                            {{ $rule->rule_code }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $rule->name }}</div>
                                        <div class="small text-muted">{{ $rule->agreement_ref ?: 'Standard Agreement' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light-primary text-primary">{{ ucfirst($rule->scope_type) }}</span>
                                    </td>
                                    <td>
                                        <span class="small">{{ $rule->scope_label }}</span>
                                    </td>
                                    <td class="fw-bold text-success">{{ number_format($rule->commission_percentage, 2) }}%</td>
                                    <td>{{ $rule->fixed_fee > 0 ? '₹' . number_format($rule->fixed_fee, 2) : '—' }}</td>
                                    <td class="small">
                                        <div>From: {{ $rule->effective_from->format('d M Y') }}</div>
                                        <div class="text-muted">To: {{ $rule->effective_to ? $rule->effective_to->format('d M Y') : 'Open-ended' }}</div>
                                    </td>
                                    <td>
                                        @if($rule->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($rule->status === 'pending_approval')
                                            <span class="badge bg-info text-dark">Pending Approval</span>
                                        @elseif($rule->status === 'scheduled')
                                            <span class="badge bg-warning text-dark">Scheduled</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($rule->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.commissions.rules.show', $rule->id) }}" class="btn btn-sm btn-outline-primary">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="bx bx-receipt fs-1 d-block mb-2"></i>
                                        No commission rules found. Click "+ New Commission Rule" above to create an agreement.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $rules->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

