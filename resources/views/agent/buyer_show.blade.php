@extends('layout')
@section('title', 'Assigned Buyer Details')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Field Agent Portal</div>
            <div class="ps-3">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('agent.dashboard') }}">Agent Dashboard</a></li>
                    <li class="breadcrumb-item active">{{ $buyerData['buyer']['name'] }}</li>
                </ol>
            </div>
            <div class="ms-auto">
                <a href="{{ route('agent.dashboard') }}" class="btn btn-sm btn-outline-secondary">Back to Dashboard</a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Dealer / Buyer Account Summary</h6>
                <span class="badge bg-primary">{{ $buyerData['attribution_ref'] }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><small class="text-muted">Dealer Name:</small><div class="fw-bold">{{ $buyerData['buyer']['name'] }}</div></div>
                    <div class="col-md-4"><small class="text-muted">Location:</small><div class="fw-bold">{{ $buyerData['buyer']['location'] ?? 'N/A' }}</div></div>
                    <div class="col-md-4"><small class="text-muted">Account Status:</small><div class="fw-bold">{{ ucfirst($buyerData['buyer']['account_status'] ?? 'active') }}</div></div>
                    <div class="col-md-4"><small class="text-muted">Contact Mobile:</small><div>{{ $buyerData['buyer']['mobile'] }}</div></div>
                    <div class="col-md-4"><small class="text-muted">Email:</small><div>{{ $buyerData['buyer']['email'] ?? 'N/A' }}</div></div>
                    <div class="col-md-4"><small class="text-muted">Attribution Status:</small><div><span class="badge bg-success">{{ ucfirst($buyerData['attribution_status']) }}</span></div></div>
                </div>
                <hr>
                <div class="p-3 bg-light rounded">
                    <div class="fw-bold small mb-1">First 3 Eligible Orders Progress</div>
                    <div class="text-secondary fw-bold">{{ $buyerData['eligible_order_progress']['display'] }}</div>
                    @if(!empty($buyerData['eligible_order_progress']['orders']))
                        <div class="table-responsive mt-2">
                            <table class="table table-sm table-bordered mb-0 bg-white">
                                <thead class="table-light"><tr><th>Order #</th><th>Slot</th><th>Eligibility Status</th><th>Incentive Status</th></tr></thead>
                                <tbody>
                                    @foreach($buyerData['eligible_order_progress']['orders'] as $eo)
                                    <tr>
                                        <td class="fw-bold">{{ $eo['order_no'] }}</td>
                                        <td>{{ $eo['sequence_number'] ? $eo['sequence_number'].'/3' : '-' }}</td>
                                        <td>{{ ucwords(str_replace('_', ' ', $eo['eligibility_status'])) }}</td>
                                        <td>{{ ucwords(str_replace('_', ' ', $eo['incentive_status'])) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

