@extends('layout')
@section('title', 'Fulfilment Detail - ' . $fulfilment->fulfilment_ref)
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Logistics</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.logistics.index') }}"><i class="bx bx-home-alt"></i> Fulfilments</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $fulfilment->fulfilment_ref }}</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="{{ route('admin.logistics.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-arrow-back"></i> Back to Fulfilments
                </a>
            </div>
        </div>

        <!-- Commercial Terms Banner -->
        <div class="alert alert-info border-0 bg-light-info py-2 mb-3">
            <div class="d-flex align-items-center">
                <div class="font-24 text-info me-2"><i class="bx bx-info-circle"></i></div>
                <div class="text-dark small">
                    <strong>Commercial Baseline:</strong> {{ $fulfilment->freight_terms }}. Freight is settled directly between buyer and transporter.
                    LR / Bilty upload is dispatch evidence and does not constitute delivery proof.
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column: Fulfilment & Dispatch Details -->
            <div class="col-lg-8">
                <!-- Overview Card -->
                <div class="card mb-3">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="bx bx-package me-1 text-primary"></i> Consignment Summary</h6>
                        <span class="badge bg-primary">{{ $fulfilment->fulfilment_status }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <span class="text-muted small">Fulfilment Ref:</span>
                                <div class="fw-bold">{{ $fulfilment->fulfilment_ref }}</div>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted small">Order Number:</span>
                                <div class="fw-bold">#{{ $fulfilment->order?->orderno }}</div>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted small">Parent Order:</span>
                                <div class="fw-bold">#{{ $fulfilment->parent_order_id }}</div>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted small">Seller:</span>
                                <div class="fw-bold">{{ $fulfilment->company?->name ?? $fulfilment->seller?->name }}</div>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted small">Buyer / Customer:</span>
                                <div class="fw-bold">{{ $fulfilment->customer?->name }}</div>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted small">Freight Terms:</span>
                                <div><span class="badge bg-light-primary text-primary">{{ $fulfilment->freight_terms }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Locations Card -->
                <div class="card mb-3">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0 fw-bold"><i class="bx bx-map-pin me-1 text-primary"></i> Locations & Logistics Flow</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 border-end">
                                <h6 class="fw-bold text-secondary mb-2"><i class="bx bx-buildings"></i> Dispatch Origin (Factory / Godown)</h6>
                                <div class="fw-bold">{{ $fulfilment->dispatch_origin_name ?: 'Seller Factory' }}</div>
                                <div class="small text-secondary">{{ $fulfilment->dispatch_origin_address }}</div>
                                @if($fulfilment->dispatch_origin_city)
                                    <div class="small text-muted">{{ $fulfilment->dispatch_origin_city }}, {{ $fulfilment->dispatch_origin_state }} - {{ $fulfilment->dispatch_origin_pincode }}</div>
                                @endif
                            </div>
                            <div class="col-md-6 ps-md-4">
                                <h6 class="fw-bold text-secondary mb-2"><i class="bx bx-map"></i> Delivery / Hand-over Point (Transport Nagar)</h6>
                                <div class="fw-bold">{{ $fulfilment->handover_point_name ?: 'Transport Nagar' }}</div>
                                <div class="small text-secondary">{{ $fulfilment->handover_point_address }}</div>
                                @if($fulfilment->handover_point_city)
                                    <div class="small text-muted">{{ $fulfilment->handover_point_city }}, {{ $fulfilment->handover_point_state }} - {{ $fulfilment->handover_point_pincode }}</div>
                                @endif
                                @if($fulfilment->handover_instructions)
                                    <div class="small text-primary mt-2"><strong>Instructions:</strong> {{ $fulfilment->handover_instructions }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transporter & LR Proof Card -->
                <div class="card mb-3">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0 fw-bold"><i class="bx bx-car me-1 text-primary"></i> Transporter & LR / Bilty Evidence</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <span class="text-muted small">Transporter Name:</span>
                                <div class="fw-bold">{{ $fulfilment->transporter_name ?: 'Pending Assignment' }}</div>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted small">Contact Phone:</span>
                                <div class="fw-bold">{{ $fulfilment->transporter_contact ?: 'N/A' }}</div>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted small">Online Tracking No:</span>
                                <div class="fw-bold">{{ $fulfilment->transporter_tracking_no ?: 'N/A' }}</div>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted small">LR / Bilty Number:</span>
                                <div class="fw-bold text-primary">{{ $fulfilment->lr_number ?: 'N/A' }}</div>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted small">Packages / Cartons:</span>
                                <div class="fw-bold">{{ $fulfilment->package_count }} pkgs ({{ $fulfilment->weight_volume ?: 'Weight not specified' }})</div>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted small">Vehicle Number:</span>
                                <div class="fw-bold">{{ $fulfilment->vehicle_number ?: 'N/A' }}</div>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted small">Dispatched At:</span>
                                <div class="fw-bold">{{ $fulfilment->dispatched_at ? $fulfilment->dispatched_at->format('d M Y, h:i A') : 'Not Yet Dispatched' }}</div>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted small">Estimated Delivery:</span>
                                <div class="fw-bold">{{ $fulfilment->estimated_delivery_date ? $fulfilment->estimated_delivery_date->format('d M Y') : 'N/A' }}</div>
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted small">Buyer Acknowledged At:</span>
                                <div class="fw-bold">{{ $fulfilment->buyer_acknowledged_at ? $fulfilment->buyer_acknowledged_at->format('d M Y, h:i A') : 'Pending Buyer Receipt' }}</div>
                            </div>
                        </div>

                        @if($fulfilment->lr_document_path)
                            <hr>
                            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                                <div class="d-flex align-items-center">
                                    <div class="font-30 text-primary me-3"><i class="bx bxs-file-pdf"></i></div>
                                    <div>
                                        <div class="fw-bold">LR / Bilty Document</div>
                                        <div class="text-muted small">{{ $fulfilment->lr_mime_type }} &bull; {{ number_format(($fulfilment->lr_file_size ?? 0) / 1024, 1) }} KB</div>
                                    </div>
                                </div>
                                <a href="{{ asset($fulfilment->lr_document_path) }}" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="bx bx-download"></i> View / Download Document
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0 fw-bold"><i class="bx bx-list-ul me-1 text-primary"></i> Order Items in Consignment</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>HSN</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fulfilment->order?->orderdetails ?? [] as $detail)
                                        <tr>
                                            <td class="fw-bold">{{ $detail->productname }}</td>
                                            <td>{{ $detail->hsn ?: 'N/A' }}</td>
                                            <td>{{ $detail->totalqty ?? $detail->qty ?? 1 }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Dispute Status & Audit Trail -->
            <div class="col-lg-4">
                @if($fulfilment->dispute_status)
                    <div class="card border-danger mb-3">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0 fw-bold"><i class="bx bx-error-alt me-1"></i> Reported Issue</h6>
                        </div>
                        <div class="card-body">
                            <div class="badge bg-danger mb-2">{{ str_replace('_', ' ', ucfirst($fulfilment->dispute_status)) }}</div>
                            <p class="small text-dark mb-1"><strong>Reason / Details:</strong></p>
                            <p class="small text-secondary">{{ $fulfilment->dispute_reason }}</p>
                            <div class="text-muted font-11">Reported: {{ $fulfilment->dispute_reported_at ? $fulfilment->dispute_reported_at->format('d M Y, h:i A') : 'N/A' }}</div>
                        </div>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0 fw-bold"><i class="bx bx-history me-1 text-primary"></i> Fulfilment Audit Trail</h6>
                    </div>
                    <div class="card-body p-3">
                        @if($fulfilment->history->isEmpty())
                            <p class="text-muted small mb-0">No history records logged yet.</p>
                        @else
                            <div class="timeline-feed">
                                @foreach($fulfilment->history as $event)
                                    <div class="border-start border-2 border-primary ps-3 pb-3 position-relative">
                                        <div class="badge bg-light-primary text-primary small">{{ $event->action }}</div>
                                        <div class="small fw-bold text-dark mt-1">{{ $event->actor_name ?: 'System' }} ({{ $event->actor_role ?: 'system' }})</div>
                                        @if($event->notes)
                                            <div class="small text-secondary">{{ $event->notes }}</div>
                                        @endif
                                        <div class="text-muted font-11 mt-1">{{ $event->created_at->format('d M Y, h:i A') }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
