@extends('layout')
@section('title', 'Catalogue Approval Submissions')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Seller Portal</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ url('seller/dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('product.list') }}">Products</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Catalogue Submissions & Approvals</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('seller.catalogue.bulk') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-list-check me-1"></i> Bulk Manager
                </a>
                <a href="{{ url('seller/product/add') }}" class="btn btn-primary">
                    <i class="fa-solid fa-plus-circle me-1"></i> Create Product
                </a>
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
                        <a class="nav-link {{ $currentStatus === 'all' ? 'active' : '' }}" href="{{ route('seller.catalogue.submissions', ['status' => 'all']) }}">
                            All Products <span class="badge bg-secondary ms-1">{{ $counts['all'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $currentStatus === 'draft' ? 'active' : '' }}" href="{{ route('seller.catalogue.submissions', ['status' => 'draft']) }}">
                            Drafts <span class="badge bg-secondary ms-1">{{ $counts['draft'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $currentStatus === 'pending_review' ? 'active' : '' }}" href="{{ route('seller.catalogue.submissions', ['status' => 'pending_review']) }}">
                            Pending Review <span class="badge bg-warning text-dark ms-1">{{ $counts['pending_review'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $currentStatus === 'resubmitted' ? 'active' : '' }}" href="{{ route('seller.catalogue.submissions', ['status' => 'resubmitted']) }}">
                            Resubmitted <span class="badge bg-info text-dark ms-1">{{ $counts['resubmitted'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $currentStatus === 'corrections_requested' ? 'active' : '' }}" href="{{ route('seller.catalogue.submissions', ['status' => 'corrections_requested']) }}">
                            Corrections Requested <span class="badge bg-warning ms-1">{{ $counts['corrections_requested'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $currentStatus === 'rejected' ? 'active' : '' }}" href="{{ route('seller.catalogue.submissions', ['status' => 'rejected']) }}">
                            Rejected <span class="badge bg-danger ms-1">{{ $counts['rejected'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $currentStatus === 'approved' ? 'active' : '' }}" href="{{ route('seller.catalogue.submissions', ['status' => 'approved']) }}">
                            Approved & Live <span class="badge bg-success ms-1">{{ $counts['approved'] }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Filter & Search Card -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('seller.catalogue.submissions') }}" class="row g-2 align-items-center">
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                    <div class="col-md-9">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fa-solid fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search by Product Name, Title, or Seller SKU..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="fa-solid fa-filter me-1"></i> Search</button>
                        @if(request()->has('search'))
                            <a href="{{ route('seller.catalogue.submissions', ['status' => $currentStatus]) }}" class="btn btn-outline-secondary">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Submissions Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Product Details</th>
                                <th>Category & Brand</th>
                                <th>Variants / SKUs</th>
                                <th>Approval Status</th>
                                <th>Last Validated</th>
                                <th>Submitted / Reviewed</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $p)
                                @php
                                    $statusBadge = match($p->approval_status) {
                                        'approved'              => 'bg-success',
                                        'pending_review'        => 'bg-warning text-dark',
                                        'resubmitted'           => 'bg-info text-dark',
                                        'corrections_requested' => 'bg-warning',
                                        'rejected'              => 'bg-danger',
                                        default                 => 'bg-secondary',
                                    };
                                    $latestVal = $p->latestValidationResult;
                                @endphp
                                <tr>
                                    <td><strong>#{{ $p->id }}</strong></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($p->image)
                                                <img src="{{ asset('storage/' . $p->image) }}" alt="img" class="rounded me-2 border" style="width: 44px; height: 44px; object-fit: cover;">
                                            @else
                                                <div class="rounded me-2 border d-flex align-items-center justify-content-center bg-light text-muted" style="width: 44px; height: 44px;">
                                                    <i class="fa-solid fa-image"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold">{{ $p->name }}</div>
                                                <small class="text-muted">{{ Str::limit($p->title, 40) }}</small>
                                            </div>
                                        </div>
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
                                        @if($p->approval_status === 'approved' && $p->status === 'Active')
                                            <span class="badge bg-success-subtle text-success border border-success ms-1"><i class="fa-solid fa-globe me-1"></i>Live</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($latestVal)
                                            @if($latestVal->isPassed())
                                                <span class="badge bg-success-subtle text-success border border-success" title="Passed: {{ count($latestVal->passed_checks) }} rules">
                                                    <i class="fa-solid fa-check me-1"></i>Passed
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger" title="{{ $latestVal->error_count }} blocking errors">
                                                    <i class="fa-solid fa-xmark me-1"></i>{{ $latestVal->error_count }} Error(s)
                                                </span>
                                            @endif
                                            @if($latestVal->warning_count > 0)
                                                <span class="badge bg-warning-subtle text-warning border border-warning" title="{{ $latestVal->warning_count }} warnings">
                                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $latestVal->warning_count }}
                                                </span>
                                            @endif
                                            <div class="small text-muted">{{ $p->last_validated_at?->diffForHumans() }}</div>
                                        @else
                                            <span class="text-muted small">Not yet validated</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($p->submitted_at)
                                            <small class="d-block text-muted">Sub: {{ $p->submitted_at->format('d M Y') }}</small>
                                        @endif
                                        @if($p->reviewed_at)
                                            <small class="d-block text-muted">Rev: {{ $p->reviewed_at->format('d M Y') }}</small>
                                        @endif
                                        @if(!$p->submitted_at && !$p->reviewed_at)
                                            <small class="text-muted">-</small>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <!-- Pre-validate button -->
                                            <form method="POST" action="{{ route('seller.catalogue.submissions.validate', $p->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-info" title="Run Pre-validation">
                                                    <i class="fa-solid fa-stethoscope"></i>
                                                </button>
                                            </form>

                                            <!-- View / Inspect button -->
                                            <a href="{{ route('seller.catalogue.submissions.show', $p->id) }}" class="btn btn-outline-secondary" title="View Details & Feedback">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>

                                            <!-- Submit for Review button (Draft) -->
                                            @if($p->approval_status === 'draft')
                                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#submitModal{{ $p->id }}" title="Submit for Review">
                                                    <i class="fa-solid fa-paper-plane"></i>
                                                </button>
                                            @endif

                                            <!-- Resubmit button (Corrections Requested or Rejected) -->
                                            @if(in_array($p->approval_status, ['corrections_requested', 'rejected']))
                                                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#resubmitModal{{ $p->id }}" title="Resubmit Corrected Product">
                                                    <i class="fa-solid fa-rotate-right"></i>
                                                </button>
                                            @endif
                                        </div>

                                        <!-- Submit Modal -->
                                        @if($p->approval_status === 'draft')
                                            <div class="modal fade text-start" id="submitModal{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form method="POST" action="{{ route('seller.catalogue.submissions.submit', $p->id) }}">
                                                        @csrf
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Submit Product for Admin Review</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>You are about to submit <strong>{{ $p->name }}</strong> for administrator verification.</p>
                                                                <p class="small text-muted">The product will be automatically validated against category dynamic attributes, required images, pricing, and variants. If validation passes, it will enter the admin review queue.</p>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Submission Notes (Optional)</label>
                                                                    <textarea name="notes" class="form-control" rows="3" placeholder="Add any notes or context for the catalogue administrator..."></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane me-1"></i> Submit for Review</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Resubmit Modal -->
                                        @if(in_array($p->approval_status, ['corrections_requested', 'rejected']))
                                            <div class="modal fade text-start" id="resubmitModal{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form method="POST" action="{{ route('seller.catalogue.submissions.resubmit', $p->id) }}">
                                                        @csrf
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Resubmit Corrected Product</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="alert alert-info py-2">
                                                                    <strong>Admin Feedback:</strong>
                                                                    <div>{{ $p->correction_notes ?: $p->rejection_reason ?: 'Please review catalogue guidelines.' }}</div>
                                                                </div>
                                                                <p>Confirm that you have addressed all requested changes before resubmitting.</p>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Correction Summary / Notes</label>
                                                                    <textarea name="notes" class="form-control" rows="3" required placeholder="Describe the corrections made (e.g. Added required dynamic attribute, updated pricing)..."></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-warning"><i class="fa-solid fa-rotate-right me-1"></i> Resubmit for Review</button>
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
                                        <i class="fa-solid fa-clipboard-check fs-2 d-block mb-2"></i>
                                        No catalogue products found matching this filter.
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

