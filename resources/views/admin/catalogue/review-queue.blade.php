@extends('layout')
@section('title', 'Admin Catalogue Review Queue')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Administration</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ url('seller/dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Catalogue Review Queue</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto d-flex gap-2">
                <span class="badge bg-danger fs-6 py-2 px-3">
                    <i class="fa-solid fa-clock me-1"></i> {{ $counts['pending'] }} Awaiting Review
                </span>
            </div>
        </div>
        <!--end breadcrumb-->

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Status Filter Tabs -->
        <div class="card mb-3">
            <div class="card-body py-2">
                <ul class="nav nav-pills nav-fill">
                    <li class="nav-item">
                        <a class="nav-link {{ $currentStatus === 'pending' ? 'active' : '' }}" href="{{ route('admin.catalogue.reviews', ['status' => 'pending']) }}">
                            Needs Review <span class="badge bg-danger ms-1">{{ $counts['pending'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $currentStatus === 'resubmitted' ? 'active' : '' }}" href="{{ route('admin.catalogue.reviews', ['status' => 'resubmitted']) }}">
                            Resubmitted <span class="badge bg-info text-dark ms-1">{{ $counts['resubmitted'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $currentStatus === 'corrections_requested' ? 'active' : '' }}" href="{{ route('admin.catalogue.reviews', ['status' => 'corrections_requested']) }}">
                            Corrections Requested <span class="badge bg-warning ms-1">{{ $counts['corrections_requested'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $currentStatus === 'approved' ? 'active' : '' }}" href="{{ route('admin.catalogue.reviews', ['status' => 'approved']) }}">
                            Approved <span class="badge bg-success ms-1">{{ $counts['approved'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $currentStatus === 'rejected' ? 'active' : '' }}" href="{{ route('admin.catalogue.reviews', ['status' => 'rejected']) }}">
                            Rejected <span class="badge bg-secondary ms-1">{{ $counts['rejected'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $currentStatus === 'all' ? 'active' : '' }}" href="{{ route('admin.catalogue.reviews', ['status' => 'all']) }}">
                            All Products <span class="badge bg-secondary ms-1">{{ $counts['all'] }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.catalogue.reviews') }}" class="row g-2 align-items-end">
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Search Keywords</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Product name, SKU, seller..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Category</label>
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Brand</label>
                        <select name="brand_id" class="form-select form-select-sm">
                            <option value="">All Brands</option>
                            @foreach($brands as $b)
                                <option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Seller Company</label>
                        <select name="company_id" class="form-select form-select-sm">
                            <option value="">All Sellers</option>
                            @foreach($companies as $c)
                                <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-primary flex-grow-1"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                        <a href="{{ route('admin.catalogue.reviews', ['status' => $currentStatus]) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Review Queue Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Product Details</th>
                                <th>Seller Company</th>
                                <th>Category & Brand</th>
                                <th>Variants / SKUs</th>
                                <th>Status</th>
                                <th>Submitted At</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $p)
                                @php
                                    $statusBadge = match($p->approval_status) {
                                        'approved'              => 'bg-success',
                                        'pending_review'        => 'bg-danger',
                                        'resubmitted'           => 'bg-info text-dark',
                                        'corrections_requested' => 'bg-warning',
                                        'rejected'              => 'bg-secondary',
                                        default                 => 'bg-light text-dark border',
                                    };
                                    $sellerCompany = $p->user?->company;
                                @endphp
                                <tr>
                                    <td><strong>#{{ $p->id }}</strong></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($p->image)
                                                <img src="{{ asset('storage/' . $p->image) }}" class="rounded me-2 border" style="width: 44px; height: 44px; object-fit: cover;">
                                            @else
                                                <div class="rounded me-2 border d-flex align-items-center justify-content-center bg-light text-muted" style="width: 44px; height: 44px;">
                                                    <i class="fa-solid fa-image"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <a href="{{ route('admin.catalogue.reviews.show', $p->id) }}" class="fw-bold text-dark text-decoration-none">
                                                    {{ $p->name }}
                                                </a>
                                                <div class="text-muted small">{{ Str::limit($p->title, 40) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $sellerCompany?->name ?? 'Company #' . ($p->user_id) }}</div>
                                        <small class="text-muted">{{ $p->user?->email }}</small>
                                    </td>
                                    <td>
                                        <div><span class="badge bg-light text-dark border">{{ $p->category?->name ?? 'N/A' }}</span></div>
                                        <small class="text-muted">{{ $p->brand?->name ?? 'No Brand' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-primary border me-1">{{ $p->variants_count }} Var</span>
                                        <span class="badge bg-light text-success border">{{ $p->listings_count }} SKU</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $statusBadge }} text-capitalize px-2 py-1">
                                            {{ str_replace('_', ' ', $p->approval_status ?? 'draft') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($p->submitted_at)
                                            <div>{{ $p->submitted_at->format('d M Y') }}</div>
                                            <small class="text-muted">{{ $p->submitted_at->diffForHumans() }}</small>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <!-- Inspect button -->
                                            <a href="{{ route('admin.catalogue.reviews.show', $p->id) }}" class="btn btn-outline-primary" title="Inspect & Review">
                                                <i class="fa-solid fa-magnifying-glass me-1"></i> Review
                                            </a>

                                            @if(in_array($p->approval_status, ['pending_review', 'resubmitted', 'draft']))
                                                <!-- Quick Approve Form -->
                                                <form method="POST" action="{{ route('admin.catalogue.reviews.approve', $p->id) }}" class="d-inline" onsubmit="return confirm('Approve this product and publish live to marketplace?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success" title="Approve & Publish Live">
                                                        <i class="fa-solid fa-check"></i>
                                                    </button>
                                                </form>

                                                <!-- Request Corrections Trigger -->
                                                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#correctModal{{ $p->id }}" title="Request Corrections">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </button>

                                                <!-- Reject Trigger -->
                                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $p->id }}" title="Reject Submission">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            @endif
                                        </div>

                                        <!-- Corrections Modal -->
                                        @if(in_array($p->approval_status, ['pending_review', 'resubmitted', 'draft']))
                                            <div class="modal fade text-start" id="correctModal{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form method="POST" action="{{ route('admin.catalogue.reviews.request-corrections', $p->id) }}">
                                                        @csrf
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Request Corrections: {{ $p->name }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p class="small text-muted">The product will be set to <strong>Corrections Requested</strong> and returned to the seller with your feedback.</p>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Required Corrections / Instructions <span class="text-danger">*</span></label>
                                                                    <textarea name="instructions" class="form-control" rows="4" required placeholder="Specify what the seller must fix (e.g. Please provide high-resolution images, update incorrect dynamic attribute value)..."></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-warning"><i class="fa-solid fa-paper-plane me-1"></i> Send Feedback to Seller</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                            <!-- Reject Modal -->
                                            <div class="modal fade text-start" id="rejectModal{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form method="POST" action="{{ route('admin.catalogue.reviews.reject', $p->id) }}">
                                                        @csrf
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title text-danger">Reject Product Submission</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p class="small text-muted">The product will be marked as <strong>Rejected</strong> and associated listings will be deactivated.</p>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Mandatory Rejection Reason <span class="text-danger">*</span></label>
                                                                    <textarea name="reason" class="form-control" rows="4" required placeholder="State why this catalogue listing cannot be approved..."></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-ban me-1"></i> Reject Product</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-check-double fs-2 d-block mb-2 text-success"></i>
                                        No review requests currently in this queue!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $products->links() }}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

