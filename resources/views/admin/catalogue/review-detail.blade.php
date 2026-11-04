@extends('layout')
@section('title', 'Admin Review: ' . $product->name)
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
                        <li class="breadcrumb-item"><a href="{{ route('admin.catalogue.reviews') }}">Review Queue</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Review #{{ $product->id }} - {{ $product->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('admin.catalogue.reviews') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Queue
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

        <!-- Admin Action Toolbar Card -->
        <div class="card border-primary shadow-sm mb-4">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center">
                    @php
                        $badgeBg = match($product->approval_status) {
                            'approved'              => 'bg-success',
                            'pending_review'        => 'bg-danger',
                            'resubmitted'           => 'bg-info text-dark',
                            'corrections_requested' => 'bg-warning',
                            'rejected'              => 'bg-secondary',
                            default                 => 'bg-light text-dark border',
                        };
                    @endphp
                    <span class="badge {{ $badgeBg }} fs-6 px-3 py-2 text-capitalize me-3">
                        Status: {{ str_replace('_', ' ', $product->approval_status) }}
                    </span>
                    <div>
                        <div class="fw-bold">Seller Company: {{ $company?->name ?? 'User #' . $product->user_id }}</div>
                        <small class="text-muted">Submitted: {{ $product->submitted_at?->format('d M Y, h:i A') ?? 'Not submitted' }}</small>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <!-- Approve Button -->
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                        <i class="fa-solid fa-check me-1"></i> Approve & Publish Live
                    </button>

                    <!-- Request Corrections Button -->
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#correctModal">
                        <i class="fa-solid fa-rotate-left me-1"></i> Request Corrections
                    </button>

                    <!-- Reject Button -->
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="fa-solid fa-xmark me-1"></i> Reject
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column: Product Details & Automated Checks -->
            <div class="col-lg-7">
                <!-- Product Overview Card -->
                <div class="card mb-4">
                    <div class="card-header bg-white py-3 fw-bold">
                        <i class="fa-solid fa-box-open me-2 text-primary"></i> Product Core Profile
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3 text-center">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" class="img-fluid rounded border mb-2" style="max-height: 140px; object-fit: contain;">
                                @else
                                    <div class="rounded border p-4 bg-light text-muted"><i class="fa-solid fa-image fs-1"></i></div>
                                @endif
                                <span class="badge bg-light text-dark border w-100">Product #{{ $product->id }}</span>
                            </div>
                            <div class="col-md-9">
                                <h4>{{ $product->name }}</h4>
                                <div class="text-muted mb-3">{{ $product->title }}</div>

                                <div class="row g-2 mb-3">
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">Category:</small>
                                        <strong>{{ $product->category?->name ?? 'None' }}</strong>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">Subcategory:</small>
                                        <strong>{{ $product->subcategory?->name ?? 'None' }}</strong>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">Brand:</small>
                                        <strong>{{ $product->brand?->name ?? 'None' }}</strong>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">Seller Company:</small>
                                        <strong>{{ $company?->name ?? 'N/A' }}</strong>
                                    </div>
                                </div>

                                @if($product->description)
                                    <div>
                                        <small class="text-muted d-block mb-1">Product Description:</small>
                                        <div class="p-2 bg-light rounded border small">{!! nl2br(e($product->description)) !!}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Automated Catalogue Validation Results -->
                @if($latest_validation)
                    <div class="card mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <div class="fw-bold">
                                <i class="fa-solid fa-stethoscope me-2 text-info"></i> Automated Catalogue Diagnostics
                            </div>
                            <div>
                                @if($latest_validation->isPassed())
                                    <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Passed Validation</span>
                                @else
                                    <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i> {{ $latest_validation->error_count }} Error(s)</span>
                                @endif
                                @if($latest_validation->warning_count > 0)
                                    <span class="badge bg-warning text-dark ms-1">{{ $latest_validation->warning_count }} Warning(s)</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            @if(!empty($latest_validation->failed_checks))
                                <div class="mb-3">
                                    <h6 class="text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> Validation Failures (Blocking):</h6>
                                    <ul class="list-group">
                                        @foreach($latest_validation->failed_checks as $fc)
                                            <li class="list-group-item list-group-item-danger">
                                                <strong>[{{ $fc['field'] ?? 'Rule' }}]:</strong> {{ $fc['message'] ?? 'Error' }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(!empty($latest_validation->warnings))
                                <div class="mb-3">
                                    <h6 class="text-warning text-dark"><i class="fa-solid fa-circle-exclamation me-1"></i> Warnings & Advisories:</h6>
                                    <ul class="list-group">
                                        @foreach($latest_validation->warnings as $wn)
                                            <li class="list-group-item list-group-item-warning">
                                                <strong>[{{ $wn['field'] ?? 'Advisory' }}]:</strong> {{ $wn['message'] ?? 'Warning' }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(!empty($latest_validation->passed_checks))
                                <div>
                                    <h6 class="text-success"><i class="fa-solid fa-check-double me-1"></i> Verified Criteria:</h6>
                                    <ul class="list-group list-group-flush small">
                                        @foreach($latest_validation->passed_checks as $pc)
                                            <li class="list-group-item py-1 text-success">
                                                <i class="fa-solid fa-circle-check me-2"></i> {{ $pc }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column: Dynamic Attributes, Listings & Audit History -->
            <div class="col-lg-5">
                <!-- Dynamic Category Attributes -->
                <div class="card mb-4">
                    <div class="card-header bg-white py-3 fw-bold">
                        <i class="fa-solid fa-sliders me-2 text-primary"></i> Dynamic Attributes ({{ $dynamic_attributes->count() }})
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Attribute</th>
                                    <th>Assigned Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dynamic_attributes as $attrVal)
                                    <tr>
                                        <td><strong>{{ $attrVal->attribute?->name ?? 'Attr #' . $attrVal->attribute_id }}</strong></td>
                                        <td>
                                            @if($attrVal->option)
                                                <span class="badge bg-primary">{{ $attrVal->option->value }}</span>
                                            @elseif(!empty($attrVal->text_value))
                                                <span>{{ $attrVal->text_value }}</span>
                                            @elseif(!is_null($attrVal->number_value))
                                                <span>{{ $attrVal->number_value }}</span>
                                            @else
                                                <span class="text-muted italic">Empty</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3">No dynamic attributes configured.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Seller SKU Listings Table -->
                <div class="card mb-4">
                    <div class="card-header bg-white py-3 fw-bold">
                        <i class="fa-solid fa-tags me-2 text-success"></i> Seller SKU Listings ({{ $listings->count() }})
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>SKU</th>
                                    <th>Selling Price</th>
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
                                        <td colspan="5" class="text-center text-muted py-3">No sellable listings found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Review Audit Trail -->
                <div class="card mb-4">
                    <div class="card-header bg-white py-3 fw-bold">
                        <i class="fa-solid fa-clock-rotate-left me-2 text-secondary"></i> Review Audit Trail
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse($review_history as $rh)
                                @php
                                    $actionBadge = match($rh->action) {
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
                                        <span class="badge {{ $actionBadge }} text-capitalize">{{ str_replace('_', ' ', $rh->action) }}</span>
                                        <small class="text-muted">{{ $rh->created_at?->format('d M Y, h:i A') }}</small>
                                    </div>
                                    <div class="small text-muted mb-1">
                                        By: <strong>{{ $rh->reviewer?->name ?? $rh->seller?->name ?? 'System' }}</strong>
                                        &bull; Transition: <code>{{ $rh->previous_status }}</code> &rarr; <code>{{ $rh->new_status }}</code>
                                    </div>
                                    @if($rh->notes)
                                        <div class="p-2 bg-light rounded border small">{{ $rh->notes }}</div>
                                    @endif
                                </li>
                            @empty
                                <li class="list-group-item text-center text-muted py-3">No review history logged yet.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approve Modal -->
        <div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('admin.catalogue.reviews.approve', $product->id) }}">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title text-success"><i class="fa-solid fa-check-circle me-1"></i> Approve Product & Publish Live</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to approve <strong>{{ $product->name }}</strong>?</p>
                            <p class="small text-muted">This will publish the product to the marketplace, activate all compliant seller listings, and notify the seller company.</p>
                            <div class="mb-3">
                                <label class="form-label">Approval Notes (Optional)</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Optional internal review or publication remarks..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success"><i class="fa-solid fa-check me-1"></i> Confirm Approval</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Request Corrections Modal -->
        <div class="modal fade" id="correctModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('admin.catalogue.reviews.request-corrections', $product->id) }}">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title text-warning"><i class="fa-solid fa-rotate-left me-1"></i> Request Corrections</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-muted">Product status will be updated to <strong>Corrections Requested</strong> and returned to the seller with your feedback.</p>
                            <div class="mb-3">
                                <label class="form-label">Correction Instructions <span class="text-danger">*</span></label>
                                <textarea name="instructions" class="form-control" rows="4" required placeholder="Specify what the seller must fix before resubmitting..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning"><i class="fa-solid fa-paper-plane me-1"></i> Send Instructions</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('admin.catalogue.reviews.reject', $product->id) }}">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title text-danger"><i class="fa-solid fa-ban me-1"></i> Reject Product Submission</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-muted">Product will be marked as <strong>Rejected</strong> and removed from the active queue. Associated listings will be deactivated.</p>
                            <div class="mb-3">
                                <label class="form-label">Mandatory Rejection Reason <span class="text-danger">*</span></label>
                                <textarea name="reason" class="form-control" rows="4" required placeholder="State the reason why this product was rejected..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger"><i class="fa-solid fa-ban me-1"></i> Confirm Rejection</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

