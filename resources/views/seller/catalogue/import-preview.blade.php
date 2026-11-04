@extends('layout')
@section('title', 'Import Validation Preview #' . $import->id)
@section('content')
@php
    $totalCount   = $summary['total_rows'] ?? $summary['total'] ?? $import->total_rows ?? 0;
    $validCount   = $summary['valid_rows'] ?? $summary['valid'] ?? $import->valid_rows ?? 0;
    $invalidCount = $summary['invalid_rows'] ?? $summary['invalid'] ?? $import->invalid_rows ?? 0;
    $createCount  = $summary['create_count'] ?? $summary['creates'] ?? 0;
    $updateCount  = $summary['update_count'] ?? $summary['updates'] ?? 0;
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
                        <li class="breadcrumb-item"><a href="{{ route('seller.catalogue.import') }}">Catalogue Import</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Preview #{{ $import->id }}</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('seller.catalogue.import') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> New Import
                </a>
                @if($import->error_report_path)
                    <a href="{{ route('seller.catalogue.import.errors', $import->id) }}" class="btn btn-outline-danger">
                        <i class="fa-solid fa-file-excel me-1"></i> Download Error Report (CSV)
                    </a>
                @endif
                <a href="{{ route('seller.catalogue.import.history') }}" class="btn btn-outline-secondary">
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

        <!-- Summary Metrics -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-5 g-3 mb-4">
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-secondary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Processed Rows</p>
                                <h4 class="my-1 text-secondary">{{ $totalCount }}</h4>
                                <p class="mb-0 font-13 text-muted">Spreadsheet rows parsed</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-secondary text-secondary ms-auto">
                                <i class="fa-solid fa-file-lines"></i>
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
                                <p class="mb-0 text-success">Valid Rows</p>
                                <h4 class="my-1 text-success">{{ $validCount }}</h4>
                                <p class="mb-0 font-13 text-muted">Ready to be committed</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto">
                                <i class="fa-solid fa-check"></i>
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
                                <p class="mb-0 text-danger">Invalid Rows</p>
                                <h4 class="my-1 text-danger">{{ $invalidCount }}</h4>
                                <p class="mb-0 font-13 text-muted">Failed validation checks</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto">
                                <i class="fa-solid fa-xmark"></i>
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
                                <p class="mb-0 text-info">New Listings</p>
                                <h4 class="my-1 text-info">{{ $createCount }}</h4>
                                <p class="mb-0 font-13 text-muted">New SKU / Products</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-info text-info ms-auto">
                                <i class="fa-solid fa-plus"></i>
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
                                <p class="mb-0 text-warning">Updates</p>
                                <h4 class="my-1 text-warning">{{ $updateCount }}</h4>
                                <p class="mb-0 font-13 text-muted">Existing SKUs modified</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-warning text-warning ms-auto">
                                <i class="fa-solid fa-arrows-rotate"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stage 2 Execution Confirmation Banner -->
        @if($import->isValidated())
            <div class="card bg-light border">
                <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h5 class="mb-1 text-primary"><i class="fa-solid fa-shield-check me-2"></i>Stage 2: Confirmation & Execution</h5>
                        <p class="mb-0 text-muted small">
                            @if($validCount > 0)
                                You have <strong>{{ $validCount }} valid rows</strong> ready to be created or updated in your catalogue.
                                @if($invalidCount > 0)
                                    <span class="text-danger font-weight-bold">Warning: {{ $invalidCount }} invalid rows will be skipped.</span>
                                @endif
                            @else
                                <span class="text-danger font-weight-bold">All rows contain validation errors. Please download the error report below, rectify your file, and re-upload.</span>
                            @endif
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        @if($validCount > 0)
                            <form method="POST" action="{{ route('seller.catalogue.import.confirm', $import->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-success btn-lg px-4" onclick="return confirm('Are you sure you want to commit these changes to your live catalogue?');">
                                    <i class="fa-solid fa-circle-check me-2"></i> Confirm & Execute Import ({{ $validCount }} rows)
                                </button>
                            </form>
                        @endif
                        @if($import->error_report_path)
                            <a href="{{ route('seller.catalogue.import.errors', $import->id) }}" class="btn btn-outline-danger btn-lg">
                                <i class="fa-solid fa-download me-2"></i> Download Error Report
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-secondary d-flex align-items-center mb-4" role="alert">
                <i class="fa-solid fa-info-circle me-2 font-20"></i>
                <div>
                    Import Status: <strong class="text-uppercase">{{ $import->status }}</strong>.
                    Created Listings: <strong>{{ $import->created_listings }}</strong>,
                    Updated Listings: <strong>{{ $import->updated_listings }}</strong>.
                    @if($import->error_report_path)
                        <a href="{{ route('seller.catalogue.import.errors', $import->id) }}" class="alert-link ms-2">Download Error Report CSV</a>
                    @endif
                </div>
            </div>
        @endif

        <!-- Row-by-Row Details Table -->
        <div class="card mt-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-uppercase font-14"><i class="fa-solid fa-table-list me-2"></i>Validated Rows Preview</h6>
                <span class="badge bg-secondary font-12">{{ count($rows) }} rows</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0 font-13">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th style="width: 80px;">Action</th>
                                <th style="width: 90px;">Status</th>
                                <th>Seller SKU</th>
                                <th>Product Details</th>
                                <th>Hierarchy</th>
                                <th>Pricing & Stock</th>
                                <th>Dynamic Attributes</th>
                                <th>Validation Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $r)
                                @php
                                    $data = $r['data'] ?? $r;
                                    $rowNum = $r['row_number'] ?? $loop->iteration;
                                    $operation = strtoupper($r['operation'] ?? ($r['action'] ?? 'CREATE'));
                                    $isValid = !empty($r['is_valid']) || (($r['status'] ?? '') === 'valid');
                                    $sku = $r['seller_sku'] ?? ($data['seller_sku'] ?? '');
                                    $prodName = $data['product_name'] ?? ($r['product_name'] ?? '');
                                    $brandName = $data['brand_name'] ?? ($r['brand'] ?? '');
                                    $catName = $data['category_name'] ?? ($r['category'] ?? '');
                                    $subcatName = $data['subcategory_name'] ?? ($r['subcategory'] ?? '');
                                    $mrp = $data['customer_mrp'] ?? ($r['mrp'] ?? 0);
                                    $price = $data['selling_price'] ?? ($data['seller_price'] ?? ($r['selling_price'] ?? 0));
                                    $stock = $data['stock_quantity'] ?? ($r['stock_quantity'] ?? 0);
                                    $dynAttrs = $data['dynamic_attributes'] ?? ($r['variant_attributes'] ?? '');
                                    $rowErrors = $r['errors'] ?? [];
                                @endphp
                                <tr>
                                    <td class="text-muted font-weight-bold">{{ $rowNum }}</td>
                                    <td>
                                        @if($operation === 'CREATE')
                                            <span class="badge bg-light-info text-info border border-info font-11">CREATE</span>
                                        @else
                                            <span class="badge bg-light-warning text-warning border border-warning font-11">UPDATE</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isValid)
                                            <span class="badge bg-success font-11"><i class="fa-solid fa-check me-1"></i>VALID</span>
                                        @else
                                            <span class="badge bg-danger font-11"><i class="fa-solid fa-xmark me-1"></i>INVALID</span>
                                        @endif
                                    </td>
                                    <td>
                                        <code class="text-dark font-weight-bold">{{ $sku ?: '—' }}</code>
                                    </td>
                                    <td>
                                        <strong>{{ $prodName ?: '—' }}</strong>
                                        @if(!empty($brandName))
                                            <div class="text-muted small">Brand: {{ $brandName }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div><span class="text-muted">Cat:</span> {{ $catName ?: '—' }}</div>
                                        <div><span class="text-muted">Sub:</span> {{ $subcatName ?: '—' }}</div>
                                    </td>
                                    <td>
                                        <div><strong>MRP:</strong> ₹{{ number_format((float)$mrp, 2) }}</div>
                                        <div><strong>Price:</strong> ₹{{ number_format((float)$price, 2) }}</div>
                                        <div class="text-muted small">Stock: {{ $stock }}</div>
                                    </td>
                                    <td>
                                        <div class="small text-truncate" style="max-width: 200px;" title="{{ $dynAttrs }}">
                                            {{ $dynAttrs ?: '—' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($isValid)
                                            <span class="text-success"><i class="fa-solid fa-check-circle me-1"></i>Ready to import</span>
                                        @else
                                            <ul class="list-unstyled mb-0 text-danger small">
                                                @foreach($rowErrors as $err)
                                                    @php
                                                        $errMsg = is_array($err) ? ($err['error'] ?? 'Validation error') : (string)$err;
                                                    @endphp
                                                    <li><i class="fa-solid fa-circle-exclamation me-1"></i>{{ $errMsg }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        No rows were found in the uploaded spreadsheet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
