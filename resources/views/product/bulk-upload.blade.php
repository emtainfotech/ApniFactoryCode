@extends('layout')
@section('title', 'Bulk Upload Products (Excel & CSV)')
@section('content')

<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Product Catalog</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ url('seller/dashboard') }}"><i class="fa fa-home"></i> Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('seller/product') }}">Products</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Bulk Upload</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('seller.product.bulk-sample-excel') }}" class="btn btn-success shadow-sm">
                    <i class="fa-solid fa-file-excel me-1"></i> Download Sample Excel (.xlsx)
                </a>
                <a href="{{ route('seller.product.bulk-sample') }}" class="btn btn-outline-success">
                    <i class="fa-solid fa-file-csv me-1"></i> Download CSV Template
                </a>
                <a href="{{ url('seller/product') }}" class="btn btn-secondary ms-1">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Products
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fa fa-check-circle fs-4 me-2"></i>
                    <div>
                        <strong>Success!</strong> {{ session('success') }}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <div class="d-flex align-items-start">
                    <i class="fa fa-triangle-exclamation fs-4 me-2 mt-1"></i>
                    <div>
                        <strong>Import Issues Detected:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Left Column: Upload Form -->
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h5 class="card-title text-primary mb-0">
                                <i class="fa-solid fa-cloud-arrow-up me-2"></i> Upload Excel / CSV Spreadsheet
                            </h5>
                            <span class="badge bg-light text-primary border">Multi-Variant & Shade Card Supported</span>
                        </div>
                        <p class="text-muted small">
                            Simplify your product catalog management. Import multiple products, sizes, packings, and shade cards with individual wholesale prices in a single upload.
                        </p>
                        <hr/>

                        <form action="{{ route('seller.product.bulk-upload.process') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <!-- 1. Spreadsheet File Input -->
                            <div class="mb-4">
                                <label for="spreadsheet_file" class="form-label fw-bold">
                                    1. Select Spreadsheet File <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fa-solid fa-file-excel text-success fs-5"></i></span>
                                    <input type="file" name="spreadsheet_file" id="spreadsheet_file" class="form-control form-control-lg" accept=".xlsx,.xls,.csv" required>
                                </div>
                                <div class="form-text text-muted">
                                    Supported formats: <strong>.xlsx</strong> (Microsoft Excel OpenXML), <strong>.xls</strong>, or standard <strong>.csv</strong>. Max file size: 20MB.
                                </div>
                            </div>

                            <!-- 2. Product Images Section -->
                            <div class="card border border-light-subtle bg-light mb-4">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold text-dark mb-2">
                                        <i class="fa-solid fa-images text-primary me-2"></i> 2. Product Images Options
                                    </h6>
                                    <p class="text-muted small mb-3">
                                        You have 3 flexible ways to provide product images:
                                    </p>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small">Option A: Select Image Files</label>
                                            <input type="file" name="image_files[]" class="form-control form-control-sm" multiple accept="image/*">
                                            <small class="text-muted d-block mt-1">Select one or more images matching filenames in your sheet.</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold small">Option B: ZIP Archive of Images</label>
                                            <input type="file" name="images_zip" class="form-control form-control-sm" accept=".zip">
                                            <small class="text-muted d-block mt-1">Upload a .zip file containing all product photos.</small>
                                        </div>
                                    </div>

                                    <div class="alert alert-secondary py-2 px-3 mt-3 mb-0 small">
                                        <i class="fa fa-info-circle text-info me-1"></i>
                                        <strong>Option C (Embedded in Excel):</strong> If you copy/paste images directly into your Excel (.xlsx) rows, our system will automatically extract and link them to each product!
                                    </div>
                                </div>
                            </div>

                            <!-- Commission & Pricing Info -->
                            <div class="alert alert-info py-2 px-3 mb-4 small d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="fa-solid fa-calculator me-1"></i>
                                    <strong>Your Commission Rate:</strong> {{ $company->comission ?? 5 }}% 
                                    <span class="text-muted ms-2">(Wholesale Customer Price = Seller Base Price + {{ $company->comission ?? 5 }}%)</span>
                                </div>
                                <span class="badge bg-primary">Auto-Calculated</span>
                            </div>

                            <!-- Submit & Download Buttons -->
                            <div class="d-flex align-items-center gap-3">
                                <button type="submit" class="btn btn-primary btn-lg px-4 shadow-sm">
                                    <i class="fa-solid fa-upload me-2"></i> Start Bulk Import
                                </button>
                                <a href="{{ route('seller.product.bulk-sample-excel') }}" class="btn btn-outline-success">
                                    <i class="fa-solid fa-download me-1"></i> Download Sample Excel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Live System Reference Accordion -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fa-solid fa-database text-info me-2"></i> Live System References & Available Values
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="accordion accordion-flush" id="systemReferencesAccordion">
                            
                            <!-- Available Shade Cards -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingShades">
                                    <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseShades" aria-expanded="false" aria-controls="collapseShades">
                                        <i class="fa-solid fa-palette text-danger me-2"></i> Available Shade Cards ({{ count($shadeCards ?? []) }} Active Shades)
                                    </button>
                                </h2>
                                <div id="collapseShades" class="accordion-collapse collapse" aria-labelledby="headingShades" data-bs-parent="#systemReferencesAccordion">
                                    <div class="accordion-body p-3">
                                        <p class="small text-muted mb-2">You can copy these shade names into the <code>shade_or_color</code> column. If you enter a new shade name, the system creates it automatically.</p>
                                        <div class="d-flex flex-wrap gap-2" style="max-height: 250px; overflow-y: auto;">
                                            @forelse($shadeCards as $sc)
                                                <span class="badge bg-white text-dark border d-inline-flex align-items-center gap-2 p-2">
                                                    <span style="display:inline-block; width:16px; height:16px; border-radius:50%; background-color: {{ $sc->hexcode ?: '#ccc' }}; border: 1px solid #999;"></span>
                                                    <span>{{ $sc->name }}</span>
                                                    <small class="text-muted">({{ $sc->hexcode }})</small>
                                                </span>
                                            @empty
                                                <span class="text-muted small">No shade cards defined yet.</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Available Packing Sizes -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingPackings">
                                    <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePackings" aria-expanded="false" aria-controls="collapsePackings">
                                        <i class="fa-solid fa-box text-warning me-2"></i> Available Packing Sizes ({{ count($boxPackings ?? []) }} Active Sizes)
                                    </button>
                                </h2>
                                <div id="collapsePackings" class="accordion-collapse collapse" aria-labelledby="headingPackings" data-bs-parent="#systemReferencesAccordion">
                                    <div class="accordion-body p-3">
                                        <p class="small text-muted mb-2">Use these exact packing names in the <code>box_packing</code> column:</p>
                                        <div class="d-flex flex-wrap gap-2" style="max-height: 200px; overflow-y: auto;">
                                            @forelse($boxPackings as $bp)
                                                <span class="badge bg-light text-dark border p-2">
                                                    <i class="fa fa-cube me-1 text-secondary"></i> {{ $bp->name }} 
                                                    @if($bp->pcs > 1)<small class="text-muted">({{ $bp->pcs }} pcs/box)</small>@endif
                                                </span>
                                            @empty
                                                <span class="text-muted small">No packings defined.</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Available Brands -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingBrands">
                                    <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBrands" aria-expanded="false" aria-controls="collapseBrands">
                                        <i class="fa-solid fa-tag text-success me-2"></i> Approved Brands ({{ count($Brandlist ?? []) }} Brands)
                                    </button>
                                </h2>
                                <div id="collapseBrands" class="accordion-collapse collapse" aria-labelledby="headingBrands" data-bs-parent="#systemReferencesAccordion">
                                    <div class="accordion-body p-3">
                                        <div class="d-flex flex-wrap gap-2">
                                            @forelse($Brandlist as $b)
                                                <span class="badge bg-light text-primary border p-2">
                                                    <i class="fa fa-certificate me-1"></i> {{ $b->name }}
                                                </span>
                                            @empty
                                                <span class="text-muted small">No brands found.</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Column Specifications & Guide -->
            <div class="col-lg-5">
                <div class="card shadow-sm border-0 bg-light mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark mb-3">
                            <i class="fa-solid fa-table-list text-primary me-2"></i> Spreadsheet Column Specifications
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered bg-white small mb-0">
                                <thead class="table-secondary">
                                    <tr>
                                        <th>Column Header</th>
                                        <th>Required</th>
                                        <th>Description & Example</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>category_name</code></td>
                                        <td><span class="badge bg-danger">Yes</span></td>
                                        <td>Product Category (e.g. <em>Enamel Paints, Cotton Fabrics</em>)</td>
                                    </tr>
                                    <tr>
                                        <td><code>brand_name</code></td>
                                        <td><span class="badge bg-danger">Yes</span></td>
                                        <td>Brand Name (e.g. <em>Asian Paints Pro, Raymond</em>)</td>
                                    </tr>
                                    <tr>
                                        <td><code>product_name</code></td>
                                        <td><span class="badge bg-danger">Yes</span></td>
                                        <td>Parent Product Name (e.g. <em>Apcolite Premium Gloss</em>)</td>
                                    </tr>
                                    <tr>
                                        <td><code>title</code></td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td>Short marketing headline (defaults to product name)</td>
                                    </tr>
                                    <tr>
                                        <td><code>description</code></td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td>Product specifications and factory features</td>
                                    </tr>
                                    <tr>
                                        <td><code>hsn_code</code></td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td>GST HSN Code (e.g. <em>3208, 5208, 6205</em>)</td>
                                    </tr>
                                    <tr>
                                        <td><code>tax_percent</code></td>
                                        <td><span class="badge bg-danger">Yes</span></td>
                                        <td>GST Rate (e.g. <em>18, 12, 5, 28</em>)</td>
                                    </tr>
                                    <tr>
                                        <td><code>box_packing</code></td>
                                        <td><span class="badge bg-danger">Yes</span></td>
                                        <td>Size/Pack (e.g. <em>1 Litre, 4 Litres, 25 Meters, Box of 12</em>)</td>
                                    </tr>
                                    <tr>
                                        <td><code>shade_or_color</code></td>
                                        <td><span class="badge bg-danger">Yes</span></td>
                                        <td>Shade Name (e.g. <em>Super White, Royal Blue, Navy Blue</em>)</td>
                                    </tr>
                                    <tr>
                                        <td><code>shade_hexcode</code></td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td>Hex Code for shade (e.g. <em>#FFFFFF, #1E3A8A</em>)</td>
                                    </tr>
                                    <tr>
                                        <td><code>seller_price</code></td>
                                        <td><span class="badge bg-danger">Yes</span></td>
                                        <td>Your base factory rate (e.g. <em>380.00</em>)</td>
                                    </tr>
                                    <tr>
                                        <td><code>customer_mrp</code></td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td>Retail list/strikethrough MRP (e.g. <em>490.00</em>)</td>
                                    </tr>
                                    <tr>
                                        <td><code>image_url_or_filename</code></td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td>Direct Image URL, companion filename, or embedded Excel image</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- How multi-variant grouping works -->
                        <div class="card border-0 bg-white mt-3 p-3">
                            <h6 class="fw-bold text-dark small mb-2">
                                <i class="fa-solid fa-sitemap text-primary me-1"></i> How Multi-Variant Grouping Works:
                            </h6>
                            <p class="small text-muted mb-0">
                                If you repeat the same <strong>product_name</strong> across multiple rows with different <strong>box_packing</strong> (sizes) and <strong>shade_or_color</strong> (shades), our system automatically creates <strong>ONE parent product</strong> with all those sizes and shades attached as variants!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
