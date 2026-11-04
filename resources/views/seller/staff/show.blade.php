@extends('layout')
@section('title', 'Manage Staff Permissions')
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
                        <li class="breadcrumb-item active" aria-current="page">{{ $staff->user->name ?? 'Staff Details' }}</li>
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

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Staff Summary Card -->
            <div class="col-xl-4">
                <div class="card mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 text-uppercase"><i class="fa-solid fa-id-badge me-2"></i>Staff Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-md bg-light-primary text-primary rounded-circle p-3 me-3 text-center" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-user-tie fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $staff->user->name ?? 'Staff User' }}</h6>
                                <small class="text-muted">{{ $staff->user->email ?? '—' }}</small>
                            </div>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                Current Role:
                                <span class="badge bg-primary">{{ $staff->role?->label ?? 'None' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                Account Status:
                                @if($staff->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                Member Since:
                                <span>{{ $staff->created_at?->format('d M Y') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                Audit Activity:
                                <a href="{{ route('seller.staff.activity', $staff->id) }}" class="btn btn-sm btn-outline-info">
                                    <i class="fa-solid fa-clock-rotate-left me-1"></i> View Logs
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Role Assignment Box -->
                @if(auth()->user()->isSellerOwner() || auth()->user()->hasSellerPermission('staff.assign_role'))
                <div class="card">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 text-uppercase"><i class="fa-solid fa-user-shield me-2"></i>Change Assigned Role</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('seller.staff.assign-role', $staff->id) }}">
                            @csrf
                            @method('PATCH')
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Select Role Template</label>
                                <select name="role" class="form-select" required>
                                    @foreach($roles->where('name', '!=', 'seller_owner') as $r)
                                        <option value="{{ $r->name }}" {{ ($staff->role?->name === $r->name) ? 'selected' : '' }}>
                                            {{ $r->label }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1">Assigning a new template reconfigures the base permissions.</small>
                            </div>
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="fa-solid fa-arrows-rotate me-1"></i> Update Role
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>

            <!-- Granular Permissions Matrix -->
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 text-uppercase"><i class="fa-solid fa-list-check me-2"></i>Granular Permissions Matrix</h6>
                            <small class="text-muted">Effective permissions combine the role template defaults and custom overrides.</small>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(auth()->user()->isSellerOwner() || auth()->user()->hasSellerPermission('staff.manage_permissions'))
                        <form method="POST" action="{{ route('seller.staff.update-permissions', $staff->id) }}">
                            @csrf
                            @method('PATCH')

                            @foreach($allPermissions as $module => $perms)
                                <div class="mb-4 p-3 bg-light rounded">
                                    <h6 class="text-uppercase fw-bold text-primary mb-3">
                                        <i class="fa-solid fa-folder-open me-2"></i>{{ ucfirst($module) }} Module
                                    </h6>
                                    <div class="row row-cols-1 row-cols-md-2 g-2">
                                        @foreach($perms as $perm)
                                            @php
                                                $inputName = 'perm_' . str_replace('.', '_', $perm->name);
                                                $isGranted = in_array($perm->name, $effectivePermissions);
                                            @endphp
                                            <div class="col">
                                                <div class="form-check form-switch p-2 bg-white rounded border">
                                                    <input class="form-check-input ms-0 me-2" type="checkbox"
                                                           role="switch"
                                                           value="1"
                                                           name="{{ $inputName }}"
                                                           id="{{ $inputName }}"
                                                           {{ $isGranted ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="{{ $inputName }}">
                                                        <strong>{{ $perm->label }}</strong>
                                                        <span class="d-block text-muted small"><code>{{ $perm->name }}</code></span>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Custom Permissions
                                </button>
                            </div>
                        </form>
                        @else
                            <div class="alert alert-info">
                                You have view-only access. Only seller owners or authorized administrators can adjust granular permissions.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

