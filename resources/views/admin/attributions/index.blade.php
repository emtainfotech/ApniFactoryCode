@extends('layout')
@section('title', 'Buyer / Dealer Agent Attributions')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Field Operations</div>
            <div class="ps-3">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.agents.index') }}">Field Agents</a></li>
                    <li class="breadcrumb-item active">Buyer Attributions</li>
                </ol>
            </div>
            <div class="ms-auto">
                <a href="{{ route('admin.agents.index') }}" class="btn btn-sm btn-outline-secondary">Agent Master Directory</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 bg-success text-white alert-dismissible fade show py-2">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-warning border-start border-4 border-danger bg-light-danger text-dark alert-dismissible fade show">
                <strong>Attribution / Policy Validation Warning:</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="alert alert-info border-0 bg-light-info py-2 mb-3 small text-dark">
            <strong>Section 12 Attribution Rule:</strong> One active primary field agent per buyer/dealer account. Reassignment is restricted to authorized approval prior to the buyer's first eligible order. Attributions link agents to buyers (never manufacturers/sellers).
        </div>

        <!-- KPI Summary -->
        <div class="row row-cols-1 row-cols-md-5 mb-3">
            <div class="col"><div class="card radius-10 border-start border-4 border-primary"><div class="card-body"><p class="mb-0 text-secondary">Total Records</p><h4 class="my-1 text-primary">{{ $metrics['total_attributions'] }}</h4></div></div></div>
            <div class="col"><div class="card radius-10 border-start border-4 border-success"><div class="card-body"><p class="mb-0 text-secondary">Active Primary Links</p><h4 class="my-1 text-success">{{ $metrics['active_attributions'] }}</h4></div></div></div>
            <div class="col"><div class="card radius-10 border-start border-4 border-warning"><div class="card-body"><p class="mb-0 text-secondary">Pending Approval</p><h4 class="my-1 text-warning">{{ $metrics['pending_approvals'] }}</h4></div></div></div>
            <div class="col"><div class="card radius-10 border-start border-4 border-info"><div class="card-body"><p class="mb-0 text-secondary">Superseded History</p><h4 class="my-1 text-info">{{ $metrics['superseded_reassigned'] }}</h4></div></div></div>
            <div class="col"><div class="card radius-10 border-start border-4 border-danger"><div class="card-body"><p class="mb-0 text-secondary">Rejected</p><h4 class="my-1 text-danger">{{ $metrics['rejected_attributions'] }}</h4></div></div></div>
        </div>

        <!-- Create / Link Attribution Card -->
        <div class="card mb-3">
            <div class="card-header bg-transparent">
                <h6 class="mb-0 fw-bold"><i class="fa-solid fa-link me-1 text-primary"></i> Attribute Field Agent to Buyer / Dealer</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.attributions.store') }}" class="row g-3">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Buyer / Dealer Account <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select" required id="attributionCustomerSelect">
                            <option value="">-- Select Buyer / Dealer --</option>
                            @foreach($customers as $cust)
                                @php $existingAttr = $cust->activeAttribution; @endphp
                                <option value="{{ $cust->id }}" data-has-attribution="{{ $existingAttr ? '1' : '0' }}" {{ old('customer_id') == $cust->id ? 'selected' : '' }}>
                                    #{{ $cust->id }} - {{ $cust->name }} ({{ $cust->mobile }})
                                    @if($existingAttr) [CONFLICT WARNING: Linked to {{ $existingAttr->agent?->agent_code }}] @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="text-danger small mt-1 d-none" id="duplicateWarningBanner">
                            Warning: This buyer already has an active/pending attribution. Submitting will trigger duplicate conflict protection; use reassignment on the existing record if authorized.
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Active Field Sales Agent <span class="text-danger">*</span></label>
                        <select name="field_agent_id" class="form-select" required>
                            <option value="">-- Select Active Agent Only --</option>
                            @foreach($activeAgents as $ag)
                                <option value="{{ $ag->id }}" {{ old('field_agent_id') == $ag->id ? 'selected' : '' }}>
                                    {{ $ag->agent_code }} - {{ $ag->name }} ({{ $ag->territory }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Attribution Source</label>
                        <select name="attribution_source" class="form-select">
                            <option value="onboarding">Onboarding Registration</option>
                            <option value="activation">Account Activation</option>
                            <option value="admin_linking" selected>Admin Linking Process</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Evidence / Reference ID</label>
                        <input type="text" name="evidence_reference" class="form-control" value="{{ old('evidence_reference') }}" placeholder="e.g. Visit Log #VL-104 / Onboarding Form">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Remarks</label>
                        <input type="text" name="remarks" class="form-control" value="{{ old('remarks') }}" placeholder="Attribution note visible in history">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Confidential Admin Notes</label>
                        <input type="text" name="admin_notes" class="form-control" value="{{ old('admin_notes') }}" placeholder="Internal ops note (hidden from agents)">
                    </div>

                    <div class="col-md-12 d-flex align-items-center justify-content-between">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="require_approval" value="1" id="reqApprovalCheck">
                            <label class="form-check-label" for="reqApprovalCheck">Submit as Pending Approval (instead of immediate activation)</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Create Primary Attribution</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.attributions.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm" value="{{ $filters['search'] ?? '' }}" placeholder="Ref, Buyer, Agent Code">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All States</option>
                            @foreach(['pending', 'active', 'rejected', 'superseded', 'revoked'] as $st)
                                <option value="{{ $st }}" {{ ($filters['status'] ?? '') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Field Agent</label>
                        <select name="field_agent_id" class="form-select form-select-sm">
                            <option value="">All Agents</option>
                            @foreach($allAgents as $ag)
                                <option value="{{ $ag->id }}" {{ ($filters['field_agent_id'] ?? '') == $ag->id ? 'selected' : '' }}>{{ $ag->agent_code }} - {{ $ag->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                        <a href="{{ route('admin.attributions.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Attributions Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ref</th>
                                <th>Buyer / Dealer</th>
                                <th>Field Agent</th>
                                <th>Source & Ref</th>
                                <th>Status / Approval</th>
                                <th>Attributed By</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attributions as $attr)
                            <tr>
                                <td class="fw-bold text-primary">{{ $attr->attribution_ref }}</td>
                                <td>
                                    <div class="fw-bold">{{ $attr->customer?->name }}</div>
                                    <div class="small text-muted">{{ $attr->customer?->mobile }} &bull; {{ $attr->customer?->location ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $attr->agent?->name }}</div>
                                    <div class="small text-muted">{{ $attr->agent?->agent_code }} ({{ $attr->agent?->territory }})</div>
                                </td>
                                <td>
                                    <div>{{ ucfirst(str_replace('_', ' ', $attr->attribution_source)) }}</div>
                                    <div class="small text-muted">{{ $attr->evidence_reference ?: 'No Ref' }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $attr->status === 'active' ? 'bg-success' : ($attr->status === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                        {{ ucfirst($attr->status) }}
                                    </span>
                                    <div class="small text-muted">{{ ucfirst(str_replace('_', ' ', $attr->approval_status)) }}</div>
                                </td>
                                <td class="small">
                                    {{ $attr->attributedBy?->name }}<br>
                                    <span class="text-muted">{{ $attr->attributed_at?->format('d M Y') }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.attributions.show', $attr->id) }}" class="btn btn-sm btn-outline-primary">Review & History</a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">No attribution records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($attributions->hasPages())
                <div class="card-footer bg-white">{{ $attributions->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>
</div>
<script>
    document.getElementById('attributionCustomerSelect')?.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const banner = document.getElementById('duplicateWarningBanner');
        if (banner) {
            if (selected && selected.getAttribute('data-has-attribution') === '1') {
                banner.classList.remove('d-none');
            } else {
                banner.classList.add('d-none');
            }
        }
    });
</script>
@endsection

