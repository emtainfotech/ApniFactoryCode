@extends('layout')
@section('title', 'Seller Settlements & Payouts')
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
                        <li class="breadcrumb-item active" aria-current="page">Seller Settlements</li>
                    </ol>
                </nav>
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

        <!-- Metric Cards -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-3">
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Net Payable</p>
                                <h4 class="my-1 text-info">₹{{ number_format($metrics['total_payable'], 2) }}</h4>
                                <p class="mb-0 font-13">Net owed to sellers</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-info text-info ms-auto">
                                <i class="bx bx-money"></i>
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
                                <p class="mb-0 text-secondary">Platform Commissions</p>
                                <h4 class="my-1 text-primary">₹{{ number_format($metrics['total_commissions'], 2) }}</h4>
                                <p class="mb-0 font-13">Marketplace fees</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-primary text-primary ms-auto">
                                <i class="bx bx-pie-chart-alt-2"></i>
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
                                <p class="mb-0 text-secondary">On Hold / Eligible</p>
                                <h4 class="my-1 text-warning">{{ $metrics['on_hold_count'] + $metrics['eligible_count'] }}</h4>
                                <p class="mb-0 font-13">{{ $metrics['eligible_count'] }} Ready for Disbursement</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-warning text-warning ms-auto">
                                <i class="bx bx-time-five"></i>
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
                                <p class="mb-0 text-secondary">Completed Disbursed</p>
                                <h4 class="my-1 text-success">{{ $metrics['completed_count'] }}</h4>
                                <p class="mb-0 font-13">Settled to bank accounts</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto">
                                <i class="bx bx-check-double"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.finance.settlements.index') }}" class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Search Settlement Ref / Payout UTR</label>
                        <input type="text" name="search" class="form-control" placeholder="SETTLE-..., Bank UTR..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="settlement_status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="on_hold" {{ request('settlement_status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="eligible" {{ request('settlement_status') == 'eligible' ? 'selected' : '' }}>Eligible</option>
                            <option value="processing" {{ request('settlement_status') == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="completed" {{ request('settlement_status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary px-3"><i class="bx bx-search me-1"></i> Filter</button>
                        <a href="{{ route('admin.finance.settlements.index') }}" class="btn btn-outline-secondary px-3">Reset</a>
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
                                <th>Settlement Ref</th>
                                <th>Order #</th>
                                <th>Seller</th>
                                <th>Gross Amount</th>
                                <th>Platform Deductions</th>
                                <th>Net Seller Payable</th>
                                <th>Status</th>
                                <th>Hold Until</th>
                                <th>Payout Ref</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($settlements as $st)
                                <tr>
                                    <td>
                                        <div class="fw-bold font-monospace text-primary">{{ $st->settlement_ref }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">#{{ $st->order?->orderno ?? $st->order_id }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $st->company?->name ?? $st->seller?->name ?? 'Seller #' . $st->seller_user_id }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">₹{{ number_format($st->gross_order_amount, 2) }}</div>
                                    </td>
                                    <td>
                                        <small class="d-block text-danger">-₹{{ number_format($st->total_platform_deduction, 2) }}</small>
                                        <small class="text-muted">(Comm: ₹{{ number_format($st->commission_amount, 2) }} + GST: ₹{{ number_format($st->commission_gst_amount, 2) }})</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-success font-15">₹{{ number_format($st->net_seller_payable, 2) }}</div>
                                    </td>
                                    <td>
                                        @if($st->settlement_status === 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($st->settlement_status === 'eligible')
                                            <span class="badge bg-info text-dark">Eligible</span>
                                        @elseif($st->settlement_status === 'on_hold')
                                            <span class="badge bg-warning text-dark">On Hold</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $st->settlement_status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $st->hold_until ? $st->hold_until->format('d M Y') : 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <small class="font-monospace">{{ $st->payout_reference ?? 'Pending' }}</small>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#updateModal-{{ $st->id }}">
                                            <i class="bx bx-edit"></i> Update
                                        </button>

                                        <!-- Update Modal -->
                                        <div class="modal fade" id="updateModal-{{ $st->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered text-start">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('admin.finance.settlements.update-status', $st->id) }}">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Update Settlement: {{ $st->settlement_ref }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Net Seller Payable</label>
                                                                <div class="form-control-plaintext text-success fw-bold font-18">₹{{ number_format($st->net_seller_payable, 2) }}</div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Settlement Status <span class="text-danger">*</span></label>
                                                                <select name="status" class="form-select" required>
                                                                    <option value="on_hold" {{ $st->settlement_status == 'on_hold' ? 'selected' : '' }}>On Hold (Delivery validation pending)</option>
                                                                    <option value="eligible" {{ $st->settlement_status == 'eligible' ? 'selected' : '' }}>Eligible (Ready for batch disbursement)</option>
                                                                    <option value="processing" {{ $st->settlement_status == 'processing' ? 'selected' : '' }}>Processing (Sent to payment gateway)</option>
                                                                    <option value="completed" {{ $st->settlement_status == 'completed' ? 'selected' : '' }}>Completed (Disbursed to seller account)</option>
                                                                    <option value="cancelled" {{ $st->settlement_status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Bank UTR / Payout Reference</label>
                                                                <input type="text" name="payout_reference" class="form-control font-monospace" placeholder="e.g. UTR-HDFC-99887766" value="{{ $st->payout_reference }}">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Internal Notes</label>
                                                                <textarea name="notes" class="form-control" rows="2" placeholder="Settlement notes...">{{ $st->notes }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">
                                        No seller settlements found matching criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $settlements->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

