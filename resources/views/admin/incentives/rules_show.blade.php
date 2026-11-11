@extends('layout')
@section('title', 'Manage Incentive Rule')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Field Agent Incentives</div>
            <div class="ps-3"><ol class="breadcrumb mb-0 p-0"><li class="breadcrumb-item"><a href="{{ route('admin.incentives.rules.index') }}">Rules</a></li><li class="breadcrumb-item active">{{ $rule->rule_code }} v{{ $rule->version }}</li></ol></div>
        </div>

        @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger py-2"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

        <div class="card mb-3">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">{{ $rule->name }} ({{ $rule->rule_code }} v{{ $rule->version }})</h6>
                <span class="badge bg-primary">{{ strtoupper($rule->status) }}</span>
            </div>
            <div class="card-body small row g-2">
                <div class="col-md-4"><strong>Method:</strong> {{ $rule->calculation_method }}</div>
                <div class="col-md-4"><strong>Base:</strong> {{ $rule->calculation_base }}</div>
                <div class="col-md-4"><strong>Funding Source:</strong> {{ $rule->funding_source }} (Zero seller deduction)</div>
                <div class="col-md-4"><strong>Rate / Amount:</strong> {{ $rule->rate_percentage ? $rule->rate_percentage.'%' : '₹'.$rule->fixed_amount }}</div>
                <div class="col-md-4"><strong>Cap / Rounding:</strong> {{ $rule->max_cap_amount ? '₹'.$rule->max_cap_amount : 'No Cap' }} / {{ $rule->rounding_rule }}</div>
                <div class="col-md-4"><strong>Tax Treatment:</strong> {{ $rule->tax_treatment_code }} {{ $rule->tds_percentage ? "({$rule->tds_percentage}%)" : '' }}</div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card h-100"><div class="card-body">
                    <h6 class="fw-bold small">1. Business Approval</h6>
                    @if($rule->hasBusinessApproval())
                        <div class="text-success small">Approved Ref: {{ $rule->business_approval_ref }}</div>
                    @else
                        <form method="POST" action="{{ route('admin.incentives.rules.approve-business', $rule->id) }}">@csrf
                            <input type="text" name="business_approval_ref" class="form-control form-control-sm mb-2" placeholder="BUS-APP-2026-01" required>
                            <button class="btn btn-sm btn-outline-primary w-100">Record Business Approval</button>
                        </form>
                    @endif
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card h-100"><div class="card-body">
                    <h6 class="fw-bold small">2. Finance Approval</h6>
                    @if($rule->hasFinanceApproval())
                        <div class="text-success small">Approved Ref: {{ $rule->finance_approval_ref }}</div>
                    @else
                        <form method="POST" action="{{ route('admin.incentives.rules.approve-finance', $rule->id) }}">@csrf
                            <input type="text" name="finance_approval_ref" class="form-control form-control-sm mb-2" placeholder="FIN-APP-2026-01" required>
                            <button class="btn btn-sm btn-outline-primary w-100">Record Finance Approval</button>
                        </form>
                    @endif
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card h-100"><div class="card-body">
                    <h6 class="fw-bold small">3. Activation Control</h6>
                    @if(!$fundingApproved)
                        <div class="alert alert-warning py-1 small mb-2">Locked until written funding policy approval (`funding_policy_approved`).</div>
                    @endif
                    @if($rule->status !== 'active')
                        <form method="POST" action="{{ route('admin.incentives.rules.activate', $rule->id) }}">@csrf
                            <button class="btn btn-sm btn-success w-100">Activate Rule</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.incentives.rules.deactivate', $rule->id) }}">@csrf
                            <input type="text" name="reason" class="form-control form-control-sm mb-2" placeholder="Deactivation reason" required>
                            <button class="btn btn-sm btn-danger w-100">Deactivate</button>
                        </form>
                    @endif
                </div></div>
            </div>
        </div>
    </div>
</div>
@endsection

