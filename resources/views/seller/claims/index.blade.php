@extends('layout')
@section('title', 'Seller Consignment Claims & Disputes')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Seller Portal</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('order.list') }}"><i class="bx bx-home-alt"></i> Orders</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Consignment Claims & Shortages</li>
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

        <div class="card border-primary mb-3">
            <div class="card-body bg-light-primary rounded">
                <div class="d-flex align-items-center">
                    <div class="font-30 text-primary me-3"><i class="bx bx-shield-quarter"></i></div>
                    <div>
                        <h6 class="mb-1 text-primary fw-bold">Dispute Intake & Claims Management</h6>
                        <p class="mb-0 text-secondary font-13">
                            Buyers may report shortages, transit damages, or variant discrepancies against your dispatched consignments.
                            Review reported line items and evidence below. You can submit clarification remarks and counter-evidence directly from the claim view.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Claim Ref / Date</th>
                                <th>Order #</th>
                                <th>Issue Type</th>
                                <th>Buyer Description</th>
                                <th>Claim Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($claims as $claim)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-primary">{{ $claim->claim_ref }}</div>
                                        <div class="small text-muted">{{ $claim->created_at->format('d M Y, h:i A') }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">#{{ $claim->order?->orderno }}</div>
                                        <div class="small text-muted">{{ $claim->sellerFulfilment?->fulfilment_ref }}</div>
                                    </td>
                                    <td>
                                        @switch($claim->claim_type)
                                            @case('shortage')
                                                <span class="badge bg-danger text-white">Shortage</span>
                                                @break
                                            @case('damage')
                                                <span class="badge bg-warning text-dark">Damage</span>
                                                @break
                                            @case('wrong_item')
                                                <span class="badge bg-info text-white">Wrong Item</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary text-white">{{ ucfirst($claim->claim_type) }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <div class="small text-dark" style="max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            {{ $claim->description }}
                                        </div>
                                    </td>
                                    <td>
                                        @switch($claim->claim_status)
                                            @case('submitted')
                                                <span class="badge bg-warning text-dark">Action Required</span>
                                                @break
                                            @case('seller_responded')
                                                <span class="badge bg-info text-white">Responded</span>
                                                @break
                                            @case('under_review')
                                                <span class="badge bg-primary text-white">Under Review</span>
                                                @break
                                            @case('resolved_settled')
                                                <span class="badge bg-success text-white">Resolved</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge bg-danger text-white">Rejected</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary text-white">{{ ucfirst($claim->claim_status) }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <a href="{{ route('seller.claims.show', $claim->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-show"></i> Review & Respond
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No shortage or damage claims found for your seller orders.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $claims->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

