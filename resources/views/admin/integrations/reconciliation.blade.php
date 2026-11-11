@extends('layout')
@section('title', 'Cross-Channel Reconciliation & Conflict Resolution')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Integration Governance</div>
            <div class="ps-3"><ol class="breadcrumb mb-0 p-0"><li class="breadcrumb-item active">Cross-Channel Reconciliation Diffs</li></ol></div>
            <div class="ms-auto d-flex gap-2">
                <form method="POST" action="{{ route('admin.integrations.reconciliation.scan') }}">@csrf
                    <button class="btn btn-sm btn-primary">Run Discrepancy Scan</button>
                </form>
                <a href="{{ route('admin.integrations.sync') }}" class="btn btn-sm btn-outline-secondary">Back to Sync Monitor</a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger py-2 small">{{ implode(' | ', $errors->all()) }}</div>
        @endif

        <div class="card">
            <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold">Reconciliation Discrepancies ({{ $records->total() }})</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Ref</th><th>Entity &amp; Channels</th><th>Mismatch Type</th><th>Severity &amp; Status</th><th>Summary</th><th>Resolve</th></tr>
                    </thead>
                    <tbody>
                        @forelse($records as $rec)
                        <tr>
                            <td class="small fw-bold">{{ $rec->reconciliation_ref }}</td>
                            <td class="small">{{ $rec->entity_type }} <code>{{ $rec->canonical_ref }}</code><br>{{ $rec->source_channel }} vs {{ $rec->target_channel }}</td>
                            <td class="small">{{ ucwords(str_replace('_', ' ', $rec->mismatch_type)) }}</td>
                            <td>
                                <span class="badge bg-{{ $rec->severity === 'critical' || $rec->severity === 'high' ? 'danger' : 'warning text-dark' }}">{{ $rec->severity }}</span>
                                <span class="badge bg-{{ $rec->status === 'reconciled' ? 'success' : 'secondary' }}">{{ $rec->status }}</span>
                            </td>
                            <td class="small">{{ $rec->discrepancy_summary }}</td>
                            <td>
                                @if(in_array($rec->status, ['open', 'investigating'], true))
                                <form method="POST" action="{{ route('admin.integrations.reconciliation.resolve', $rec->id) }}" class="d-flex gap-1">
                                    @csrf
                                    <select name="action" class="form-select form-select-sm" style="max-width:150px;">
                                        <option value="reconcile_to_authoritative">Push Authoritative</option>
                                        <option value="mark_investigating">Investigating</option>
                                        <option value="escalate">Escalate</option>
                                        <option value="ignore">Ignore</option>
                                    </select>
                                    <input type="text" name="notes" class="form-control form-control-sm" placeholder="Audit note" required minlength="3">
                                    <button class="btn btn-sm btn-success">Apply</button>
                                </form>
                                @else
                                <span class="small text-muted">{{ $rec->resolution_notes }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">Zero cross-channel discrepancies found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($records->hasPages())
            <div class="card-footer bg-transparent">{{ $records->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

