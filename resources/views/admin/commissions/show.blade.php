@extends('layout')
@section('title', 'Commission Rule: ' . $rule->rule_code)
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Administration</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.commissions.rules.index') }}">Commission Rules</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $rule->rule_code }}</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('admin.commissions.rules.index') }}" class="btn btn-outline-secondary px-3">
                    <i class="bx bx-arrow-back me-1"></i> Back
                </a>
                @if($rule->status === 'pending_approval')
                    <form method="POST" action="{{ route('admin.commissions.rules.approve', $rule->id) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success px-3">
                            <i class="bx bx-check-circle me-1"></i> Approve Rule
                        </button>
                    </form>
                    <button type="button" class="btn btn-danger px-3" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bx bx-x-circle me-1"></i> Reject
                    </button>
                @elseif($rule->status === 'active' || $rule->status === 'scheduled')
                    <form method="POST" action="{{ route('admin.commissions.rules.deactivate', $rule->id) }}" class="d-inline" onsubmit="return confirm('Deactivate this commission rule?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger px-3">
                            <i class="bx bx-pause-circle me-1"></i> Deactivate
                        </button>
                    </form>
                @endif
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

        <!-- Active Conflicts Alert -->
        @if(!empty($conflicts))
            <div class="alert alert-warning border-0 bg-warning alert-dismissible fade show py-3">
                <div class="d-flex">
                    <div class="font-35 text-dark"><i class="bx bx-error-circle"></i></div>
                    <div class="ms-3 text-dark">
                        <h6 class="fw-bold mb-1">Overlapping Rule Conflict Detected</h6>
                        <p class="mb-0">This rule has {{ count($conflicts) }} active overlapping rule(s) with the exact same scope:</p>
                        <ul class="mb-0 mt-1">
                            @foreach($conflicts as $c)
                                <li><strong>{{ $c['rule_code'] }}</strong> ({{ $c['name'] }}): Effective from {{ $c['effective_from'] }} to {{ $c['effective_to'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Details Card -->
        <div class="card radius-10 mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-7 border-end">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <h4 class="mb-0 fw-bold">{{ $rule->name }}</h4>
                            <span class="badge bg-light-primary text-primary fs-6">{{ $rule->rule_code }}</span>
                            @if($rule->status === 'active')
                                <span class="badge bg-success fs-6">Active</span>
                            @elseif($rule->status === 'pending_approval')
                                <span class="badge bg-info text-dark fs-6">Pending Approval</span>
                            @elseif($rule->status === 'scheduled')
                                <span class="badge bg-warning text-dark fs-6">Scheduled</span>
                            @else
                                <span class="badge bg-secondary fs-6">{{ ucfirst($rule->status) }}</span>
                            @endif
                        </div>
                        <p class="text-secondary mb-1"><strong>Scope Target:</strong> {{ $rule->scope_label }}</p>
                        <p class="text-secondary mb-1"><strong>Agreement Reference:</strong> {{ $rule->agreement_ref ?: 'Standard Policy' }}</p>
                        <p class="text-secondary mb-1"><strong>Remarks:</strong> {{ $rule->remarks ?: 'No additional notes provided.' }}</p>
                        @if($rule->rejection_reason)
                            <div class="alert alert-danger py-2 mt-2">
                                <strong>Rejection Reason:</strong> {{ $rule->rejection_reason }}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-5 ps-md-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">Precedence Priority:</span>
                            <span class="badge bg-dark">Priority {{ \App\Services\CommissionCalculationService::PRECEDENCE_PRIORITY[$rule->scope_type] ?? 5 }} ({{ ucfirst($rule->scope_type) }})</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">Commission Rate:</span>
                            <span class="fw-bold text-success fs-5">{{ number_format($rule->commission_percentage, 2) }}%</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">Fixed Platform Fee:</span>
                            <span class="fw-bold">{{ $rule->fixed_fee > 0 ? '₹' . number_format($rule->fixed_fee, 2) : 'None' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">GST on Service:</span>
                            <span>{{ number_format($rule->gst_rate, 2) }}% ({{ $rule->is_gst_inclusive ? 'Inclusive' : 'Exclusive' }})</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">Effective Period:</span>
                            <span class="fw-bold">{{ $rule->effective_from->format('d M Y') }} &rarr; {{ $rule->effective_to ? $rule->effective_to->format('d M Y') : 'Open' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-0">
                            <span class="text-secondary">Created By:</span>
                            <span>{{ $rule->creator ? $rule->creator->name : 'System' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Audit Trail Card -->
        <div class="card radius-10">
            <div class="card-header bg-transparent py-3">
                <h6 class="mb-0 font-weight-bold">Audit History Timeline</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Timestamp</th>
                                <th>Actor</th>
                                <th>Event Type</th>
                                <th>Notes / Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rule->auditLogs as $log)
                                <tr>
                                    <td class="small">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                                    <td>{{ $log->actor ? $log->actor->name : 'System Cron Runner' }}</td>
                                    <td>
                                        <span class="badge bg-light-dark text-dark">{{ strtoupper($log->event_type) }}</span>
                                    </td>
                                    <td class="small text-secondary">{{ $log->notes }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No audit events logged yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.commissions.rules.reject', $rule->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Commission Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="4" required placeholder="Specify why this commercial rule is rejected..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

