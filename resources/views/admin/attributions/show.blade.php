@extends('layout')
@section('title', 'Attribution Detail - ' . $attribution->attribution_ref)
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Field Operations</div>
            <div class="ps-3">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.attributions.index') }}">Attributions</a></li>
                    <li class="breadcrumb-item active">{{ $attribution->attribution_ref }}</li>
                </ol>
            </div>
            <div class="ms-auto">
                <a href="{{ route('admin.attributions.index') }}" class="btn btn-sm btn-outline-secondary">Back to Attributions</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 bg-success text-white py-2">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger py-2">
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <!-- Core Record -->
                <div class="card mb-3">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">Attribution Record #{{ $attribution->attribution_ref }}</h6>
                        <span class="badge {{ $attribution->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($attribution->status) }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4"><small class="text-muted">Buyer / Dealer:</small><div class="fw-bold">{{ $attribution->customer?->name }}</div><div class="small text-muted">{{ $attribution->customer?->mobile }}</div></div>
                            <div class="col-md-4"><small class="text-muted">Field Agent:</small><div class="fw-bold">{{ $attribution->agent?->name }} ({{ $attribution->agent?->agent_code }})</div><div class="small text-muted">{{ $attribution->agent?->territory }}</div></div>
                            <div class="col-md-4"><small class="text-muted">Approval Status:</small><div class="fw-bold">{{ ucfirst(str_replace('_', ' ', $attribution->approval_status)) }}</div></div>
                            <div class="col-md-4"><small class="text-muted">Source:</small><div>{{ ucfirst(str_replace('_', ' ', $attribution->attribution_source)) }}</div></div>
                            <div class="col-md-4"><small class="text-muted">Evidence Reference:</small><div>{{ $attribution->evidence_reference ?: 'None provided' }}</div></div>
                            <div class="col-md-4"><small class="text-muted">Attributed By:</small><div>{{ $attribution->attributedBy?->name }} ({{ $attribution->attributed_at?->format('d M Y H:i') }})</div></div>
                            @if($attribution->remarks)
                                <div class="col-md-12"><small class="text-muted">Remarks:</small><div class="p-2 bg-light rounded">{{ $attribution->remarks }}</div></div>
                            @endif
                            @if($attribution->admin_notes)
                                <div class="col-md-12"><small class="text-danger fw-bold">Confidential Admin Notes:</small><div class="p-2 bg-light-warning rounded">{{ $attribution->admin_notes }}</div></div>
                            @endif
                        </div>

                        @if($attribution->reassignedFrom)
                            <div class="alert alert-info mt-3 mb-0 small">
                                Reassigned from previous record <a href="{{ route('admin.attributions.show', $attribution->reassignedFrom->id) }}">#{{ $attribution->reassignedFrom->attribution_ref }}</a> ({{ $attribution->reassignedFrom->agent?->agent_code }}). Reason: {{ $attribution->reassignment_reason }}
                            </div>
                        @endif
                        @if($attribution->supersededBy)
                            <div class="alert alert-secondary mt-3 mb-0 small">
                                Superseded by <a href="{{ route('admin.attributions.show', $attribution->supersededBy->id) }}">#{{ $attribution->supersededBy->attribution_ref }}</a> ({{ $attribution->supersededBy->agent?->agent_code }}).
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Audit Trail -->
                <div class="card">
                    <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold">Immutable Attribution History</h6></div>
                    <div class="card-body">
                        @foreach($attribution->history as $log)
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold text-primary">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span>
                                    <span class="small text-muted">{{ $log->created_at?->format('d M Y H:i:s') }}</span>
                                </div>
                                <div class="small text-dark">{{ $log->reason }}</div>
                                <div class="small text-muted">Actor: {{ $log->actor_name }} ({{ $log->actor_role }}) @if($log->evidence_reference) &bull; Ref: {{ $log->evidence_reference }} @endif</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                @if($attribution->approval_status === 'pending_approval')
                <div class="card mb-3 border-warning">
                    <div class="card-header bg-light-warning"><h6 class="mb-0 fw-bold">Pending Attribution Review</h6></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.attributions.approve', $attribution->id) }}" class="mb-3">
                            @csrf
                            <input type="text" name="notes" class="form-control form-control-sm mb-2" placeholder="Optional approval note">
                            <button type="submit" class="btn btn-success w-100">Approve & Activate Attribution</button>
                        </form>
                        <hr>
                        <form method="POST" action="{{ route('admin.attributions.reject', $attribution->id) }}">
                            @csrf
                            <textarea name="reason" rows="2" class="form-control form-control-sm mb-2" placeholder="Mandatory rejection reason" required></textarea>
                            <button type="submit" class="btn btn-danger w-100">Reject Attribution</button>
                        </form>
                    </div>
                </div>
                @endif

                @if($attribution->status === 'active')
                <div class="card">
                    <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold">Documented Reassignment Workflow</h6></div>
                    <div class="card-body">
                        @if($eligibleOrderStatus['has_eligible_order'])
                            <div class="alert alert-danger small">
                                <strong>Eligible Order Lock Active:</strong> This buyer has {{ $eligibleOrderStatus['paid_orders_count'] }} paid / {{ $eligibleOrderStatus['delivered_fulfilments_count'] }} delivered order(s). Per BRD Section 12, reassignment is blocked unless an approved corporate exception policy is active.
                            </div>
                        @else
                            <div class="alert alert-success small">
                                Buyer has 0 paid/eligible orders. Pre-order authorized reassignment is eligible.
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.attributions.reassign') }}">
                            @csrf
                            <input type="hidden" name="customer_id" value="{{ $attribution->customer_id }}">
                            <div class="mb-2">
                                <label class="form-label small fw-bold">New Active Field Agent</label>
                                <select name="new_field_agent_id" class="form-select form-select-sm" required>
                                    <option value="">-- Select Target Agent --</option>
                                    @foreach($activeAgents as $ag)
                                        <option value="{{ $ag->id }}">{{ $ag->agent_code }} - {{ $ag->name }} ({{ $ag->territory }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-bold">Reassignment Reason <span class="text-danger">*</span></label>
                                <textarea name="reason" rows="2" class="form-control form-control-sm" required placeholder="Document business justification"></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Evidence Reference</label>
                                <input type="text" name="evidence_reference" class="form-control form-control-sm" placeholder="Approval ticket / memo ID">
                            </div>
                            @if($eligibleOrderStatus['has_eligible_order'] && config('marketplace_agents.allow_reassignment_after_eligible_order_exception'))
                            <div class="form-check mb-2 small">
                                <input class="form-check-input" type="checkbox" name="is_exception_approved" value="1" id="excCheck">
                                <label class="form-check-label fw-bold text-danger" for="excCheck">Approved Policy Exception</label>
                                <input type="text" name="exception_approval_ref" class="form-control form-control-sm mt-1" placeholder="Executive Exception Approval Ref">
                            </div>
                            @endif
                            <button type="submit" class="btn btn-warning w-100 mt-2">Execute Authorized Reassignment</button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

