@extends('layout')
@section('title', 'Bulk Operation Preview #' . $operation->id)
@section('content')
@php
    $previewData = $operation->preview_data ?? [];
    $previewSample = $previewData['preview_sample'] ?? [];
    $ineligibleItems = $previewData['ineligible_items'] ?? [];
    $canConfirm = $operation->canConfirm();
@endphp
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
                        <li class="breadcrumb-item"><a href="{{ route('seller.catalogue.bulk') }}">Bulk Catalogue Manager</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Preview #{{ $operation->id }}</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('seller.catalogue.bulk') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Manager
                </a>
                <a href="{{ route('seller.catalogue.bulk.history') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-clock-rotate-left me-1"></i> History
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

        <!-- Summary Cards -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mb-4">
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-secondary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Matching Items</p>
                                <h4 class="my-1 text-secondary">{{ $operation->total_matching }}</h4>
                                <p class="mb-0 font-13 text-muted">Scope: {{ strtoupper($operation->selection_mode) }}</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-secondary text-secondary ms-auto">
                                <i class="fa-solid fa-list-check"></i>
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
                                <p class="mb-0 text-success">Eligible Items</p>
                                <h4 class="my-1 text-success">{{ $operation->eligible_count }}</h4>
                                <p class="mb-0 font-13 text-muted">Ready to be committed</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto">
                                <i class="fa-solid fa-circle-check"></i>
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
                                <p class="mb-0 text-warning">Ineligible / Blocked</p>
                                <h4 class="my-1 text-warning">{{ $operation->ineligible_count }}</h4>
                                <p class="mb-0 font-13 text-muted">Validation/policy restricted</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-warning text-warning ms-auto">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-primary">Operation</p>
                                <h5 class="my-1 text-primary">{{ strtoupper(str_replace('_', ' ', $operation->operation_type)) }}</h5>
                                <p class="mb-0 font-13 text-muted">Target: {{ ucfirst($operation->target_entity) }}</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-primary text-primary ms-auto">
                                <i class="fa-solid fa-gear"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ineligible Items Warning Banner if any -->
        @if(!empty($ineligibleItems))
            <div class="card border-warning mb-4">
                <div class="card-header bg-warning bg-opacity-10 py-3">
                    <h6 class="mb-0 text-dark">
                        <i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>
                        Ineligible Items Diagnostics ({{ count($ineligibleItems) }} items cannot be processed)
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 250px;">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item ID</th>
                                    <th>SKU</th>
                                    <th>Reason / Blocking Policy</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ineligibleItems as $ineligible)
                                    <tr>
                                        <td>#{{ $ineligible['id'] ?? 'N/A' }}</td>
                                        <td><code>{{ $ineligible['sku'] ?? 'N/A' }}</code></td>
                                        <td class="text-danger small">{{ $ineligible['reason'] ?? 'Condition not met' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Delta / Change Preview Table -->
        <div class="card mb-4">
            <div class="card-header bg-transparent py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0">
                            <i class="fa-solid fa-code-compare me-2 text-primary"></i>
                            Preview Sample (Old vs New Values)
                        </h6>
                        <small class="text-muted">Showing sample of up to 50 items affected by this operation</small>
                    </div>
                    <span class="badge bg-light text-dark border">
                        {{ count($previewSample) }} records previewed
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item / SKU</th>
                                <th>Product Name</th>
                                <th>Current State</th>
                                <th>Proposed State</th>
                                <th class="text-center">Eligibility</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($previewSample as $row)
                                <tr>
                                    <td>
                                        <strong>#{{ $row['id'] }}</strong>
                                        <div class="font-monospace small text-primary">{{ $row['sku'] ?? 'N/A' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $row['title'] ?? 'N/A' }}</div>
                                    </td>
                                    <td>
                                        @if(isset($row['current']) && is_array($row['current']))
                                            @foreach($row['current'] as $fKey => $fVal)
                                                <div class="small">
                                                    <span class="text-muted">{{ ucfirst(str_replace('_', ' ', $fKey)) }}:</span>
                                                    <span class="fw-bold">{{ is_bool($fVal) ? ($fVal ? 'true' : 'false') : $fVal }}</span>
                                                </div>
                                            @endforeach
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(isset($row['proposed']) && is_array($row['proposed']))
                                            @foreach($row['proposed'] as $fKey => $fVal)
                                                <div class="small">
                                                    <span class="text-muted">{{ ucfirst(str_replace('_', ' ', $fKey)) }}:</span>
                                                    <span class="fw-bold text-success">{{ is_bool($fVal) ? ($fVal ? 'true' : 'false') : $fVal }}</span>
                                                </div>
                                            @endforeach
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(!empty($row['eligible']))
                                            <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Eligible</span>
                                        @else
                                            <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i>Ineligible</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        No preview sample records available.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Stage 2 Confirmation Card -->
        <div class="card border-top border-4 border-success">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h5 class="mb-1 text-success">
                            <i class="fa-solid fa-circle-check me-2"></i>Stage 2: Confirm & Execute Operation
                        </h5>
                        <p class="text-muted small mb-0">
                            @if($canConfirm)
                                Review the above diagnostic preview carefully. Clicking confirm will atomically execute updates for all <strong>{{ $operation->eligible_count }}</strong> eligible records.
                            @else
                                This bulk operation cannot be executed because there are zero eligible records.
                            @endif
                        </p>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('seller.catalogue.bulk') }}" class="btn btn-outline-secondary px-4">
                            Cancel
                        </a>

                        @if($canConfirm)
                            <form method="POST" action="{{ route('seller.catalogue.bulk.confirm', $operation->id) }}" onsubmit="return confirm('Are you sure you want to permanently execute this bulk operation for {{ $operation->eligible_count }} records?');">
                                @csrf
                                <button type="submit" class="btn btn-success px-4 font-16">
                                    <i class="fa-solid fa-bolt me-2"></i> Confirm & Execute ({{ $operation->eligible_count }} Items)
                                </button>
                            </form>
                        @else
                            <button type="button" class="btn btn-secondary px-4 font-16" disabled>
                                Cannot Execute (0 Eligible)
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
