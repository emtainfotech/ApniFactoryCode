@extends('layout')
@section('title', 'Eligible Order Inspection')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Field Agent Incentives</div>
            <div class="ps-3">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.incentives.eligible-orders') }}">Eligible Orders</a></li>
                    <li class="breadcrumb-item active">{{ $eligibleOrder->eligibility_ref }}</li>
                </ol>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold">Parent Order &amp; Buyer Progress ({{ $progress['display'] }})</h6></div>
                    <div class="card-body small">
                        <p><strong>Parent Order No:</strong> {{ $eligibleOrder->orderno }}</p>
                        <p><strong>Buyer / Dealer:</strong> {{ $eligibleOrder->customer?->name }} (#{{ $eligibleOrder->customer_id }})</p>
                        <p><strong>Field Agent:</strong> {{ $eligibleOrder->agent?->agent_code }} — {{ $eligibleOrder->agent?->name }}</p>
                        <p><strong>Attribution Ref:</strong> {{ $eligibleOrder->attribution?->attribution_ref }}</p>
                        <p><strong>Assigned Slot:</strong> {{ $eligibleOrder->sequence_number ? $eligibleOrder->sequence_number . '/3' : 'None Consumed' }}</p>
                        <p><strong>Eligibility Status:</strong> <span class="badge bg-primary">{{ $eligibleOrder->eligibility_status }}</span></p>
                        <p><strong>Incentive Generation:</strong> <span class="badge bg-secondary">{{ $eligibleOrder->incentive_generation_status }}</span></p>
                        @if($eligibleOrder->incentive_block_reason)
                            <div class="alert alert-warning py-2 mb-0">{{ $eligibleOrder->incentive_block_reason }}</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold">Linked Seller Fulfilment Records ({{ $eligibleOrder->seller_count }})</h6></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead class="table-light"><tr><th>Fulfilment Ref</th><th>Seller Order</th><th>Fulfilment Status</th><th>Dispute</th></tr></thead>
                            <tbody>
                                @forelse(($eligibleOrder->seller_fulfilment_refs ?? []) as $ref)
                                <tr>
                                    <td>{{ $ref['fulfilment_ref'] ?? '-' }}</td>
                                    <td>{{ $ref['order_no'] ?? '#'.$ref['order_id'] }} ({{ $ref['company_name'] ?? 'Seller' }})</td>
                                    <td><span class="badge bg-info text-dark">{{ $ref['fulfilment_status'] ?? '-' }}</span></td>
                                    <td>{{ $ref['dispute_status'] ?? 'none' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Awaiting seller fulfilment initialization.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold">Immutable Audit History</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Timestamp</th><th>Action</th><th>Transition</th><th>Reason</th></tr></thead>
                    <tbody>
                        @forelse($auditLogs as $log)
                        <tr>
                            <td class="small">{{ $log->created_at }}</td>
                            <td class="fw-bold small">{{ $log->action }}</td>
                            <td class="small">{{ $log->previous_status ?? '-' }} &rarr; {{ $log->new_status ?? '-' }}</td>
                            <td class="small">{{ $log->reason }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No audit events recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

