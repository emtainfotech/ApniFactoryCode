@extends('layout')
@section('title', 'Invite Staff Member')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Seller Portal</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ url('seller/dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('seller.staff.index') }}">Staff Management</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Invite Staff Member</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="{{ route('seller.staff.index') }}" class="btn btn-outline-secondary px-3">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Staff List
                </a>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="row">
            <div class="col-xl-7 mx-auto">
                <div class="card border-top border-0 border-4 border-primary">
                    <div class="card-body p-4">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="fa-solid fa-user-plus me-2 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Invite Seller Staff Member</h5>
                        </div>
                        <hr>

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('seller.staff.store') }}" class="row g-3">
                            @csrf
                            <div class="col-12">
                                <label class="form-label font-weight-bold">Staff Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. John Doe" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label font-weight-bold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="staff@example.com" required>
                                <small class="text-muted">If an account already exists with this email, they will be linked to your organization.</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label font-weight-bold">Assign Initial Role <span class="text-danger">*</span></label>
                                <select name="role" class="form-select" required>
                                    <option value="">-- Choose Role Template --</option>
                                    @foreach($roles->where('name', '!=', 'seller_owner') as $role)
                                        <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                                            {{ $role->label }} &mdash; {{ $role->description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label font-weight-bold">Initial Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" required minlength="8" placeholder="At least 8 characters">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label font-weight-bold">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control" required minlength="8" placeholder="Repeat password">
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fa-solid fa-paper-plane me-1"></i> Send Invite & Create Staff Member
                                </button>
                                <a href="{{ route('seller.staff.index') }}" class="btn btn-light px-3 ms-2">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

