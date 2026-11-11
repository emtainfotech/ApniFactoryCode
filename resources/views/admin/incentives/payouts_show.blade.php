@extends('layout')
@section('title', 'Payout Batch Details & Reconciliation')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Finance Control Center</div>
            <div class="ps-3"><ol class="breadcrumb mb-0 p-0"><li class="breadcrumb-item"><a href="{{ route('admin.incentives.payouts.index') }}">Agent Payouts</a></li><li class="breadcrumb-item active">{{ $payout->payout_ref }}</li></ol></div>
        </div>

        @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger py-2"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

        <div class="card mb-3">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Payout Batch {{ $payout->payout_ref }} — {{ $payout->agent?->agent_code }}</h6>
                <span class="badge bg-primary">{{ strtoupper(str_replace('_', ' ', $payout->status)) }}</span>
            </div>
            <div class="card-body small row g-2">
                <div class="col-md-3"><strong>Gross Incentives:</strong> ₹{{ number_format($payout->gross_incentive_amount, 2) }}</div>
                <div class="col-md-3"><strong>Adjustments:</strong> ₹{{ number_format($payout->approved_adjustments_amount, 2) }}</div>
                <div class="col-md-3"><strong>Withholding/Tax:</strong> ₹{{ number_format($payout->withholding_tax_amount, 2) }}</div>
                <div class="col-md-3"><strong>Net Payable:</strong> <span class="text-success fw-bold">₹{{ number_format($payout->net_payable_amount, 2) }}</span></div>
                <div class="col-md-4"><strong>Expense Account:</strong> {{ $payout->funding_expense_account }} (Zero Seller Deduction)</div>
                <div class="col-md-4"><strong>External Ref:</strong> {{ $payout->external_transaction_ref ?? 'None Yet' }}</div>
                <div class="col-md-4"><strong>Reconciliation:</strong> {{ $payout->reconciliation_status }}</div>
                @php $dest = $payout->maskedDestinationSummary(); @endphp
                <div class="col-md-12"><strong>Masked Destination Snapshot:</strong> {{ ($dest['configured'] ?? false) ? ($dest['bank_name'] ?? 'Bank') . ' ' . ($dest['account_masked'] ?? $dest['upi_masked'] ?? '') : 'Unconfigured' }}</div>
            </div>
        </div>

        <!-- Controlled Finance State Transitions -->
        <div class="card mb-3">
            <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold">Authoritative Payout Execution &amp; Reconciliation Controls</h6></div>
            <div class="card-body row g-3">
                @if(!$executionEnabled)
                    <div class="col-12"><div class="alert alert-warning py-2 small mb-0">Execution disabled by `payout_execution_enabled = false`. Authorizing, initiating, or marking paid is blocked pending written approvals.</div></div>
                @endif
                <div class="col-md-4">
                    <form method="POST" action="{{ route('admin.incentives.payouts.authorize', $payout->id) }}">@csrf
                        <button class="btn btn-sm btn-outline-primary w-100">1. Authorize Payout</button>
                    </form>
                </div>
                <div class="col-md-4">
                    <form method="POST" action="{{ route('admin.incentives.payouts.initiate', $payout->id) }}" class="d-flex gap-1">@csrf
                        <select name="payment_method" class="form-select form-select-sm"><option value="manual_bank_transfer">Manual Bank</option><option value="manual_upi">Manual UPI</option></select>
                        <button class="btn btn-sm btn-outline-warning">2. Record Initiation</button>
                    </form>
                </div>
                <div class="col-md-4">
                    <form method="POST" action="{{ route('admin.incentives.payouts.fail', $payout->id) }}" class="d-flex gap-1">@csrf
                        <input type="text" name="failure_reason" class="form-control form-control-sm" placeholder="Failure reason" required>
                        <button class="btn btn-sm btn-outline-danger">Mark Failed</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold">Included Ledger Entries ({{ $payout->items->count() }})</h6></div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Ledger Ref</th><th>Parent Order</th><th>Sequence</th><th>Gross</th><th>Adjustments</th><th>Item Net Payable</th></tr></thead>
                    <tbody>
                        @foreach($payout->items as $item)
                        <tr>
                            <td class="fw-bold">{{ $item->ledgerEntry?->ledger_ref }}</td>
                            <td>{{ $item->ledgerEntry?->parentOrder?->orderno }}</td>
                            <td>{{ $item->eligible_order_sequence }}/3</td>
                            <td>₹{{ number_format($item->ledger_gross_amount, 2) }}</td>
                            <td>₹{{ number_format($item->adjustment_amount, 2) }}</td>
                            <td class="fw-bold">₹{{ number_format($item->item_net_payable_amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

