@extends('layout')
@section('title', 'Marketplace Claims & Consignment Disputes')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Operations</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.logistics.index') }}"><i class="bx bx-home-alt"></i> Logistics</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Consignment Claims & Disputes</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-5 mb-3">
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Claims</p>
                                <h4 class="my-1 text-primary">{{ $metrics['total_claims'] }}</h4>
                                <p class="mb-0 font-13">All Lodged Issues</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-primary text-primary ms-auto">
                                <i class="bx bx-file"></i>
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
                                <p class="mb-0 text-secondary">Pending Operations</p>
                                <h4 class="my-1 text-warning">{{ $metrics['pending_review'] }}</h4>
                                <p class="mb-0 font-13">Action Required</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-warning text-warning ms-auto">
                                <i class="bx bx-time-five"></i>
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
                                <p class="mb-0 text-secondary">Shortage Claims</p>
                                <h4 class="my-1 text-danger">{{ $metrics['shortage_claims'] }}</h4>
                                <p class="mb-0 font-13">Missing Units</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto">
                                <i class="bx bx-minus-circle"></i>
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
                                <p class="mb-0 text-secondary">Damage Claims</p>
                                <h4 class="my-1 text-info">{{ $metrics['damage_claims'] }}</h4>
                                <p class="mb-0 font-13">Physical Damage</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-info text-info ms-auto">
                                <i class="bx bx-package"></i>
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
                                <p class="mb-0 text-secondary">Resolved / Closed</p>
                                <h4 class="my-1 text-success">{{ $metrics['resolved_claims'] }}</h4>
                                <p class="mb-0 font-13">Settled Cases</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto">
                                <i class="bx bx-check-shield"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Form -->
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ route('admin.claims.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search Claim #, Order #..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">-- All Statuses --</option>
                            <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted (Pending)</option>
                            <option value="seller_responded" {{ request('status') === 'seller_responded' ? 'selected' : '' }}>Seller Responded</option>
                            <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                            <option value="resolved_settled" {{ request('status') === 'resolved_settled' ? 'selected' : '' }}>Resolved</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="claim_type" class="form-select">
                            <option value="">-- All Categories --</option>
                            <option value="shortage" {{ request('claim_type') === 'shortage' ? 'selected' : '' }}>Shortage</option>
                            <option value="damage" {{ request('claim_type') === 'damage' ? 'selected' : '' }}>Damage</option>
                            <option value="wrong_item" {{ request('claim_type') === 'wrong_item' ? 'selected' : '' }}>Wrong Item</option>
                            <option value="other" {{ request('claim_type') === 'other' ? 'selected' : '' }}>Other</option>
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
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="bx bx-filter-alt"></i> Filter</button>
                        <a href="{{ route('admin.claims.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Claims Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Claim Ref / Date</th>
                                <th>Order / Fulfilment</th>
                                <th>Seller & Buyer</th>
                                <th>Category</th>
                                <th>Status</th>
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
                                        <div class="fw-bold">Order #{{ $claim->order?->orderno }}</div>
                                        <div class="small text-muted">Consignment: {{ $claim->sellerFulfilment?->fulfilment_ref }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $claim->sellerFulfilment?->company?->name ?? $claim->seller?->name }}</div>
                                        <div class="small text-muted">Buyer: {{ $claim->customer?->name }}</div>
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
                                        @switch($claim->claim_status)
                                            @case('submitted')
                                                <span class="badge bg-warning text-dark">Pending Ops Review</span>
                                                @break
                                            @case('seller_responded')
                                                <span class="badge bg-info text-white">Seller Responded</span>
                                                @break
                                            @case('under_review')
                                                <span class="badge bg-primary text-white">Under Investigation</span>
                                                @break
                                            @case('resolved_settled')
                                                <span class="badge bg-success text-white">Resolved / Settled</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge bg-danger text-white">Rejected</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary text-white">{{ ucfirst($claim->claim_status) }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.claims.show', $claim->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-show"></i> Review & Adjudicate
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No consignment claims or disputes found matching criteria.</td>
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

