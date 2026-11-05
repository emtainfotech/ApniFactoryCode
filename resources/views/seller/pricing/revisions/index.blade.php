@extends('layout')
@section('title', 'Dynamic Price Revisions')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Pricing Engine</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('seller.catalogue.analytics') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Dynamic Price Revisions</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="{{ route('seller.pricing.revisions.create') }}" class="btn btn-primary px-4">
                    <i class="bx bx-plus-circle me-1"></i> New Price Revision
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

        <!-- Summary Metric Cards -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-3">
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Revisions</p>
                                <h4 class="my-1 text-info">{{ number_format($counts['total']) }}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-info text-info ms-auto">
                                <i class="bx bx-history"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Active / Applied</p>
                                <h4 class="my-1 text-success">{{ number_format($counts['active']) }}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto">
                                <i class="bx bx-check-double"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Scheduled (Pending Time)</p>
                                <h4 class="my-1 text-warning">{{ number_format($counts['scheduled']) }}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-warning text-warning ms-auto">
                                <i class="bx bx-time-five"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Pending Approval</p>
                                <h4 class="my-1 text-danger">{{ number_format($counts['pending_approval']) }}</h4>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto">
                                <i class="bx bx-shield-quarter"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revisions Table Card -->
        <div class="card radius-10">
            <div class="card-header bg-transparent py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h6 class="mb-0 font-weight-bold">Price Revision History & Scheduled Tasks</h6>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <form method="GET" action="{{ route('seller.pricing.revisions.index') }}" class="d-inline-flex gap-2">
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Statuses</option>
                                <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="pending_approval" {{ request('status') === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                            <select name="revision_method" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Methods</option>
                                <option value="percentage" {{ request('revision_method') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                                <option value="flat_per_unit" {{ request('revision_method') === 'flat_per_unit' ? 'selected' : '' }}>Flat per Unit</option>
                                <option value="explicit_list" {{ request('revision_method') === 'explicit_list' ? 'selected' : '' }}>Explicit List</option>
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
                                <th>Revision Code</th>
                                <th>Method</th>
                                <th>Adjustment</th>
                                <th>Price Basis</th>
                                <th>Eligible SKUs</th>
                                <th>Effective Timestamp (IST)</th>
                                <th>Status</th>
                                <th>Initiator</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($revisions as $rev)
                                <tr>
                                    <td>
                                        <a href="{{ route('seller.pricing.revisions.show', $rev->id) }}" class="fw-bold text-primary">
                                            {{ $rev->revision_code }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($rev->revision_method === 'percentage')
                                            <span class="badge bg-light-primary text-primary">Percentage</span>
                                        @elseif($rev->revision_method === 'flat_per_unit')
                                            <span class="badge bg-light-info text-info">Flat / Unit</span>
                                        @else
                                            <span class="badge bg-light-secondary text-secondary">Explicit List</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($rev->revision_method === 'percentage')
                                            <span class="{{ $rev->revision_direction === 'increase' ? 'text-success' : 'text-danger' }} fw-bold">
                                                {{ $rev->revision_direction === 'increase' ? '+' : '-' }}{{ number_format($rev->revision_value, 2) }}%
                                            </span>
                                        @elseif($rev->revision_method === 'flat_per_unit')
                                            <span class="{{ $rev->revision_direction === 'increase' ? 'text-success' : 'text-danger' }} fw-bold">
                                                {{ $rev->revision_direction === 'increase' ? '+' : '-' }}₹{{ number_format($rev->revision_value, 2) }}
                                            </span>
                                        @else
                                            <span class="text-muted">Per SKU list</span>
                                        @endif
                                    </td>
                                    <td>{{ $rev->price_basis_unit ?: 'Standard' }}</td>
                                    <td>
                                        <span class="badge bg-light-dark text-dark">{{ $rev->affected_skus_count }} / {{ $rev->total_skus }}</span>
                                    </td>
                                    <td>
                                        @if($rev->effective_at)
                                            <span class="text-dark">{{ $rev->effective_at->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') }}</span>
                                        @else
                                            <span class="text-muted">Immediate</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($rev->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($rev->status === 'scheduled')
                                            <span class="badge bg-warning text-dark">Scheduled</span>
                                        @elseif($rev->status === 'pending_approval')
                                            <span class="badge bg-info text-dark">Pending Approval</span>
                                        @elseif($rev->status === 'cancelled')
                                            <span class="badge bg-secondary">Cancelled</span>
                                        @elseif($rev->status === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-light-secondary text-secondary">{{ ucfirst($rev->status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $rev->creator ? $rev->creator->name : 'System' }}</td>
                                    <td>
                                        <a href="{{ route('seller.pricing.revisions.show', $rev->id) }}" class="btn btn-sm btn-outline-primary">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="bx bx-receipt fs-1 d-block mb-2"></i>
                                        No price revisions found. Click "+ New Price Revision" above to schedule a price change.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $revisions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
