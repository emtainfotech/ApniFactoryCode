@extends('layout')
@section('title', 'Admin Dispute Review - ' . $claim->claim_ref)
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Operations</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.claims.index') }}"><i class="bx bx-home-alt"></i> Claims</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $claim->claim_ref }}</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="{{ route('admin.claims.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-arrow-back"></i> Back to Queue
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

        @if($errors->any())
            <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show py-2">
                <div class="text-white">
                    <strong>Please resolve the following errors:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Policy Boundary Notice -->
        <div class="alert alert-warning border-0 bg-light-warning py-2 mb-3">
            <div class="d-flex align-items-center">
                <div class="font-24 text-warning me-2"><i class="bx bx-info-circle"></i></div>
                <div class="text-dark small">
                    <strong>Operations Governance:</strong> Automated liability decisions, automatic refunds, or captive return pick-ups are strictly disallowed per BRD guidelines.
                    Platform operations adjudicates disputes in consultation with buyer and seller records.
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column: Claim Statement, Line Items, Seller Response -->
            <div class="col-lg-8">
                <!-- Overview Card -->
                <div class="card mb-3">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="bx bx-error-circle me-1 text-danger"></i> Consignment Discrepancy Statement</h6>
                        <span class="badge bg-primary font-14">{{ ucfirst($claim->claim_status) }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <span class="text-muted small">Claim Reference:</span>
                                <div class="fw-bold text-primary">{{ $claim->claim_ref }}</div>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted small">Child Order #:</span>
                                <div class="fw-bold">#{{ $claim->order?->orderno }}</div>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted small">Parent Order #:</span>
                                <div class="fw-bold">#{{ $claim->parent_order_id }}</div>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted small">Issue Category:</span>
                                <div><span class="badge bg-danger">{{ ucfirst($claim->claim_type) }}</span></div>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted small">Manufacturer / Seller:</span>
                                <div class="fw-bold text-dark">{{ $claim->sellerFulfilment?->company?->name ?? $claim->seller?->name }}</div>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted small">Buyer / Customer:</span>
                                <div class="fw-bold text-dark">{{ $claim->customer?->name }}</div>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted small">Transporter / LR:</span>
                                <div class="fw-bold">{{ $claim->sellerFulfilment?->transporter_name ?: 'N/A' }} ({{ $claim->sellerFulfilment?->lr_number ?: 'No LR' }})</div>
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded">
                            <h6 class="fw-bold text-dark small mb-1">Buyer's Reported Description:</h6>
                            <p class="mb-0 text-secondary">{{ $claim->description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Line Items Breakdown -->
                <div class="card mb-3">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0 fw-bold"><i class="bx bx-list-check me-1 text-primary"></i> Reported Affected Line Items</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Ordered Qty</th>
                                        <th>Received Qty</th>
                                        <th>Affected Qty</th>
                                        <th>Condition Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($claim->items as $item)
                                        <tr>
                                            <td class="fw-bold">{{ $item->product_name }}</td>
                                            <td>{{ $item->ordered_qty }}</td>
                                            <td>{{ $item->received_qty }}</td>
                                            <td class="text-danger fw-bold">{{ $item->affected_qty }}</td>
                                            <td class="small text-secondary">{{ $item->condition_notes ?: '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-3 text-muted small">No line items broken down.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Seller Response -->
                <div class="card mb-3">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0 fw-bold"><i class="bx bx-reply me-1 text-primary"></i> Seller Submitted Response</h6>
                    </div>
                    <div class="card-body">
                        @if($claim->seller_response)
                            <div class="bg-light-info p-3 rounded">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-bold text-primary small">Response from Manufacturer:</span>
                                    <span class="text-muted font-11">{{ $claim->seller_responded_at?->format('d M Y, h:i A') }}</span>
                                </div>
                                <p class="mb-0 text-dark">{{ $claim->seller_response }}</p>
                            </div>
                        @else
                            <p class="text-muted small mb-0"><i class="bx bx-time"></i> Seller has not submitted clarification remarks yet.</p>
                        @endif
                    </div>
                </div>

                <!-- Operations Adjudication & Status Transition Form -->
                <div class="card mb-3">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0 fw-bold"><i class="bx bx-check-shield me-1 text-primary"></i> Operations Review & Decision</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.claims.status.update', $claim->id) }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Update Claim Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="under_review" {{ $claim->claim_status === 'under_review' ? 'selected' : '' }}>Under Investigation</option>
                                        <option value="resolved_settled" {{ $claim->claim_status === 'resolved_settled' ? 'selected' : '' }}>Resolved / Settled</option>
                                        <option value="rejected" {{ $claim->claim_status === 'rejected' ? 'selected' : '' }}>Reject Claim (Unverified)</option>
                                        <option value="cancelled" {{ $claim->claim_status === 'cancelled' ? 'selected' : '' }}>Cancel / Buyer Withdrawn</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Public Resolution Summary <span class="text-muted small">(Visible to Buyer & Seller)</span></label>
                                    <textarea name="resolution_summary" class="form-control" rows="2" placeholder="Record agreed resolution or reasons for closure...">{{ old('resolution_summary', $claim->resolution_summary) }}</textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold text-danger">Internal Operations Notes <span class="text-muted small">(Confidential: Never shown to Buyer or Seller)</span></label>
                                    <textarea name="admin_notes" class="form-control" rows="3" placeholder="Confidential audit findings, carrier investigation notes...">{{ old('admin_notes', $claim->admin_notes) }}</textarea>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bx bx-save me-1"></i> Save Operations Determination
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column: Evidence & Timeline -->
            <div class="col-lg-4">
                <!-- Evidence Attachments Card -->
                <div class="card mb-3">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0 fw-bold"><i class="bx bx-file me-1 text-primary"></i> Supporting Evidence</h6>
                    </div>
                    <div class="card-body p-3">
                        @if($claim->evidence->isEmpty())
                            <p class="text-muted small mb-0">No evidence documents attached.</p>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($claim->evidence as $ev)
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <div class="small fw-bold text-dark text-truncate" style="max-width: 170px;">{{ $ev->file_name }}</div>
                                            <div class="text-muted font-11">By: <span class="badge bg-light text-dark">{{ ucfirst($ev->uploader_role) }}</span> &bull; {{ number_format($ev->file_size / 1024, 1) }} KB</div>
                                        </div>
                                        <a href="{{ asset($ev->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-download"></i> View
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Audit History -->
                <div class="card">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0 fw-bold"><i class="bx bx-history me-1 text-primary"></i> Claim Audit Trail</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="timeline-feed">
                            @foreach($claim->history as $event)
                                <div class="border-start border-2 border-primary ps-3 pb-3 position-relative">
                                    <div class="badge bg-light-primary text-primary small">{{ $event->action }}</div>
                                    <div class="small fw-bold text-dark mt-1">{{ $event->actor_name ?: 'System' }} ({{ $event->actor_role }})</div>
                                    @if($event->notes)
                                        <div class="small text-secondary">{{ $event->notes }}</div>
                                    @endif
                                    <div class="text-muted font-11 mt-1">{{ $event->created_at->format('d M Y, h:i A') }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

