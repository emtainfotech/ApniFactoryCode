@extends('layout')
@section('title', 'Edit Field Agent - ' . $agent->agent_code)
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Field Operations</div>
            <div class="ps-3">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.agents.index') }}">Agents</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.agents.show', $agent->id) }}">{{ $agent->agent_code }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="card">
            <div class="card-header bg-transparent">
                <h6 class="mb-0 fw-bold">Edit Field Sales Agent: {{ $agent->agent_code }}</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.agents.update', $agent->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Unique Agent ID <span class="text-danger">*</span></label>
                            <input type="text" name="agent_code" class="form-control" value="{{ old('agent_code', $agent->agent_code) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $agent->name) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Primary Mobile <span class="text-danger">*</span></label>
                            <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $agent->mobile) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $agent->email) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Territory / Area <span class="text-danger">*</span></label>
                            <input type="text" name="territory" class="form-control" value="{{ old('territory', $agent->territory) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', $agent->joining_date?->format('Y-m-d')) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">City</label>
                            <input type="text" name="territory_city" class="form-control" value="{{ old('territory_city', $agent->territory_city) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">State</label>
                            <input type="text" name="territory_state" class="form-control" value="{{ old('territory_state', $agent->territory_state) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Supervisor</label>
                            <select name="supervisor_user_id" class="form-select">
                                <option value="">None / Direct Operations</option>
                                @foreach($supervisors as $sup)
                                    <option value="{{ $sup->id }}" {{ old('supervisor_user_id', $agent->supervisor_user_id) == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">KYC Status</label>
                            <select name="kyc_status" class="form-select">
                                @foreach(config('marketplace_agents.kyc_statuses') as $key => $label)
                                    <option value="{{ $key }}" {{ old('kyc_status', $agent->kyc_status) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Contract Status</label>
                            <select name="contract_status" class="form-select">
                                @foreach(config('marketplace_agents.contract_statuses') as $key => $label)
                                    <option value="{{ $key }}" {{ old('contract_status', $agent->contract_status) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">KYC Reference (Restricted)</label>
                            <input type="text" name="kyc_reference" class="form-control" value="{{ old('kyc_reference', $agent->kyc_reference) }}">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">Confidential Admin Notes</label>
                            <textarea name="notes" rows="2" class="form-control">{{ old('notes', $agent->notes) }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Update Audit Reason</label>
                            <input type="text" name="reason" class="form-control" placeholder="Document reason for profile modification">
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="{{ route('admin.agents.show', $agent->id) }}" class="btn btn-outline-secondary">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

