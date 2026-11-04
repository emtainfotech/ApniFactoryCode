@extends('layout')
@section('title', 'Catalogue Export History')
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
                        <li class="breadcrumb-item"><a href="{{ route('seller.catalogue.export') }}">Catalogue Export</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Export History</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="{{ route('seller.catalogue.export') }}" class="btn btn-success px-3">
                    <i class="fa-solid fa-plus me-1"></i> New Catalogue Export
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

        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-uppercase"><i class="fa-solid fa-clock-rotate-left me-2"></i>Catalogue Export Audit & History &mdash; {{ $company->name }}</h6>
                <span class="badge bg-secondary">{{ $exports->total() }} Total Exports</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th># ID</th>
                                <th>Format</th>
                                <th>Requested By</th>
                                <th>Applied Filters</th>
                                <th>Total Rows</th>
                                <th>File Size</th>
                                <th>Status</th>
                                <th>Date Requested</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($exports as $export)
                            <tr>
                                <td><strong>#{{ $export->id }}</strong></td>
                                <td>
                                    <span class="badge {{ $export->export_format === 'xlsx' ? 'bg-success' : 'bg-info text-dark' }}">
                                        {{ strtoupper($export->export_format) }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $export->user->name ?? 'User' }}</strong>
                                    <small class="d-block text-muted">{{ $export->user->email ?? '' }}</small>
                                </td>
                                <td>
                                    @if($export->filters && count($export->filters) > 0)
                                        <small class="text-muted">
                                            @foreach($export->filters as $k => $v)
                                                <span class="badge bg-light text-dark border">{{ $k }}: {{ is_array($v) ? implode(',', $v) : $v }}</span>
                                            @endforeach
                                        </small>
                                    @else
                                        <span class="text-muted small">All Products</span>
                                    @endif
                                </td>
                                <td><strong>{{ number_format($export->total_records) }}</strong></td>
                                <td>
                                    @if($export->file_size)
                                        <small class="text-muted">{{ round($export->file_size / 1024, 1) }} KB</small>
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                                <td>
                                    @if($export->status === 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($export->status === 'processing')
                                        <span class="badge bg-warning text-dark"><i class="fa-solid fa-spinner fa-spin me-1"></i>Processing</span>
                                    @elseif($export->status === 'pending')
                                        <span class="badge bg-secondary">Pending</span>
                                    @else
                                        <span class="badge bg-danger" title="{{ $export->error_message }}">Failed</span>
                                    @endif
                                </td>
                                <td>{{ $export->created_at?->format('d M Y, H:i') }}</td>
                                <td class="text-end pe-4">
                                    @if($export->isCompleted() && $export->file_path)
                                        <a href="{{ route('seller.catalogue.export.download', $export->id) }}" class="btn btn-sm btn-outline-success">
                                            <i class="fa-solid fa-download me-1"></i> Download
                                        </a>
                                    @elseif($export->isFailed())
                                        <small class="text-danger">{{ Str::limit($export->error_message, 25) }}</small>
                                    @else
                                        <span class="text-muted small">In Progress...</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    No export history found. <a href="{{ route('seller.catalogue.export') }}">Export your catalogue now</a>.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($exports->hasPages())
                <div class="card-footer bg-white">
                    {{ $exports->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

