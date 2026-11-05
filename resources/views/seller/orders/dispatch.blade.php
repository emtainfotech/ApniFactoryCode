@extends('layout')
@section('title', 'Seller Order Dispatch & Logistics - #' . $order->orderno)
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Logistics</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('order.list') }}"><i class="bx bx-home-alt"></i> Orders</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('order.detail', $order->id) }}">#{{ $order->orderno }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Dispatch & To-Pay Logistics</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="{{ route('order.detail', $order->id) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-arrow-back"></i> Back to Order
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

        <!-- Commercial Terms & Platform Policy Callout -->
        <div class="card border-primary mb-3">
            <div class="card-body bg-light-primary rounded">
                <div class="d-flex align-items-center">
                    <div class="font-30 text-primary me-3"><i class="bx bx-info-circle"></i></div>
                    <div>
                        <h6 class="mb-1 text-primary fw-bold">Commercial Terms: To-Pay (Buyer-Paid Freight)</h6>
                        <p class="mb-0 text-secondary font-13">
                            The manufacturer/seller delivers goods to the agreed factory gate or Transport Nagar handover point.
                            Freight charges are payable directly by the buyer to the designated transporter under <strong>To-Pay</strong> terms.
                            <br>
                            <strong>Important Rule:</strong> Uploading an LR / Bilty serves as proof of dispatch/handover only.
                            It does <em>not</em> mark the order delivered and does not imply buyer acceptance.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order & Fulfilment Header Card -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <span class="text-muted small">Fulfilment Ref</span>
                        <h6 class="fw-bold mb-0 text-primary">{{ $fulfilment->fulfilment_ref }}</h6>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted small">Seller Order # / Parent #</span>
                        <h6 class="fw-bold mb-0">#{{ $order->orderno }} @if($order->parent_order_id)<span class="badge bg-light-secondary text-dark">Parent #{{ $order->parent_order_id }}</span>@endif</h6>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted small">Freight Terms</span>
                        <div><span class="badge bg-primary">{{ $fulfilment->freight_terms }}</span></div>
                    </div>
                    <div class="col-md-3 text-md-end">
                        <span class="text-muted small">Current Status</span>
                        <div>
                            @switch($fulfilment->fulfilment_status)
                                @case('pending_acceptance')
                                    <span class="badge bg-warning text-dark font-14">Pending Acceptance</span>
                                    @break
                                @case('accepted')
                                    <span class="badge bg-info text-white font-14">Accepted</span>
                                    @break
                                @case('processing_packed')
                                    <span class="badge bg-secondary text-white font-14">Packed & Ready</span>
                                    @break
                                @case('dispatched')
                                    <span class="badge bg-primary text-white font-14">Dispatched / In-Transit</span>
                                    @break
                                @case('buyer_acknowledged')
                                    <span class="badge bg-success text-white font-14">Buyer Acknowledged</span>
                                    @break
                                @default
                                    <span class="badge bg-dark text-white font-14">{{ ucfirst($fulfilment->fulfilment_status) }}</span>
                            @endswitch
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column: Dispatch Execution Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0 fw-bold"><i class="bx bx-send me-2 text-primary"></i> Dispatch Execution & Transporter Details</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('seller.orders.dispatch.submit', $order->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <!-- Section 1: Dispatch Origin -->
                            <h6 class="fw-bold text-secondary mb-3"><i class="bx bx-buildings me-1"></i> 1. Dispatch Origin (Factory / Godown)</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Origin Facility / Factory Name <span class="text-danger">*</span></label>
                                    <input type="text" name="dispatch_origin_name" class="form-control" required
                                           value="{{ old('dispatch_origin_name', $fulfilment->dispatch_origin_name ?? $fulfilment->company?->name) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">City / State</label>
                                    <div class="input-group">
                                        <input type="text" name="dispatch_origin_city" class="form-control" placeholder="City"
                                               value="{{ old('dispatch_origin_city', $fulfilment->dispatch_origin_city ?? $fulfilment->company?->city) }}">
                                        <input type="text" name="dispatch_origin_state" class="form-control" placeholder="State"
                                               value="{{ old('dispatch_origin_state', $fulfilment->dispatch_origin_state ?? $fulfilment->company?->state) }}">
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label">Address <span class="text-danger">*</span></label>
                                    <textarea name="dispatch_origin_address" class="form-control" rows="2" required>{{ old('dispatch_origin_address', $fulfilment->dispatch_origin_address ?? $fulfilment->company?->address) }}</textarea>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Pincode</label>
                                    <input type="text" name="dispatch_origin_pincode" class="form-control"
                                           value="{{ old('dispatch_origin_pincode', $fulfilment->dispatch_origin_pincode ?? $fulfilment->company?->pincode) }}">
                                </div>
                            </div>

                            <hr>

                            <!-- Section 2: Hand-over Point (Transport Nagar) Snapshot -->
                            <h6 class="fw-bold text-secondary mb-3"><i class="bx bx-map-pin me-1"></i> 2. Delivery / Hand-over Point (Transport Nagar)</h6>
                            <div class="bg-light p-3 rounded mb-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <span class="text-muted small">Designated Hand-over Destination:</span>
                                        <div class="fw-bold">{{ $fulfilment->handover_point_name ?: 'Transport Nagar Hub' }}</div>
                                        <div class="small text-secondary">{{ $fulfilment->handover_point_address }}</div>
                                        @if($fulfilment->handover_point_city || $fulfilment->handover_point_state)
                                            <div class="small text-muted">{{ $fulfilment->handover_point_city }}, {{ $fulfilment->handover_point_state }} - {{ $fulfilment->handover_point_pincode }}</div>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <span class="text-muted small">Buyer / Delivery Instructions:</span>
                                        <div class="small text-dark mt-1">{{ $fulfilment->handover_instructions ?: 'Standard Transport Nagar gate delivery.' }}</div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Section 3: Transporter & LR Details -->
                            <h6 class="fw-bold text-secondary mb-3"><i class="bx bx-car me-1"></i> 3. Transporter & LR / Bilty</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Transporter / Carrier Name <span class="text-danger">*</span></label>
                                    <input type="text" name="transporter_name" class="form-control" required
                                           placeholder="e.g. VRL Logistics, TCI Express, SafeX"
                                           value="{{ old('transporter_name', $fulfilment->transporter_name) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Transporter Contact / Phone</label>
                                    <input type="text" name="transporter_contact" class="form-control"
                                           placeholder="e.g. 9876543210"
                                           value="{{ old('transporter_contact', $fulfilment->transporter_contact) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">LR / Bilty Number <span class="text-muted small">(Lorry Receipt)</span></label>
                                    <input type="text" name="lr_number" class="form-control"
                                           placeholder="e.g. LR-2026-98765"
                                           value="{{ old('lr_number', $fulfilment->lr_number) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Transporter Online Tracking No / Docket</label>
                                    <input type="text" name="transporter_tracking_no" class="form-control"
                                           placeholder="Optional tracking reference"
                                           value="{{ old('transporter_tracking_no', $fulfilment->transporter_tracking_no) }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">LR / Bilty Scan or Photo Document <span class="text-muted small">(JPEG, PNG, WEBP, PDF up to 5MB)</span></label>
                                    <input type="file" name="lr_document" class="form-control">
                                    @if($fulfilment->lr_document_path)
                                        <div class="mt-2 small">
                                            <span class="text-success"><i class="bx bx-check-circle"></i> File uploaded:</span>
                                            <a href="{{ asset($fulfilment->lr_document_path) }}" target="_blank" class="fw-bold text-primary">
                                                <i class="bx bx-file"></i> View Existing LR Document
                                            </a>
                                            ({{ number_format(($fulfilment->lr_file_size ?? 0) / 1024, 1) }} KB)
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <hr>

                            <!-- Section 4: Consignment & Vehicle Details -->
                            <h6 class="fw-bold text-secondary mb-3"><i class="bx bx-package me-1"></i> 4. Consignment & Vehicle Details</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Total Packages / Cartons <span class="text-danger">*</span></label>
                                    <input type="number" name="package_count" min="1" class="form-control" required
                                           value="{{ old('package_count', $fulfilment->package_count ?? 1) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Weight / Volume</label>
                                    <input type="text" name="weight_volume" class="form-control"
                                           placeholder="e.g. 120 kg / 0.8 CBM"
                                           value="{{ old('weight_volume', $fulfilment->weight_volume) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Vehicle / Truck Number</label>
                                    <input type="text" name="vehicle_number" class="form-control"
                                           placeholder="e.g. RJ14 GB 1234"
                                           value="{{ old('vehicle_number', $fulfilment->vehicle_number) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Handover / Dispatch Date</label>
                                    <input type="date" name="dispatched_at" class="form-control"
                                           value="{{ old('dispatched_at', $fulfilment->dispatched_at ? $fulfilment->dispatched_at->format('Y-m-d') : date('Y-m-d')) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Estimated Delivery Date</label>
                                    <input type="date" name="estimated_delivery_date" class="form-control"
                                           value="{{ old('estimated_delivery_date', $fulfilment->estimated_delivery_date ? $fulfilment->estimated_delivery_date->format('Y-m-d') : '') }}">
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between pt-2">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bx bx-check-shield me-1"></i> Save & Confirm Dispatch
                                </button>
                                <span class="text-muted small">
                                    <i class="bx bx-bell"></i> Saving dispatch will send an in-app notification to the buyer.
                                </span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column: Consignment Items & History Audit Log -->
            <div class="col-lg-4">
                <!-- Order Items Card -->
                <div class="card mb-3">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0 fw-bold"><i class="bx bx-list-check me-1"></i> Consignment Items</h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($order->orderdetails as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold">{{ $item->productname }}</div>
                                        <div class="text-muted small">HSN: {{ $item->hsn ?: 'N/A' }}</div>
                                    </div>
                                    <span class="badge bg-light-primary text-primary font-14">Qty: {{ $item->totalqty ?? $item->qty ?? 1 }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Standalone LR Document Uploader Card (if order is already dispatched) -->
                @if($fulfilment->fulfilment_status === 'dispatched' || $fulfilment->fulfilment_status === 'buyer_acknowledged')
                    <div class="card mb-3">
                        <div class="card-header bg-transparent">
                            <h6 class="mb-0 fw-bold"><i class="bx bx-upload me-1"></i> Update LR / Bilty Document</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('seller.orders.dispatch.upload-lr', $order->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label small">LR Number</label>
                                    <input type="text" name="lr_number" class="form-control form-control-sm" value="{{ $fulfilment->lr_number }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">LR File Scan</label>
                                    <input type="file" name="lr_document" class="form-control form-control-sm" required>
                                </div>
                                <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                                    <i class="bx bx-cloud-upload"></i> Re-Upload LR
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Audit Log History -->
                <div class="card">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0 fw-bold"><i class="bx bx-history me-1"></i> Fulfilment Audit Trail</h6>
                    </div>
                    <div class="card-body p-3">
                        @if($history->isEmpty())
                            <p class="text-muted small mb-0">No history events recorded yet.</p>
                        @else
                            <div class="timeline-feed">
                                @foreach($history as $event)
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
