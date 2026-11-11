@extends('layout')
@section('title', 'Register Field Sales Agent')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Field Operations</div>
            <div class="ps-3">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.agents.index') }}">Agents</a></li>
                    <li class="breadcrumb-item active">Register Agent</li>
                </ol>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-header bg-transparent">
                <h6 class="mb-0 fw-bold">Field Sales Agent Master Profile</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.agents.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Unique Agent ID (Optional auto-generate)</label>
                            <input type="text" name="agent_code" class="form-control" value="{{ old('agent_code') }}" placeholder="e.g. FSA-2026-0001">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                @foreach(config('marketplace_agents.agent_statuses') as $key => $label)
                                    <option value="{{ $key }}" {{ old('status', 'active') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Primary Mobile <span class="text-danger">*</span></label>
                            <input type="text" name="mobile" class="form-control" value="{{ old('mobile') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Alternate Contact</label>
                            <input type="text" name="alternate_mobile" class="form-control" value="{{ old('alternate_mobile') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Territory / Area <span class="text-danger">*</span></label>
                            <input type="text" name="territory" class="form-control" value="{{ old('territory') }}" required placeholder="e.g. Jaipur Industrial South">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">City</label>
                            <input type="text" name="territory_city" class="form-control" value="{{ old('territory_city') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">State</label>
                            <input type="text" name="territory_state" class="form-control" value="{{ old('territory_state') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Supervisor (User Account)</label>
                            <select name="supervisor_user_id" class="form-select">
                                <option value="">None / Direct Operations</option>
                                @foreach($supervisors as $sup)
                                    <option value="{{ $sup->id }}" {{ old('supervisor_user_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }} ({{ $sup->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Supervisor Name (External Ref)</label>
                            <input type="text" name="supervisor_name" class="form-control" value="{{ old('supervisor_name') }}">
                        </div>

                        <div class="col-md-12"><hr><h6 class="fw-bold text-secondary">Policy Compliance & Restricted Metadata</h6></div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">KYC Status</label>
                            <select name="kyc_status" class="form-select">
                                @foreach(config('marketplace_agents.kyc_statuses') as $key => $label)
                                    <option value="{{ $key }}" {{ old('kyc_status', 'not_submitted') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Contract Status</label>
                            <select name="contract_status" class="form-select">
                                @foreach(config('marketplace_agents.contract_statuses') as $key => $label)
                                    <option value="{{ $key }}" {{ old('contract_status', 'not_signed') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">KYC / Contract Reference (Restricted)</label>
                            <input type="text" name="kyc_reference" class="form-control" value="{{ old('kyc_reference') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Payout Account Holder (Encrypted)</label>
                            <input type="text" name="account_holder_name" class="form-control" value="{{ old('account_holder_name') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Account Number</label>
                            <input type="text" name="account_number" class="form-control" value="{{ old('account_number') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">IFSC Code / UPI ID</label>
                            <input type="text" name="ifsc_code" class="form-control mb-1" placeholder="IFSC" value="{{ old('ifsc_code') }}">
                            <input type="text" name="upi_id" class="form-control" placeholder="UPI ID (Optional)" value="{{ old('upi_id') }}">
                        </div>

                        <div class="col-md-12">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="create_user_account" value="1" id="createUserCheck" {{ old('create_user_account') ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="createUserCheck">
                                    Provision Portal Login Account in Unified Auth System (requires email)
                                </label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Initial Login Password (if creating login)</label>
                            <input type="password" name="password" class="form-control" placeholder="Min 8 characters">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Confidential Admin Notes</label>
                            <textarea name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Field Agent</button>
                        <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

