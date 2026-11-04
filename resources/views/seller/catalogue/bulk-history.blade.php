@extends('layout')
@section('title', 'Bulk Operations History')
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
                        <li class="breadcrumb-item"><a href="{{ route('seller.catalogue.bulk') }}">Bulk Catalogue Manager</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Operations History</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="{{ route('seller.catalogue.bulk') }}" class="btn btn-primary">
                    <i class="fa-solid fa-plus me-1"></i> New Bulk Operation
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
            <div class="card-header bg-transparent py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="mb-0 text-dark"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Audit Trail & Bulk Execution History</h6>
                        <small class="text-muted">Comprehensive log of all bulk catalogue operations performed on your company catalogue.</small>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Operation ID</th>
                                <th>Type & Target</th>
                                <th>Selection Scope</th>
                                <th>Counts Summary</th>
                                <th>Status</th>
                                <th>Requested By</th>
                                <th>Date & Time</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($operations as $op)
                                <tr>
                                    <td>
                                        <strong class="font-monospace text-primary">#{{ $op->id }}</strong>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ strtoupper(str_replace('_', ' ', $op->operation_type)) }}</div>
                                        <small class="text-muted">Entity: {{ ucfirst($op->target_entity) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ strtoupper($op->selection_mode) }}</span>
                                        <div class="small text-muted">{{ $op->total_matching }} items matched</div>
                                    </td>
                                    <td>
                                        @if($op->status === 'completed')
                                            <div><span class="badge bg-success">{{ $op->success_count }} succeeded</span></div>
                                            @if($op->failed_count > 0)
                                                <div class="mt-1"><span class="badge bg-danger">{{ $op->failed_count }} failed</span></div>
                                            @endif
                                        @else
                                            <div class="small text-muted">
                                                <span>Eligible: <strong>{{ $op->eligible_count }}</strong></span> /
                                                <span>Blocked: <strong>{{ $op->ineligible_count }}</strong></span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($op->status === 'completed')
                                            <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Completed</span>
                                        @elseif($op->status === 'processing')
                                            <span class="badge bg-info text-dark"><i class="fa-solid fa-spinner fa-spin me-1"></i>Processing</span>
                                        @elseif($op->status === 'previewed')
                                            <span class="badge bg-warning text-dark"><i class="fa-solid fa-eye me-1"></i>Previewed</span>
                                        @else
                                            <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i>{{ ucfirst($op->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $op->user->name ?? 'User #'.$op->user_id }}</div>
                                        <small class="text-muted">{{ $op->user->email ?? '' }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $op->created_at->format('d M Y, h:i A') }}</div>
                                        <small class="text-muted">{{ $op->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#opDetailsModal{{ $op->id }}" title="View Details">
                                            <i class="fa-solid fa-info-circle me-1"></i> Details
                                        </button>
                                        @if($op->canConfirm())
                                            <form method="POST" action="{{ route('seller.catalogue.bulk.confirm', $op->id) }}" class="d-inline" onsubmit="return confirm('Execute bulk operation #{{ $op->id }} for {{ $op->eligible_count }} eligible records?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fa-solid fa-bolt me-1"></i> Execute
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>

                                <!-- Modal Details for Operation -->
                                <div class="modal fade" id="opDetailsModal{{ $op->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Bulk Operation #{{ $op->id }} Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3 mb-3">
                                                    <div class="col-sm-6">
                                                        <div class="small text-muted">Operation Type</div>
                                                        <div class="fw-bold">{{ strtoupper($op->operation_type) }}</div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="small text-muted">Execution Status</div>
                                                        <div class="fw-bold">{{ ucfirst($op->status) }}</div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="small text-muted">Selection Mode</div>
                                                        <div>{{ strtoupper($op->selection_mode) }}</div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="small text-muted">Target Entity</div>
                                                        <div>{{ ucfirst($op->target_entity) }}</div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold">Update Payload</label>
                                                    <pre class="bg-light p-2 rounded small" style="max-height: 150px; overflow-y: auto;">{{ json_encode($op->update_payload, JSON_PRETTY_PRINT) }}</pre>
                                                </div>

                                                @if(!empty($op->error_log))
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold text-danger">Errors / Ineligibility Log</label>
                                                        <pre class="bg-light text-danger p-2 rounded small" style="max-height: 150px; overflow-y: auto;">{{ json_encode($op->error_log, JSON_PRETTY_PRINT) }}</pre>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-clock-rotate-left font-30 mb-2 d-block"></i>
                                        No bulk operations recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($operations->hasPages())
                <div class="card-footer bg-white border-top-0 d-flex justify-content-end py-3">
                    {{ $operations->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection