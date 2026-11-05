@extends('layout')
@section('title', 'GST / HSN Tax Master')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Finance</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.finance.payments.index') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">GST / HSN Tax Master</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <button type="button" class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#newTaxMasterModal">
                    <i class="bx bx-plus-circle me-1"></i> New HSN Classification
                </button>
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

        @if(session('error'))
            <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show py-2">
                <div class="d-flex align-items-center">
                    <div class="font-35 text-white"><i class="bx bxs-message-square-x"></i></div>
                    <div class="ms-3 text-white">{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show py-2">
                <div class="d-flex align-items-center">
                    <div class="font-35 text-white"><i class="bx bxs-message-square-x"></i></div>
                    <div class="ms-3 text-white">
                        <ul class="mb-0">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Metric Cards -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-3">
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Master Records</p>
                                <h4 class="my-1 text-info">{{ $metrics['total_count'] }}</h4>
                                <p class="mb-0 font-13">All historical versions</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-info text-info ms-auto">
                                <i class="bx bx-receipt"></i>
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
                                <p class="mb-0 text-secondary">Approved & Active</p>
                                <h4 class="my-1 text-success">{{ $metrics['approved_count'] }}</h4>
                                <p class="mb-0 font-13">Ready for checkout calculation</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto">
                                <i class="bx bx-check-shield"></i>
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
                                <p class="mb-0 text-secondary">Pending Approval</p>
                                <h4 class="my-1 text-warning">{{ $metrics['pending_count'] }}</h4>
                                <p class="mb-0 font-13">Awaiting admin review</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-warning text-warning ms-auto">
                                <i class="bx bx-time-five"></i>
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
                                <p class="mb-0 text-secondary">Rejected / Inactive</p>
                                <h4 class="my-1 text-danger">{{ $metrics['rejected_count'] }}</h4>
                                <p class="mb-0 font-13">Unapproved submissions</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto">
                                <i class="bx bx-x-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.finance.tax-master.index') }}" class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Search HSN / Description</label>
                        <input type="text" name="search" class="form-control" placeholder="Search HSN code, product description..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="approval_status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="approved" {{ request('approval_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="pending" {{ request('approval_status') == 'pending' ? 'selected' : '' }}>Pending Review</option>
                            <option value="rejected" {{ request('approval_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary px-3"><i class="bx bx-search me-1"></i> Filter</button>
                        <a href="{{ route('admin.finance.tax-master.index') }}" class="btn btn-outline-secondary px-3">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>HSN / SAC</th>
                                <th>Product Tax Description</th>
                                <th>GST Rate</th>
                                <th>Type</th>
                                <th>Effective Period</th>
                                <th>Status</th>
                                <th>Version</th>
                                <th>Source / Verification</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $rec)
                                <tr>
                                    <td>
                                        <div class="fw-bold font-monospace text-primary">{{ $rec->hsn_code }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $rec->description }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light-dark text-dark fw-bold font-14">{{ number_format($rec->gst_rate, 2) }}%</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light-secondary text-secondary text-capitalize">{{ $rec->type }}</span>
                                    </td>
                                    <td>
                                        <small class="d-block">{{ $rec->effective_from->format('d M Y') }} &rarr; {{ $rec->effective_to ? $rec->effective_to->format('d M Y') : 'Indefinite' }}</small>
                                    </td>
                                    <td>
                                        @if($rec->approval_status === 'approved')
                                            <span class="badge bg-success"><i class="bx bx-check"></i> Approved</span>
                                        @elseif($rec->approval_status === 'pending')
                                            <span class="badge bg-warning text-dark"><i class="bx bx-time"></i> Pending</span>
                                        @elseif($rec->approval_status === 'rejected')
                                            <span class="badge bg-danger"><i class="bx bx-x"></i> Rejected</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $rec->approval_status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light-info text-info">v{{ $rec->version }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted d-block">{{ $rec->source_reference ?? 'Official Notification' }}</small>
                                        @if($rec->last_verified_at)
                                            <small class="text-success"><i class="bx bx-check-circle"></i> Verified {{ $rec->last_verified_at->format('d M Y') }}</small>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($rec->approval_status === 'pending')
                                            <div class="btn-group">
                                                <form method="POST" action="{{ route('admin.finance.tax-master.approve', $rec->id) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                        <i class="bx bx-check"></i> Approve
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal-{{ $rec->id }}" title="Reject">
                                                    <i class="bx bx-x"></i>
                                                </button>
                                            </div>

                                            <!-- Reject Modal -->
                                            <div class="modal fade" id="rejectModal-{{ $rec->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered text-start">
                                                    <div class="modal-content">
                                                        <form method="POST" action="{{ route('admin.finance.tax-master.reject', $rec->id) }}">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Reject Tax Classification: {{ $rec->hsn_code }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <label class="form-label fw-bold">Reason for Rejection <span class="text-danger">*</span></label>
                                                                <textarea name="reason" class="form-control" rows="3" placeholder="Provide reason for rejecting this HSN/GST classification..." required></textarea>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted small">&mdash;</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        No GST / HSN tax master records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $records->links() }}
                </div>
            </div>
        </div>

        <!-- New Classification Modal -->
        <div class="modal fade" id="newTaxMasterModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.finance.tax-master.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">New GST / HSN Classification</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">HSN / SAC Code <span class="text-danger">*</span></label>
                                <input type="text" name="hsn_code" class="form-control font-monospace" placeholder="e.g. 3208, 3209, 998311" required>
                                <small class="text-muted">HSN: 4, 6, or 8 digits. SAC: 6 digits starting with 99.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                <input type="text" name="description" class="form-control" placeholder="Product or service description" required>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">GST Rate (%) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="gst_rate" class="form-control" placeholder="e.g. 18.00" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Classification Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" required>
                                        <option value="goods" selected>Goods</option>
                                        <option value="services">Services</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Effective From <span class="text-danger">*</span></label>
                                    <input type="date" name="effective_from" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Effective To</label>
                                    <input type="date" name="effective_to" class="form-control" placeholder="Optional end date">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Statutory Source / Notification Reference</label>
                                <input type="text" name="source_reference" class="form-control" placeholder="e.g. CBIC Notification No. 1/2017-Central Tax (Rate)">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Create Classification</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

