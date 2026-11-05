@extends('layout')
@section('title', 'My Settlements & Payouts')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Seller Portal</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('seller.pricing.revisions.index') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Settlements & Payouts</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="row row-cols-1 row-cols-md-2 mb-3">
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Settled Payables</p>
                                <h4 class="my-1 text-success">₹{{ number_format($totalPayable, 2) }}</h4>
                                <p class="mb-0 font-13">All-time earnings from orders</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto">
                                <i class="bx bx-wallet"></i>
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
                                <p class="mb-0 text-secondary">Pending / In Hold Period</p>
                                <h4 class="my-1 text-warning">₹{{ number_format($pendingPayable, 2) }}</h4>
                                <p class="mb-0 font-13">Awaiting delivery hold release</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-warning text-warning ms-auto">
                                <i class="bx bx-time-five"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settlements Table -->
        <div class="card">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="bx bx-money me-1"></i> My Order Settlements</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Settlement Ref</th>
                                <th>Order #</th>
                                <th>Gross Value</th>
                                <th>Marketplace Deductions</th>
                                <th>Net Payable</th>
                                <th>Status</th>
                                <th>Hold Until</th>
                                <th>Payout Ref</th>
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
                                        <div class="fw-bold">₹{{ number_format($st->gross_order_amount, 2) }}</div>
                                    </td>
                                    <td>
                                        <span class="text-danger">-₹{{ number_format($st->total_platform_deduction, 2) }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-success font-15">₹{{ number_format($st->net_seller_payable, 2) }}</div>
                                    </td>
                                    <td>
                                        @if($st->settlement_status === 'completed')
                                            <span class="badge bg-success">Disbursed</span>
                                        @elseif($st->settlement_status === 'eligible')
                                            <span class="badge bg-info text-dark">Ready for Payout</span>
                                        @elseif($st->settlement_status === 'on_hold')
                                            <span class="badge bg-warning text-dark">Hold Period</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $st->settlement_status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $st->hold_until ? $st->hold_until->format('d M Y') : 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <small class="font-monospace">{{ $st->payout_reference ?? 'Processing' }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        No settlement records available.
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

