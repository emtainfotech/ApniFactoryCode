@extends('layout')
@section('title', 'Field Agent Detail - ' . $agent->agent_code)
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Field Operations</div>
            <div class="ps-3">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.agents.index') }}">Agents</a></li>
                    <li class="breadcrumb-item active">{{ $agent->agent_code }}</li>
                </ol>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('admin.agents.edit', $agent->id) }}" class="btn btn-sm btn-outline-primary">Edit Profile</a>
                <a href="{{ route('admin.agents.index') }}" class="btn btn-sm btn-outline-secondary">Back to Directory</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 bg-success text-white alert-dismissible fade show py-2">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <!-- Agent Profile Overview -->
                <div class="card mb-3">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">{{ $agent->name }} ({{ $agent->agent_code }})</h6>
                        <span class="badge {{ $agent->isActive() ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($agent->status) }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4"><small class="text-muted">Mobile:</small><div class="fw-bold">{{ $agent->mobile }}</div></div>
                            <div class="col-md-4"><small class="text-muted">Email:</small><div class="fw-bold">{{ $agent->email ?: 'N/A' }}</div></div>
                            <div class="col-md-4"><small class="text-muted">Joining Date:</small><div class="fw-bold">{{ $agent->joining_date?->format('d M Y') ?? 'N/A' }}</div></div>
                            <div class="col-md-4"><small class="text-muted">Territory:</small><div class="fw-bold">{{ $agent->territory }}</div></div>
                            <div class="col-md-4"><small class="text-muted">Supervisor:</small><div class="fw-bold">{{ $agent->supervisor_name ?? $agent->supervisor?->name ?? 'Direct' }}</div></div>
                            <div class="col-md-4"><small class="text-muted">Portal Login Linked:</small><div class="fw-bold">{{ $agent->user ? $agent->user->email : 'Not Linked' }}</div></div>
                        </div>

                        <hr>
                        <h6 class="fw-bold text-danger small mb-2">Restricted Policy & Payout Information (Admin-Only Visibility)</h6>
                        <div class="row g-2 bg-light p-2 rounded">
                            <div class="col-md-4"><small class="text-muted">KYC Status:</small><div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $agent->kyc_status)) }}</div></div>
                            <div class="col-md-4"><small class="text-muted">Contract Status:</small><div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $agent->contract_status)) }}</div></div>
                            <div class="col-md-4"><small class="text-muted">KYC Ref:</small><div class="fw-semibold">{{ $agent->kyc_reference ?: 'None' }}</div></div>
                            @php $payout = $agent->maskedPayoutSummary(); @endphp
                            <div class="col-md-12">
                                <small class="text-muted">Payout Summary:</small>
                                <div>
                                    @if(!empty($payout['configured']))
                                        Bank: {{ $payout['bank_name'] ?? 'N/A' }} | A/C: {{ $payout['account_masked'] ?? 'N/A' }} | IFSC: {{ $payout['ifsc_code'] ?? 'N/A' }} | UPI: {{ $payout['upi_masked'] ?? 'N/A' }}
                                    @else
                                        Not configured
                                    @endif
                                </div>
                            </div>
                            @if($agent->notes)
                                <div class="col-md-12"><small class="text-muted">Internal Notes:</small><div class="text-dark">{{ $agent->notes }}</div></div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Assigned Buyers -->
                <div class="card mb-3">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">Buyer Attribution Portfolio ({{ $agent->attributions_count }} Historical & Active)</h6>
                        <a href="{{ route('admin.attributions.index', ['field_agent_id' => $agent->id]) }}" class="btn btn-sm btn-outline-primary">Manage Attributions</a>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ref</th>
                                    <th>Buyer / Dealer</th>
                                    <th>Status</th>
                                    <th>Attributed At</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($agent->attributions as $attr)
                                <tr>
                                    <td class="fw-bold">{{ $attr->attribution_ref }}</td>
                                    <td>{{ $attr->customer?->name }} <span class="text-muted small">({{ $attr->customer?->location ?? 'N/A' }})</span></td>
                                    <td><span class="badge {{ $attr->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($attr->status) }}</span></td>
                                    <td class="small">{{ $attr->attributed_at?->format('d M Y') }}</td>
                                    <td class="text-end"><a href="{{ route('admin.attributions.show', $attr->id) }}" class="btn btn-sm btn-outline-secondary">Inspect</a></td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-3 text-muted">No buyer attributions linked yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Status Management Control -->
                <div class="card mb-3">
                    <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold">Status Management</h6></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.agents.status', $agent->id) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">Target Status</label>
                                <select name="status" class="form-select">
                                    @foreach(config('marketplace_agents.agent_statuses') as $key => $label)
                                        <option value="{{ $key }}" {{ $agent->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status Change Reason</label>
                                <textarea name="reason" rows="2" class="form-control" placeholder="Document activation/deactivation justification"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Update Operational Status</button>
                        </form>
                    </div>
                </div>

                <!-- Status Audit Log -->
                <div class="card">
                    <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold">Activation & Audit History</h6></div>
                    <div class="card-body">
                        @forelse($agent->statusHistory as $log)
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <strong class="small text-primary">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</strong>
                                    <span class="small text-muted">{{ $log->created_at?->format('d M y H:i') }}</span>
                                </div>
                                <div class="small text-dark">{{ $log->reason }}</div>
                                <div class="small text-muted">By: {{ $log->actor_name }} @if($log->new_status) ({{ $log->previous_status ?? 'none' }} &rarr; {{ $log->new_status }}) @endif</div>
                            </div>
                        @empty
                            <div class="text-muted small">No status events logged.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

