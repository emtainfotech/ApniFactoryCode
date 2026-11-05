@extends('layout')
@section('title', 'Marketplace Logistics & Multi-Seller Fulfilments')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Logistics</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.commissions.rules.index') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Multi-Seller Fulfilments & Dispatch</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Commercial Terms Reminder Alert -->
        <div class="alert alert-info border-0 bg-light-info py-2 mb-3">
            <div class="d-flex align-items-center">
                <div class="font-24 text-info me-2"><i class="bx bx-info-circle"></i></div>
                <div class="text-dark small">
                    <strong>Commercial Operating Model:</strong> Manufacturer dispatches to agreed factory gate or Transport Nagar handover point.
                    All freight is <strong>To-Pay (Buyer-Paid Freight)</strong>. Platform does NOT operate transport fleet or deduct platform freight charges.
                    LR / Bilty upload is dispatch evidence and does not constitute proof of delivery.
                </div>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-5 mb-3">
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Fulfilments</p>
                                <h4 class="my-1 text-primary">{{ $metrics['total'] }}</h4>
                                <p class="mb-0 font-13">All Seller Consignments</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-primary text-primary ms-auto">
                                <i class="bx bx-package"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Pending Dispatch</p>
                                <h4 class="my-1 text-warning">{{ $metrics['pending'] }}</h4>
                                <p class="mb-0 font-13">Awaiting Packing/Dispatch</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-warning text-warning ms-auto">
                                <i class="bx bx-time-five"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">In-Transit / Dispatched</p>
                                <h4 class="my-1 text-info">{{ $metrics['in_transit'] }}</h4>
                                <p class="mb-0 font-13">Transporter Handed Over</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-info text-info ms-auto">
                                <i class="bx bx-car"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Buyer Acknowledged</p>
                                <h4 class="my-1 text-success">{{ $metrics['acknowledged'] }}</h4>
                                <p class="mb-0 font-13">Receipt Confirmed</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto">
                                <i class="bx bx-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Reported Issues</p>
                                <h4 class="my-1 text-danger">{{ $metrics['disputes'] }}</h4>
                                <p class="mb-0 font-13">Shortage / Damage</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto">
                                <i class="bx bx-error-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Form -->
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('admin.logistics.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search Order #, Fulfilment Ref, LR, Transporter..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">-- All Fulfilment Statuses --</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Dispatch</option>
                            <option value="dispatched" {{ request('status') === 'dispatched' ? 'selected' : '' }}>Dispatched / In-Transit</option>
                            <option value="buyer_acknowledged" {{ request('status') === 'buyer_acknowledged' ? 'selected' : '' }}>Buyer Acknowledged</option>
                            <option value="disputed" {{ request('status') === 'disputed' ? 'selected' : '' }}>Disputed / Shortage / Damage</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="seller_id" class="form-select">
                            <option value="">-- All Sellers --</option>
                            @foreach($sellers as $seller)
                                <option value="{{ $seller->id }}" {{ request('seller_id') == $seller->id ? 'selected' : '' }}>
                                    {{ $seller->name }} ({{ $seller->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-filter-alt"></i> Filter</button>
                        <a href="{{ route('admin.logistics.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Fulfilments Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Fulfilment Ref / Order</th>
                                <th>Seller (Factory Origin)</th>
                                <th>Buyer (Handover Point)</th>
                                <th>Transporter & LR</th>
                                <th>Freight Terms</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fulfilments as $f)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-primary">{{ $f->fulfilment_ref }}</div>
                                        <div class="small text-muted">
                                            Order: #{{ $f->order?->orderno }}
                                            @if($f->parent_order_id && $f->parent_order_id != $f->order_id)
                                                <span class="badge bg-light text-dark">Parent #{{ $f->parent_order_id }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $f->company?->name ?? $f->seller?->name ?? 'Seller #' . $f->seller_user_id }}</div>
                                        <div class="small text-muted">{{ $f->dispatch_origin_city ?: $f->company?->city }}, {{ $f->dispatch_origin_state ?: $f->company?->state }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $f->customer?->name ?? 'Buyer #' . $f->customer_id }}</div>
                                        <div class="small text-muted">{{ $f->handover_point_name ?: 'Transport Nagar' }} ({{ $f->handover_point_city }})</div>
                                    </td>
                                    <td>
                                        @if($f->transporter_name)
                                            <div class="fw-bold">{{ $f->transporter_name }}</div>
                                            <div class="small text-secondary">
                                                LR: <span class="badge bg-light-secondary text-dark">{{ $f->lr_number ?: 'Not Set' }}</span>
                                                @if($f->lr_document_path)
                                                    <a href="{{ asset($f->lr_document_path) }}" target="_blank" class="ms-1 text-primary" title="View LR / Bilty">
                                                        <i class="bx bx-file"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted small">Not Assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light-primary text-primary">{{ $f->freight_terms }}</span>
                                    </td>
                                    <td>
                                        @switch($f->fulfilment_status)
                                            @case('pending_acceptance')
                                                <span class="badge bg-warning text-dark">Pending Acceptance</span>
                                                @break
                                            @case('accepted')
                                                <span class="badge bg-info text-white">Accepted</span>
                                                @break
                                            @case('processing_packed')
                                                <span class="badge bg-secondary text-white">Packed & Ready</span>
                                                @break
                                            @case('dispatched')
                                                <span class="badge bg-primary text-white">Dispatched</span>
                                                @break
                                            @case('buyer_acknowledged')
                                                <span class="badge bg-success text-white">Buyer Acknowledged</span>
                                                @break
                                            @default
                                                <span class="badge bg-dark text-white">{{ ucfirst($f->fulfilment_status) }}</span>
                                        @endswitch

                                        @if($f->dispute_status)
                                            <div class="mt-1">
                                                <span class="badge bg-danger text-white small">{{ str_replace('_', ' ', ucfirst($f->dispute_status)) }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.logistics.show', $f->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-show"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        No seller fulfilment records found matching filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $fulfilments->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
