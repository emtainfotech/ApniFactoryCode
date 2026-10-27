@extends('layout')
@section('title', 'Bulk Upload Products')
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
            <div class="ms-auto">
                <a href="{{ route('seller.product.bulk-sample') }}" class="btn btn-outline-success">
                    <i class="fa-solid fa-file-arrow-down me-1"></i> Download Sample CSV Template
                </a>
                <a href="{{ url('seller/product') }}" class="btn btn-secondary ms-2">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Products
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="fa fa-triangle-exclamation me-1"></i> Upload Issues:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-7">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h5 class="card-title text-primary"><i class="fa-solid fa-cloud-arrow-up me-2"></i> Upload Product & Variant Spreadsheet</h5>
                        <p class="text-muted">Import large inventories with multi-variant packing, shade cards, and wholesale pricing in seconds via CSV.</p>
                        <hr/>

                        <form action="{{ route('seller.product.bulk-upload.process') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="mb-4">
                                <label for="csv_file" class="form-label fw-bold">Select CSV File (.csv)</label>
                                <input type="file" name="csv_file" id="csv_file" class="form-control form-control-lg" accept=".csv,text/csv" required>
                                <small class="text-muted">Max file size: 10MB. File format must be standard UTF-8 CSV.</small>
                            </div>

                            <div class="d-flex align-items-center gap-3">
                                <button type="submit" class="btn btn-primary btn-lg px-4">
                                    <i class="fa-solid fa-upload me-2"></i> Start Bulk Import
                                </button>
                                <a href="{{ route('seller.product.bulk-sample') }}" class="btn btn-outline-secondary">
                                    <i class="fa-solid fa-download me-1"></i> Download Template
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm border-0 bg-light">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-circle-info text-info me-2"></i> CSV Column Specifications</h6>
                        <table class="table table-sm table-bordered bg-white">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Column Header</th>
                                    <th>Required</th>
                                    <th>Example</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>category_name</code></td>
                                    <td><span class="badge bg-danger">Yes</span></td>
                                    <td>Enamel Paints, Cotton Fabrics</td>
                                </tr>
                                <tr>
                                    <td><code>brand_name</code></td>
                                    <td><span class="badge bg-danger">Yes</span></td>
                                    <td>Asian Paints Pro, Raymond</td>
                                </tr>
                                <tr>
                                    <td><code>product_name</code></td>
                                    <td><span class="badge bg-danger">Yes</span></td>
                                    <td>Apcolite Premium Gloss</td>
                                </tr>
                                <tr>
                                    <td><code>title</code></td>
                                    <td><span class="badge bg-secondary">Optional</span></td>
                                    <td>High Gloss Enamel Paint</td>
                                </tr>
                                <tr>
                                    <td><code>description</code></td>
                                    <td><span class="badge bg-secondary">Optional</span></td>
                                    <td>Superior solvent paint...</td>
                                </tr>
                                <tr>
                                    <td><code>hsn_code</code></td>
                                    <td><span class="badge bg-secondary">Optional</span></td>
                                    <td>3208, 5208, 6205</td>
                                </tr>
                                <tr>
                                    <td><code>tax_percent</code></td>
                                    <td><span class="badge bg-danger">Yes</span></td>
                                    <td>18 (or 5, 12)</td>
                                </tr>
                                <tr>
                                    <td><code>box_packing</code></td>
                                    <td><span class="badge bg-danger">Yes</span></td>
                                    <td>1 Litre, 4 Litres, 25 Meters</td>
                                </tr>
                                <tr>
                                    <td><code>shade_or_color</code></td>
                                    <td><span class="badge bg-secondary">Optional</span></td>
                                    <td>Super White, Navy Blue</td>
                                </tr>
                                <tr>
                                    <td><code>seller_price</code></td>
                                    <td><span class="badge bg-danger">Yes</span></td>
                                    <td>400.00</td>
                                </tr>
                                <tr>
                                    <td><code>customer_mrp</code></td>
                                    <td><span class="badge bg-secondary">Optional</span></td>
                                    <td>500.00</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="alert alert-info py-2 px-3 mb-0 small">
                            <i class="fa fa-lightbulb me-1"></i> If an existing product name matches, new variants and prices will be appended/updated automatically without duplicating the parent product.
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
