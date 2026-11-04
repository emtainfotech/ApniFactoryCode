@extends('layout')
@section('title', 'Staff Activity Log')
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
                        <li class="breadcrumb-item active" aria-current="page">Activity Audit &mdash; {{ $staff->user->name ?? 'Staff' }}</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="{{ route('seller.staff.show', $staff->id) }}" class="btn btn-outline-secondary px-3">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Staff Member
                </a>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-uppercase"><i class="fa-solid fa-history me-2"></i>Security & Permission Audit Logs &mdash; {{ $staff->user->name ?? 'Staff User' }}</h6>
                <span class="badge bg-secondary">{{ $logs->total() }} Log Entries</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date & Time</th>
                                <th>Action Taken</th>
                                <th>Subject / Target</th>
                                <th>Performed By</th>
                                <th>Details / Changes</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->created_at?->format('d M Y, H:i:s') }}</td>
                                <td><span class="badge bg-dark font-monospace">{{ $log->action }}</span></td>
                                <td>{{ $log->subject ?? '—' }}</td>
                                <td>
                                    <strong>{{ $log->actingUser->name ?? 'System' }}</strong>
                                    <span class="d-block text-muted small">{{ $log->actingUser->email ?? '' }}</span>
                                </td>
                                <td>
                                    @if($log->new_value)
                                        <small class="text-muted d-block">New: <code>{{ json_encode($log->new_value) }}</code></small>
                                    @endif
                                    @if($log->old_value)
                                        <small class="text-muted d-block">Old: <code>{{ json_encode($log->old_value) }}</code></small>
                                    @endif
                                    @if(!$log->new_value && !$log->old_value)
                                        <span class="text-muted">&mdash;</span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ $log->ip_address ?? '—' }}</small></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No audit entries recorded for this staff member yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($logs->hasPages())
                <div class="card-footer bg-white">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

