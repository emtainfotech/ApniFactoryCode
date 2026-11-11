@extends('layout')
@section('title', 'Agent Incentive Rule Engine')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Field Agent Incentives</div>
            <div class="ps-3"><ol class="breadcrumb mb-0 p-0"><li class="breadcrumb-item active">Versioned Incentive Rules</li></ol></div>
            <div class="ms-auto">
                <a href="{{ route('admin.incentives.rules.create') }}" class="btn btn-sm btn-primary">+ New Draft Rule</a>
            </div>
        </div>

        @if(!$fundingApproved)
        <div class="alert alert-warning py-2 small">
            <strong>Written Commercial Sign-Off Pending:</strong> You may create and pre-approve draft rules or run previews, but live activation remains <strong>BLOCKED</strong> until the Section 12 Apni Factory-funded vs seller-funded contradiction is resolved in writing (`funding_policy_approved`).
        </div>
        @endif

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Rule Code &amp; Ver</th>
                                <th>Name</th>
                                <th>Method &amp; Base</th>
                                <th>Rate / Fixed / Cap</th>
                                <th>Approvals</th>
                                <th>Effective Window</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rules as $rule)
                            <tr>
                                <td class="fw-bold">{{ $rule->rule_code }} <span class="badge bg-dark">v{{ $rule->version }}</span></td>
                                <td>{{ $rule->name }}<br><small class="text-success">Apni Factory Funded Only</small></td>
                                <td class="small">{{ str_replace('_', ' ', $rule->calculation_method) }}<br><span class="text-muted">{{ str_replace('_', ' ', $rule->calculation_base) }}</span></td>
                                <td class="small">
                                    {{ $rule->calculation_method === 'fixed_amount' ? '₹'.number_format($rule->fixed_amount, 2) : number_format($rule->rate_percentage, 2).'%' }}
                                    @if($rule->max_cap_amount)<br><span class="text-muted">Cap: ₹{{ number_format($rule->max_cap_amount, 2) }}</span>@endif
                                </td>
                                <td class="small">
                                    Bus: {!! $rule->hasBusinessApproval() ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-light text-dark border">No</span>' !!}
                                    Fin: {!! $rule->hasFinanceApproval() ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-light text-dark border">No</span>' !!}
                                </td>
                                <td class="small">{{ $rule->effective_from?->format('d M Y') }}<br>to {{ $rule->effective_to ? $rule->effective_to->format('d M Y') : 'Open' }}</td>
                                <td><span class="badge {{ $rule->status === 'active' ? 'bg-success' : ($rule->status === 'approved' ? 'bg-info text-dark' : 'bg-secondary') }}">{{ ucwords(str_replace('_', ' ', $rule->status)) }}</span></td>
                                <td class="text-end"><a href="{{ route('admin.incentives.rules.show', $rule->id) }}" class="btn btn-sm btn-outline-primary">Manage</a></td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center py-4 text-muted">No incentive rules configured yet (safe default).</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

