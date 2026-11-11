@extends('layout')
@section('title', 'Create Draft Incentive Rule')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Field Agent Incentives</div>
            <div class="ps-3"><ol class="breadcrumb mb-0 p-0"><li class="breadcrumb-item"><a href="{{ route('admin.incentives.rules.index') }}">Rules</a></li><li class="breadcrumb-item active">New Draft Rule</li></ol></div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <div class="card">
            <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold">Configure Agent Incentive Rule (Starts in Inactive Draft State)</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.incentives.rules.store') }}" class="row g-3">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Rule Code</label>
                        <input type="text" name="rule_code" class="form-control" value="{{ old('rule_code') }}" placeholder="INC-RULE-001">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-bold small">Rule Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Funding Source *</label>
                        <input type="text" class="form-control bg-light" value="Apni Factory Platform Funded (Section 12.1)" readonly>
                        <input type="hidden" name="funding_source" value="apnifactory_funded">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Calculation Method *</label>
                        <select name="calculation_method" class="form-select" required>
                            <option value="percentage_of_order_value">Method A: % of Order Value</option>
                            <option value="percentage_of_platform_commission">Method B: % of Platform Commission</option>
                            <option value="fixed_amount">Method C: Fixed Amount per Eligible Order</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Calculation Base *</label>
                        <select name="calculation_base" class="form-select" required>
                            <option value="taxable_order_value">Taxable Order Value (Excl. GST)</option>
                            <option value="gross_order_value">Gross Order Value (Incl. GST)</option>
                            <option value="platform_commission_ex_gst">Platform Commission (Excl. GST)</option>
                            <option value="platform_commission_incl_gst">Platform Commission (Incl. GST)</option>
                            <option value="fixed_per_order">Fixed Amount per Eligible Order</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Rate (%) [Methods A &amp; B]</label>
                        <input type="number" step="0.0001" name="rate_percentage" class="form-control" value="{{ old('rate_percentage') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Fixed Amount (₹) [Method C]</label>
                        <input type="number" step="0.01" name="fixed_amount" class="form-control" value="{{ old('fixed_amount') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Max Cap per Order (₹)</label>
                        <input type="number" step="0.01" name="max_cap_amount" class="form-control" value="{{ old('max_cap_amount') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Rounding Rule *</label>
                        <select name="rounding_rule" class="form-select" required>
                            <option value="half_up_2">Standard 2 Decimals (Half Up)</option>
                            <option value="nearest_rupee">Nearest Whole Rupee</option>
                            <option value="floor_0">Floor to Rupee</option>
                            <option value="ceil_0">Ceil to Rupee</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Tax Treatment *</label>
                        <select name="tax_treatment_code" class="form-select">
                            <option value="pending_tax_decision">Pending Finance Tax Decision</option>
                            <option value="exclusive_no_gst">Exclusive (No GST / No Deduction)</option>
                            <option value="tds_applicable_configurable">Configurable TDS Percentage</option>
                            <option value="gst_inclusive">GST Inclusive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">TDS (%) if applicable</label>
                        <input type="number" step="0.01" name="tds_percentage" class="form-control" value="{{ old('tds_percentage') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Effective From *</label>
                        <input type="datetime-local" name="effective_from" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Effective To (Optional)</label>
                        <input type="datetime-local" name="effective_to" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Agreement / Policy Ref</label>
                        <input type="text" name="agreement_ref" class="form-control" value="{{ old('agreement_ref') }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-bold small">Justification / Governance Reason</label>
                        <input type="text" name="reason" class="form-control" value="{{ old('reason') }}">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Save Draft Rule (Inactive)</button>
                        <a href="{{ route('admin.incentives.rules.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

