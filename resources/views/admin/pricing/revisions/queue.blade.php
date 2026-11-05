@extends('layout')
@section('title', 'Admin Price Revision Approvals')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Administration</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.catalogue.reviews') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Price Revision Queue</li>
                    </ol>
                </nav>
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

        <div class="card radius-10">
            <div class="card-header bg-transparent py-3">
                <h6 class="mb-0 font-weight-bold">Pending Seller Price Revisions Requiring Approval</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Revision Code</th>
                                <th>Seller Company</th>
                                <th>Method</th>
                                <th>Adjustment</th>
                                <th>Price Basis</th>
                                <th>Eligible SKUs</th>
                                <th>Effective Timestamp (IST)</th>
                                <th>Initiated By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($revisions as $rev)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.pricing.revisions.show', $rev->id) }}" class="fw-bold text-primary">
                                            {{ $rev->revision_code }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ $rev->company ? $rev->company->name : 'Company #' . $rev->company_id }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light-primary text-primary">{{ ucfirst($rev->revision_method) }}</span>
                                    </td>
                                    <td>
                                        @if($rev->revision_method === 'percentage')
                                            <span class="fw-bold {{ $rev->revision_direction === 'increase' ? 'text-success' : 'text-danger' }}">
                                                {{ $rev->revision_direction === 'increase' ? '+' : '-' }}{{ number_format($rev->revision_value, 2) }}%
                                            </span>
                                        @elseif($rev->revision_method === 'flat_per_unit')
                                            <span class="fw-bold {{ $rev->revision_direction === 'increase' ? 'text-success' : 'text-danger' }}">
                                                {{ $rev->revision_direction === 'increase' ? '+' : '-' }}₹{{ number_format($rev->revision_value, 2) }}
                                            </span>
                                        @else
                                            <span class="text-muted">Explicit List</span>
                                        @endif
                                    </td>
                                    <td>{{ $rev->price_basis_unit ?: 'Standard' }}</td>
                                    <td><span class="badge bg-light-dark text-dark">{{ $rev->affected_skus_count }}</span></td>
                                    <td>{{ $rev->effective_at ? $rev->effective_at->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') : 'Immediate' }}</td>
                                    <td>{{ $rev->creator ? $rev->creator->name : 'N/A' }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('admin.pricing.revisions.show', $rev->id) }}" class="btn btn-sm btn-outline-primary">
                                                Review
                                            </a>
                                            <form method="POST" action="{{ route('admin.pricing.revisions.approve', $rev->id) }}" class="d-inline" onsubmit="return confirm('Approve this price revision?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="bx bx-check-shield fs-1 d-block mb-2 text-success"></i>
                                        Queue is empty! All submitted price revisions have been processed.
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
