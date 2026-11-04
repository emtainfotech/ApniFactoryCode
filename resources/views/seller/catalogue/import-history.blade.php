@extends('layout')
@section('title', 'Catalogue Import History')
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
                        <li class="breadcrumb-item"><a href="{{ route('seller.catalogue.import') }}">Catalogue Import</a></li>
                        <li class="breadcrumb-item active" aria-current="page">History</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('seller.catalogue.import') }}" class="btn btn-primary">
                    <i class="fa-solid fa-cloud-arrow-up me-1"></i> New Import
                </a>
                <a href="{{ route('seller.catalogue.import.template', ['format' => 'xlsx']) }}" class="btn btn-outline-success">
                    <i class="fa-solid fa-file-excel me-1"></i> Download Template
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

        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <div>
                    <h5 class="mb-0 text-dark"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Batch Catalogue Import History</h5>
                    <p class="text-muted small mb-0">Audit trail of all catalogue bulk spreadsheets uploaded, validated, and executed for {{ $company->company_name ?? 'your seller company' }}.</p>
                </div>
                <div>
                    <span class="badge bg-light text-dark border font-12">{{ $imports->total() }} total batches</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0 font-13">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 70px;">Import #</th>
                                <th>File Name</th>
                                <th>Status</th>
                                <th>Processed Rows</th>
                                <th>Results (Create / Update)</th>
                                <th>Uploaded By</th>
                                <th>Date & Time</th>
                                <th class="text-end" style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($imports as $imp)
                                <tr>
                                    <td class="font-weight-bold text-muted">#{{ $imp->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($imp->import_format === 'xlsx')
                                                <i class="fa-solid fa-file-excel text-success font-20 me-2"></i>
                                            @else
                                                <i class="fa-solid fa-file-csv text-info font-20 me-2"></i>
                                            @endif
                                            <div>
                                                <div class="fw-bold">{{ $imp->original_filename }}</div>
                                                <small class="text-muted text-uppercase">{{ $imp->import_format }} &bull; {{ number_format($imp->file_size / 1024, 1) }} KB</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($imp->status === 'completed')
                                            <span class="badge bg-success font-11"><i class="fa-solid fa-circle-check me-1"></i>Completed</span>
                                        @elseif($imp->status === 'validated')
                                            <span class="badge bg-info text-dark font-11"><i class="fa-solid fa-hourglass-half me-1"></i>Awaiting Confirm</span>
                                        @elseif($imp->status === 'partial_success')
                                            <span class="badge bg-warning text-dark font-11"><i class="fa-solid fa-triangle-exclamation me-1"></i>Partial Success</span>
                                        @elseif($imp->status === 'processing')
                                            <span class="badge bg-primary font-11"><i class="fa-solid fa-spinner fa-spin me-1"></i>Processing</span>
                                        @elseif($imp->status === 'validation_failed')
                                            <span class="badge bg-danger font-11"><i class="fa-solid fa-circle-xmark me-1"></i>Validation Failed</span>
                                        @elseif($imp->status === 'failed')
                                            <span class="badge bg-danger font-11"><i class="fa-solid fa-circle-xmark me-1"></i>Failed</span>
                                        @else
                                            <span class="badge bg-secondary font-11">{{ ucfirst($imp->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div><strong>{{ $imp->total_rows }}</strong> total rows</div>
                                        <small class="text-muted">
                                            <span class="text-success">{{ $imp->valid_rows }} valid</span> &bull; 
                                            <span class="text-danger">{{ $imp->invalid_rows }} invalid</span>
                                        </small>
                                    </td>
                                    <td>
                                        @if($imp->isCompleted())
                                            <span class="badge bg-light-info text-info border border-info font-11">+{{ $imp->created_listings }} Created</span>
                                            <span class="badge bg-light-warning text-warning border border-warning font-11">{{ $imp->updated_listings }} Updated</span>
                                        @else
                                            <span class="text-muted">&mdash;</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $imp->user?->name ?? 'System' }}</div>
                                        <small class="text-muted">{{ $imp->user?->email }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $imp->created_at?->format('d M Y, h:i A') }}</div>
                                        <small class="text-muted">{{ $imp->created_at?->diffForHumans() }}</small>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            @if($imp->isValidated())
                                                <a href="{{ route('seller.catalogue.import.preview', $imp->id) }}" class="btn btn-sm btn-primary" title="Review & Confirm">
                                                    <i class="fa-solid fa-eye me-1"></i> Review
                                                </a>
                                            @else
                                                <a href="{{ route('seller.catalogue.import.preview', $imp->id) }}" class="btn btn-sm btn-outline-secondary" title="View Summary">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                            @endif

                                            @if($imp->error_report_path)
                                                <a href="{{ route('seller.catalogue.import.errors', $imp->id) }}" class="btn btn-sm btn-outline-danger" title="Download Errors (CSV)">
                                                    <i class="fa-solid fa-file-excel"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-folder-open font-30 mb-2 d-block"></i>
                                        No imports performed yet. Click <strong>"New Import"</strong> above to upload your first batch.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($imports->hasPages())
                    <div class="card-footer bg-white d-flex justify-content-end py-3">
                        {{ $imports->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
