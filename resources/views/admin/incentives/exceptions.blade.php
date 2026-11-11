@extends('layout')
@section('title', 'Payout Reconciliation Exceptions')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Finance Control Center</div>
            <div class="ps-3"><ol class="breadcrumb mb-0 p-0"><li class="breadcrumb-item"><a href="{{ route('admin.incentives.payouts.index') }}">Agent Payouts</a></li><li class="breadcrumb-item active">Reconciliation Exceptions</li></ol></div>
        </div>

        @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger py-2"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

        <div class="card">
            <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold">Reconciliation &amp; Exception Queue ({{ $exceptions->total() }})</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Exception Ref</th><th>Type</th><th>Payout / Ledger</th><th>Expected vs Reported</th><th>Description</th><th>Status</th><th class="text-end">Resolution</th></tr>
                    </thead>
                    <tbody>
                        @forelse($exceptions as $ex)
                        <tr>
                            <td class="fw-bold text-danger">{{ $ex->exception_ref }}</td>
                            <td class="small">{{ ucwords(str_replace('_', ' ', $ex->exception_type)) }}</td>
                            <td class="small">{{ $ex->payout?->payout_ref ?? '-' }}<br>{{ $ex->ledgerEntry?->ledger_ref ?? '-' }}</td>
                            <td class="small">Exp: ₹{{ number_format((float)$ex->expected_amount, 2) }}<br>Rep: ₹{{ number_format((float)$ex->reported_amount, 2) }}</td>
                            <td class="small">{{ $ex->description }}</td>
                            <td><span class="badge {{ $ex->status === 'resolved' ? 'bg-success' : 'bg-warning text-dark' }}">{{ strtoupper($ex->status) }}</span></td>
                            <td class="text-end">
                                @if(in_array($ex->status, ['open', 'under_review'], true))
                                <form method="POST" action="{{ route('admin.incentives.exceptions.resolve', $ex->id) }}" class="d-flex gap-1 justify-content-end">@csrf
                                    <input type="hidden" name="status" value="resolved">
                                    <input type="text" name="resolution_notes" class="form-control form-control-sm" placeholder="Resolution note" required>
                                    <button class="btn btn-sm btn-success">Resolve</button>
                                </form>
                                @else
                                    <span class="small text-muted">{{ $ex->resolution_notes }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">No reconciliation exceptions logged.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

