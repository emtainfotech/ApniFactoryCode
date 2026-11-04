@extends('layout')
@section('title', 'Staff Management')
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
                        <li class="breadcrumb-item active" aria-current="page">Staff Management</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                @if(auth()->user()->isSellerOwner() || auth()->user()->hasSellerPermission('staff.invite'))
                <a href="{{ route('seller.staff.create') }}" class="btn btn-primary px-3">
                    <i class="fa-solid fa-user-plus me-1"></i> Invite Staff Member
                </a>
                @endif
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

        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-uppercase">Staff Members &mdash; {{ $company->name }}</h6>
                <span class="badge bg-secondary">{{ count($staff) }} Total Members</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Staff Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Added Date</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staff as $member)
                            <tr>
                                <td>{{ $member->id }}</td>
                                <td><strong>{{ $member->user->name ?? '—' }}</strong></td>
                                <td>{{ $member->user->email ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-info text-dark">{{ $member->role?->label ?? '—' }}</span>
                                </td>
                                <td>
                                    @if($member->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $member->created_at?->format('d M Y') }}</td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('seller.staff.show', $member->id) }}" class="btn btn-sm btn-outline-primary" title="Manage Permissions">
                                        <i class="fa-solid fa-sliders"></i> Manage
                                    </a>
                                    <a href="{{ route('seller.staff.activity', $member->id) }}" class="btn btn-sm btn-outline-secondary" title="Audit Activity">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                    </a>
                                    @if(auth()->user()->isSellerOwner() || auth()->user()->hasSellerPermission('staff.deactivate'))
                                        @if($member->is_active)
                                        <form method="POST" action="{{ route('seller.staff.deactivate', $member->id) }}" class="d-inline" onsubmit="return confirm('Deactivate this staff member?');">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-danger" title="Deactivate">
                                                <i class="fa-solid fa-ban"></i>
                                            </button>
                                        </form>
                                        @else
                                        <form method="POST" action="{{ route('seller.staff.activate', $member->id) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-success" title="Activate">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No staff members invited yet.
                                    @if(auth()->user()->isSellerOwner() || auth()->user()->hasSellerPermission('staff.invite'))
                                        <a href="{{ route('seller.staff.create') }}">Invite your first staff member</a>.
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

