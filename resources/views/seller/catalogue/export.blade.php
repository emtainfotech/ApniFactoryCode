@extends('layout')
@section('title', 'Export Product Catalogue')
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
                        <li class="breadcrumb-item active" aria-current="page">Catalogue Export (Excel/CSV)</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="{{ route('seller.catalogue.export.history') }}" class="btn btn-outline-secondary px-3">
                    <i class="fa-solid fa-clock-rotate-left me-1"></i> View Full Export History
                </a>
            </div>
        </div>
        <!--end breadcrumb-->

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Export Form Card -->
            <div class="col-xl-8">
                <div class="card border-top border-0 border-4 border-success">
                    <div class="card-body p-4">
                        <div class="card-title d-flex align-items-center mb-3">
                            <div><i class="fa-solid fa-file-excel me-2 font-22 text-success"></i></div>
                            <h5 class="mb-0 text-success">Catalogue Excel & CSV Export Facility</h5>
                        </div>
                        <p class="text-muted">
                            Export your complete product catalogue, variants, seller SKUs, packaging specifications, and dynamic attributes into an enterprise-ready spreadsheet.
                        </p>
                        <hr>

                        <form method="POST" action="{{ route('seller.catalogue.export.process') }}" class="row g-3">
                            @csrf

                            <!-- Export Format & Delivery Mode -->
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold">Export Format <span class="text-danger">*</span></label>
                                <select name="format" class="form-select" required>
                                    <option value="xlsx" selected>Excel Spreadsheet (.xlsx) &mdash; Recommended</option>
                                    <option value="csv">Standard CSV (.csv, UTF-8)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label font-weight-bold">Processing Mode</label>
                                <select name="delivery_mode" class="form-select">
                                    <option value="direct" selected>Direct Download (Instant)</option>
                                    <option value="async">Background Job (For massive catalogues)</option>
                                </select>
                            </div>

                            <div class="col-12"><hr class="my-2"></div>
                            <h6 class="mb-0 text-uppercase font-14 text-muted"><i class="fa-solid fa-filter me-1"></i> Optional Catalogue Filters</h6>

                            <!-- Filters -->
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select">
                                    <option value="">-- All Categories --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Brand</label>
                                <select name="brand_id" class="form-select">
                                    <option value="">-- All Brands --</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Product Name / Title</label>
                                <input type="text" name="product_name" class="form-control" placeholder="Search by product name">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Seller SKU</label>
                                <input type="text" name="seller_sku" class="form-control" placeholder="Filter by SKU code">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Listing Status</label>
                                <select name="status" class="form-select">
                                    <option value="">-- Any Status --</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Stock Status</label>
                                <select name="stock_status" class="form-select">
                                    <option value="">-- Any Stock Level --</option>
                                    <option value="in_stock">In Stock (> 0)</option>
                                    <option value="low_stock">Low Stock (≤ Threshold)</option>
                                    <option value="out_of_stock">Out of Stock (0)</option>
                                </select>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-success px-4 font-16">
                                    <i class="fa-solid fa-download me-2"></i> Generate & Download Catalogue
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Permission & Column Info Card -->
            <div class="col-xl-4">
                <div class="card mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 text-uppercase"><i class="fa-solid fa-shield-halved me-2 text-primary"></i>Data Privacy & Permissions</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">
                            In accordance with Apni Factory Phase 2.1 access controls, columns in the exported spreadsheet are tailored to your account's verified permissions:
                        </p>
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="fa-solid fa-box text-success me-2"></i>Hierarchy & SKUs:</span>
                                <span class="badge bg-success">Included</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="fa-solid fa-tags text-success me-2"></i>Dynamic Attributes:</span>
                                <span class="badge bg-success">Included</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="fa-solid fa-indian-rupee-sign text-info me-2"></i>Pricing & Margins:</span>
                                @if($canViewPricing)
                                    <span class="badge bg-success">Granted (pricing.view)</span>
                                @else
                                    <span class="badge bg-secondary">Masked (No Permission)</span>
                                @endif
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="fa-solid fa-warehouse text-warning me-2"></i>Stock & Inventory:</span>
                                @if($canViewInventory)
                                    <span class="badge bg-success">Granted (inventory.view)</span>
                                @else
                                    <span class="badge bg-secondary">Masked (No Permission)</span>
                                @endif
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="fa-solid fa-lock text-danger me-2"></i>Formula Injection Defense:</span>
                                <span class="badge bg-primary">Active (CWE-1236)</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Recent Exports Quick Widget -->
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-uppercase font-14"><i class="fa-solid fa-history me-2"></i>Recent Exports</h6>
                        <a href="{{ route('seller.catalogue.export.history') }}" class="small">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($recentExports as $exp)
                                <div class="list-group-item p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="font-13">#{{ $exp->id }} &mdash; {{ strtoupper($exp->export_format) }}</strong>
                                        @if($exp->status === 'completed')
                                            <span class="badge bg-success font-11">Ready</span>
                                        @elseif($exp->status === 'processing')
                                            <span class="badge bg-warning text-dark font-11">Processing</span>
                                        @else
                                            <span class="badge bg-danger font-11">Failed</span>
                                        @endif
                                    </div>
                                    <div class="small text-muted mb-2">
                                        {{ $exp->total_records }} rows &bull; {{ $exp->created_at?->diffForHumans() }}
                                    </div>
                                    @if($exp->isCompleted() && $exp->file_path)
                                        <a href="{{ route('seller.catalogue.export.download', $exp->id) }}" class="btn btn-sm btn-outline-success w-100">
                                            <i class="fa-solid fa-download me-1"></i> Download File
                                        </a>
                                    @endif
                                </div>
                            @empty
                                <div class="p-3 text-center text-muted small">
                                    No export history yet.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

