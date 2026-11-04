@extends('layout')
@section('title', 'Import Product Catalogue')
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
                        <li class="breadcrumb-item active" aria-current="page">Catalogue Import (Excel/CSV)</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('seller.catalogue.import.template', ['format' => 'xlsx']) }}" class="btn btn-outline-success">
                    <i class="fa-solid fa-file-excel me-1"></i> Download Excel Template
                </a>
                <a href="{{ route('seller.catalogue.import.history') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-clock-rotate-left me-1"></i> Import History
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
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Upload Card -->
            <div class="col-xl-7">
                <div class="card border-top border-0 border-4 border-primary">
                    <div class="card-body p-4">
                        <div class="card-title d-flex align-items-center mb-3">
                            <div><i class="fa-solid fa-cloud-arrow-up me-2 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Stage 1: Upload Catalogue Spreadsheet</h5>
                        </div>
                        <p class="text-muted small">
                            Upload your completed spreadsheet (.xlsx or .csv). The system will deeply validate every row, category mapping, brand reference, packaging rule, and dynamic attribute before updating your live catalogue.
                        </p>
                        <hr>

                        <form method="POST" action="{{ route('seller.catalogue.import.validate') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label font-weight-bold">Select Excel or CSV File <span class="text-danger">*</span></label>
                                <input type="file" name="file" class="form-control form-control-lg" accept=".xlsx,.csv,.txt" required>
                                <small class="text-muted d-block mt-1">Supported formats: .xlsx (OpenXML), .csv (UTF-8). Maximum size: 20 MB.</small>
                            </div>

                            <button type="submit" class="btn btn-primary px-4 font-16">
                                <i class="fa-solid fa-check-double me-2"></i> Upload & Validate Rows
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Template Download Card -->
                <div class="card mt-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 text-uppercase font-14"><i class="fa-solid fa-download me-2 text-success"></i>Standardized Import Templates</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">
                            Always use the official Apni Factory template containing reference sheets for categories, subcategories, active brands, and dynamic attributes:
                        </p>
                        <div class="d-flex gap-2">
                            <a href="{{ route('seller.catalogue.import.template', ['format' => 'xlsx']) }}" class="btn btn-sm btn-outline-success">
                                <i class="fa-solid fa-file-excel me-1"></i> Multi-Sheet Excel (.xlsx)
                            </a>
                            <a href="{{ route('seller.catalogue.import.template', ['format' => 'csv']) }}" class="btn btn-sm btn-outline-info">
                                <i class="fa-solid fa-file-csv me-1"></i> Flat CSV (.csv)
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guidelines & Rules -->
            <div class="col-xl-5">
                <div class="card mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 text-uppercase font-14"><i class="fa-solid fa-circle-info me-2 text-info"></i>Import Guidelines & Validation Rules</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item px-0">
                                <strong><i class="fa-solid fa-key text-primary me-2"></i>Seller SKU:</strong>
                                Must be unique for your seller account. If an SKU already exists in your catalogue, it triggers an <code>UPDATE</code>; otherwise a <code>CREATE</code>.
                            </li>
                            <li class="list-group-item px-0">
                                <strong><i class="fa-solid fa-layer-group text-primary me-2"></i>Category & Subcategory:</strong>
                                Must match existing active categories in Apni Factory. The subcategory must belong to the chosen category.
                            </li>
                            <li class="list-group-item px-0">
                                <strong><i class="fa-solid fa-tags text-primary me-2"></i>Dynamic Attributes:</strong>
                                Format as <code>Attribute: Value; Attribute: Value</code>. Allowed options are checked against master attribute settings.
                            </li>
                            <li class="list-group-item px-0">
                                <strong><i class="fa-solid fa-shield-halved text-warning me-2"></i>Permissions Required:</strong>
                                Setting or updating prices requires <code>pricing.update</code>. Modifying stock quantities requires <code>inventory.update</code>.
                            </li>
                            <li class="list-group-item px-0">
                                <strong><i class="fa-solid fa-lock text-success me-2"></i>Two-Stage Safe Execution:</strong>
                                Uploading only validates. You will review a detailed validation summary before choosing to confirm and apply changes.
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Recent Imports Widget -->
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-uppercase font-14"><i class="fa-solid fa-history me-2"></i>Recent Imports</h6>
                        <a href="{{ route('seller.catalogue.import.history') }}" class="small">View Full History</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($recentImports as $imp)
                                <div class="list-group-item p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="font-13">#{{ $imp->id }} &mdash; {{ Str::limit($imp->original_filename, 22) }}</strong>
                                        @if($imp->status === 'completed')
                                            <span class="badge bg-success font-11">Completed</span>
                                        @elseif($imp->status === 'validated')
                                            <span class="badge bg-info text-dark font-11">Ready to Confirm</span>
                                        @elseif($imp->status === 'partial_success')
                                            <span class="badge bg-warning text-dark font-11">Partial</span>
                                        @else
                                            <span class="badge bg-danger font-11">{{ ucfirst($imp->status) }}</span>
                                        @endif
                                    </div>
                                    <div class="small text-muted">
                                        {{ $imp->total_rows }} total rows &bull; {{ $imp->created_at?->diffForHumans() }}
                                    </div>
                                    @if($imp->isValidated())
                                        <a href="{{ route('seller.catalogue.import.preview', $imp->id) }}" class="btn btn-sm btn-primary mt-2 w-100">
                                            <i class="fa-solid fa-eye me-1"></i> Review & Confirm
                                        </a>
                                    @endif
                                </div>
                            @empty
                                <div class="p-3 text-center text-muted small">
                                    No import history yet.
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
