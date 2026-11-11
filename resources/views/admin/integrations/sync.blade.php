@extends('layout')
@section('title', 'Cross-Platform Integration Sync Monitor')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Integration Governance</div>
            <div class="ps-3"><ol class="breadcrumb mb-0 p-0"><li class="breadcrumb-item active">Cross-Platform Sync Outbox & Channel Health</li></ol></div>
            <div class="ms-auto">
                <a href="{{ route('admin.integrations.reconciliation') }}" class="btn btn-sm btn-warning">View Reconciliation Diffs ({{ $summary['open_reconciliations'] }})</a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger py-2 small">{{ implode(' | ', $errors->all()) }}</div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-md-3"><div class="card mb-0 border-start border-4 border-primary"><div class="card-body py-2"><small class="text-muted">Authoritative Source</small><h5 class="mb-0 text-primary">Laravel Backend</h5></div></div></div>
            <div class="col-md-3"><div class="card mb-0 border-start border-4 border-success"><div class="card-body py-2"><small class="text-muted">Processed Sync Events</small><h4 class="mb-0 text-success">{{ $summary['processed_events'] }}</h4></div></div></div>
            <div class="col-md-3"><div class="card mb-0 border-start border-4 border-danger"><div class="card-body py-2"><small class="text-muted">Failed / Dead-Letter</small><h4 class="mb-0 text-danger">{{ $summary['failed_or_dead_letter'] }}</h4></div></div></div>
            <div class="col-md-3"><div class="card mb-0 border-start border-4 border-warning"><div class="card-body py-2"><small class="text-muted">Rejected Conflicts</small><h4 class="mb-0 text-warning">{{ $summary['rejected_conflicts'] }}</h4></div></div></div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Channel Synchronization Health</h6>
                <span class="badge {{ $summary['crm_sync_enabled'] ? 'bg-success' : 'bg-secondary' }}">CRM Auto-Push: {{ $summary['crm_sync_enabled'] ? 'Enabled' : 'Disabled (Awaiting Approval)' }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light"><tr><th>Channel</th><th>Last Successful Sync</th><th>Failed / Dead-Letter</th></tr></thead>
                    <tbody>
                        @foreach($summary['channel_health'] as $ch)
                        <tr>
                            <td class="fw-bold">{{ $ch['channel'] }}</td>
                            <td class="small">{{ $ch['last_success'] ?? 'No events yet' }}</td>
                            <td><span class="badge {{ $ch['failed_count'] > 0 ? 'bg-danger' : 'bg-success' }}">{{ $ch['failed_count'] }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold">Integration Sync Event Log ({{ $events->total() }})</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Event Ref</th><th>Direction &amp; Channel</th><th>Entity / Canonical Ref</th><th>Status</th><th>Attempts &amp; Error (Redacted)</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        @forelse($events as $ev)
                        <tr>
                            <td class="small fw-bold">{{ $ev->event_ref }}</td>
                            <td class="small">{{ $ev->direction }} &rarr; <strong>{{ $ev->target_channel }}</strong></td>
                            <td class="small">{{ $ev->entity_type }}: <code>{{ $ev->canonical_ref }}</code></td>
                            <td><span class="badge {{ $ev->status === 'processed' ? 'bg-success' : ($ev->status === 'failed' || $ev->status === 'dead_letter' ? 'bg-danger' : 'bg-warning text-dark') }}">{{ $ev->status }}</span></td>
                            <td class="small">{{ $ev->attempts }}/{{ $ev->max_attempts }} — {{ \Illuminate\Support\Str::limit($ev->last_error, 80) }}</td>
                            <td>
                                @if(in_array($ev->status, ['failed', 'dead_letter'], true))
                                <form method="POST" action="{{ route('admin.integrations.events.retry', $ev->id) }}" class="d-inline">@csrf
                                    <button class="btn btn-sm btn-outline-primary">Retry</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">No integration sync events recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($events->hasPages())
            <div class="card-footer bg-transparent">{{ $events->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

