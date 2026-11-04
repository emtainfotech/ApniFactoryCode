@extends('layout')
@section('title', 'Submission Details: ' . $product->name)
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
                        <li class="breadcrumb-item"><a href="{{ route('seller.catalogue.submissions') }}">Submissions</a></li>
                        <li class="breadcrumb-item active" aria-current="page">#{{ $product->id }} - {{ $product->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('seller.catalogue.submissions') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Submissions
                </a>
                <form method="POST" action="{{ route('seller.catalogue.submissions.validate', $product->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-info">
                        <i class="fa-solid fa-stethoscope me-1"></i> Re-run Validation
                    </button>
                </form>
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

        <!-- Approval Status Banner -->
        @php
            $alertClass = match($product->approval_status) {
                'approved'              => 'alert-success',
                'pending_review'        => 'alert-warning',
                'resubmitted'           => 'alert-info',
                'corrections_requested' => 'alert-warning',
                'rejected'              => 'alert-danger',
                default                 => 'alert-secondary',
            };
        @endphp
        <div class="alert {{ $alertClass }} d-flex align-items-center justify-content-between p-3 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-circle-info fs-3 me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1 text-capitalize">Status: {{ str_replace('_', ' ', $product->approval_status ?? 'draft') }}</h5>
                    <p class="mb-0 small">
                        @if($product->approval_status === 'approved')
                            This product is approved by catalogue administration and is live on the marketplace.
                        @elseif($product->approval_status === 'pending_review' || $product->approval_status === 'resubmitted')
                            Your submission is queued for administrative review. You will receive a notification once verified.
                        @elseif($product->approval_status === 'corrections_requested')
                            Administrator requested corrections. Please review feedback below, edit product details, and resubmit.
                        @elseif($product->approval_status === 'rejected')
                            This product submission was rejected. Review feedback below before making necessary changes.
                        @else
                            Draft mode. Run validation and submit when product details are complete.
                        @endif
                    </p>
                </div>
            </div>
            <div>
                @if($product->approval_status === 'draft')
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitModal">
                        <i class="fa-solid fa-paper-plane me-1"></i> Submit for Review
                    </button>
                @elseif(in_array($product->approval_status, ['corrections_requested', 'rejected']))
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#resubmitModal">
                        <i class="fa-solid fa-rotate-right me-1"></i> Resubmit Product
                    </button>
                @endif
            </div>
        </div>

        <!-- Admin Feedback Callout (if rejected or corrections requested) -->
        @if($product->rejection_reason || $product->correction_notes)
            <div class="card border-warning mb-4">
                <div class="card-header bg-warning bg-opacity-10 text-dark fw-bold">
                    <i class="fa-solid fa-comment-dots me-2 text-warning"></i> Catalogue Administrator Review Feedback
                </div>
                <div class="card-body">
                    @if($product->rejection_reason)
                        <div class="mb-2">
                            <strong class="text-danger">Rejection Reason:</strong>
                            <p class="mt-1 mb-0 p-2 bg-light rounded border">{{ $product->rejection_reason }}</p>
                        </div>
                    @endif
                    @if($product->correction_notes)
                        <div>
                            <strong class="text-warning text-dark">Correction Instructions:</strong>
                            <p class="mt-1 mb-0 p-2 bg-light rounded border">{{ $product->correction_notes }}</p>
                        </div>
                    @endif
                    <div class="mt-2 text-muted small">
                        Reviewed by Admin on {{ $product->reviewed_at?->format('d M Y, h:i A') }}
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <!-- Left Column: Core Product Info & Validation Checks -->
            <div class="col-lg-7">
                <!-- Product Core Info Card -->
                <div class="card mb-4">
                    <div class="card-header bg-white py-3 fw-bold">
                        <i class="fa-solid fa-box-open me-2 text-primary"></i> Product Core Information
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3 text-center">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" class="img-fluid rounded border mb-2" style="max-height: 120px; object-fit: contain;">
                                @else
                                    <div class="rounded border p-4 bg-light text-muted"><i class="fa-solid fa-image fs-1"></i></div>
                                @endif
                                <div class="badge bg-light text-dark border w-100">ID: #{{ $product->id }}</div>
                            </div>
                            <div class="col-md-9">
                                <h5>{{ $product->name }}</h5>
                                <p class="text-muted small mb-2">{{ $product->title }}</p>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <small class="text-muted d-block">Category:</small>
                                        <strong>{{ $product->category?->name ?? 'None' }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Subcategory:</small>
                                        <strong>{{ $product->subcategory?->name ?? 'None' }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Brand:</small>
                                        <strong>{{ $product->brand?->name ?? 'None' }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Catalogue Status:</small>
                                        <span class="badge {{ $product->status === 'Active' ? 'bg-success' : 'bg-secondary' }}">{{ $product->status }}</span>
                                    </div>
                                </div>
                                @if($product->description)
                                    <div class="mt-2">
                                        <small class="text-muted d-block">Description:</small>
                                        <div class="small p-2 bg-light rounded border text-muted">{!! nl2br(e(Str::limit($product->description, 250))) !!}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Catalogue Validation Results Card -->
                @if($latest_validation)
                    <div class="card mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <div class="fw-bold">
                                <i class="fa-solid fa-list-check me-2 text-info"></i> Automated Catalogue Validation
                            </div>
                            <div>
                                @if($latest_validation->isPassed())
                                    <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Passed</span>
                                @else
                                    <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i> {{ $latest_validation->error_count }} Blocking Error(s)</span>
                                @endif
                                @if($latest_validation->warning_count > 0)
                                    <span class="badge bg-warning text-dark ms-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> {{ $latest_validation->warning_count }} Warning(s)</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Blocking Errors -->
                            @if(!empty($latest_validation->failed_checks))
                                <div class="mb-3">
                                    <h6 class="text-danger"><i class="fa-solid fa-circle-exclamation me-1"></i> Mandatory Errors (Must Fix Before Submission):</h6>
                                    <ul class="list-group">
                                        @foreach($latest_validation->failed_checks as $f)
                                            <li class="list-group-item list-group-item-danger d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>[{{ $f['field'] ?? 'Field' }}]:</strong> {{ $f['message'] ?? 'Validation failed' }}
                                                </div>
                                                <span class="badge bg-danger">Blocking</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Warnings -->
                            @if(!empty($latest_validation->warnings))
                                <div class="mb-3">
                                    <h6 class="text-warning text-dark"><i class="fa-solid fa-triangle-exclamation me-1"></i> Recommendations / Warnings:</h6>
                                    <ul class="list-group">
                                        @foreach($latest_validation->warnings as $w)
                                            <li class="list-group-item list-group-item-warning">
                                                <strong>[{{ $w['field'] ?? 'Field' }}]:</strong> {{ $w['message'] ?? 'Warning' }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Passed Checks -->
                            @if(!empty($latest_validation->passed_checks))
                                <div>
                                    <h6 class="text-success"><i class="fa-solid fa-circle-check me-1"></i> Passed Verifications ({{ count($latest_validation->passed_checks) }}):</h6>
                                    <ul class="list-group list-group-flush small">
                                        @foreach($latest_validation->passed_checks as $pcheck)
                                            <li class="list-group-item py-1 text-success">
                                                <i class="fa-solid fa-check me-2"></i> {{ $pcheck }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column: Dynamic Attributes, Variants, Listings, Review History -->
            <div class="col-lg-5">
                <!-- Dynamic Category Attributes Card -->
                <div class="card mb-4">
                    <div class="card-header bg-white py-3 fw-bold">
                        <i class="fa-solid fa-tags me-2 text-primary"></i> Dynamic Category Attributes
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Attribute</th>
                                    <th>Configured Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dynamic_attributes as $av)
                                    <tr>
                                        <td><strong>{{ $av->attribute?->name ?? 'Attribute #' . $av->attribute_id }}</strong></td>
                                        <td>
                                            @if($av->option)
                                                <span class="badge bg-primary">{{ $av->option->value }}</span>
                                            @elseif(!empty($av->text_value))
                                                <span>{{ $av->text_value }}</span>
                                            @elseif(!is_null($av->number_value))
                                                <span>{{ $av->number_value }}</span>
                                            @else
                                                <span class="text-muted italic">Empty</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3">No dynamic attributes configured for this product.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Seller Listings / SKUs Card -->
                <div class="card mb-4">
                    <div class="card-header bg-white py-3 fw-bold">
                        <i class="fa-solid fa-barcode me-2 text-success"></i> Seller SKU Listings ({{ $listings->count() }})
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>SKU</th>
                                    <th>Price</th>
                                    <th>MRP</th>
                                    <th>MOQ</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($listings as $listing)
                                    <tr>
                                        <td><code>{{ $listing->seller_sku ?: 'N/A' }}</code></td>
                                        <td><strong>₹{{ number_format($listing->selling_price, 2) }}</strong></td>
                                        <td>₹{{ number_format($listing->mrp, 2) }}</td>
                                        <td>{{ $listing->min_order_quantity }}</td>
                                        <td>
                                            <span class="badge {{ $listing->status === 'Active' ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $listing->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No seller SKU listings registered.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Review History Audit Trail -->
                <div class="card mb-4">
                    <div class="card-header bg-white py-3 fw-bold">
                        <i class="fa-solid fa-timeline me-2 text-secondary"></i> Review Audit History
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse($review_history as $hist)
                                @php
                                    $histBadge = match($hist->action) {
                                        'approved'              => 'bg-success',
                                        'submitted'             => 'bg-primary',
                                        'resubmitted'           => 'bg-info text-dark',
                                        'rejected'              => 'bg-danger',
                                        'corrections_requested' => 'bg-warning',
                                        default                 => 'bg-secondary',
                                    };
                                @endphp
                                <li class="list-group-item p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge {{ $histBadge }} text-capitalize">{{ str_replace('_', ' ', $hist->action) }}</span>
                                        <small class="text-muted">{{ $hist->created_at?->format('d M Y, h:i A') }}</small>
                                    </div>
                                    <div class="small text-muted mb-1">
                                        Transition: <code>{{ $hist->previous_status }}</code> &rarr; <code>{{ $hist->new_status }}</code>
                                    </div>
                                    @if($hist->notes)
                                        <div class="small p-2 bg-light rounded border">{{ $hist->notes }}</div>
                                    @endif
                                </li>
                            @empty
                                <li class="list-group-item text-center text-muted py-3">No review history yet.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Modal -->
        @if($product->approval_status === 'draft')
            <div class="modal fade" id="submitModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST" action="{{ route('seller.catalogue.submissions.submit', $product->id) }}">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Submit for Administrator Review</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Submit <strong>{{ $product->name }}</strong> for administrator verification?</p>
                                <div class="mb-3">
                                    <label class="form-label">Notes for Administrator (Optional)</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Context or remarks..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane me-1"></i> Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Resubmit Modal -->
        @if(in_array($product->approval_status, ['corrections_requested', 'rejected']))
            <div class="modal fade" id="resubmitModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST" action="{{ route('seller.catalogue.submissions.resubmit', $product->id) }}">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Resubmit Corrected Product</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-info py-2">
                                    <strong>Admin Feedback:</strong>
                                    <div>{{ $product->correction_notes ?: $product->rejection_reason }}</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Correction Summary / Notes</label>
                                    <textarea name="notes" class="form-control" rows="3" required placeholder="Describe what corrections you performed..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-warning"><i class="fa-solid fa-rotate-right me-1"></i> Resubmit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection

