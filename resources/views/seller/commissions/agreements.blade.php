@extends('layout')
@section('title', 'My Commission Agreements')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Seller Portal</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('seller.catalogue.analytics') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Commission Agreements</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Seller Specific Contract Terms -->
        <div class="card radius-10 mb-4 border-start border-0 border-3 border-primary">
            <div class="card-header bg-transparent py-3">
                <h6 class="mb-0 font-weight-bold text-primary">1. Organization-Specific Commercial Agreement</h6>
            </div>
            <div class="card-body">
                @if($sellerRules->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Agreement Ref</th>
                                    <th>Agreement Name</th>
                                    <th>Commission Rate</th>
                                    <th>Effective Period</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sellerRules as $sr)
                                    <tr>
                                        <td class="fw-bold">{{ $sr->agreement_ref ?: $sr->rule_code }}</td>
                                        <td>{{ $sr->name }}</td>
                                        <td class="fw-bold text-success fs-6">{{ number_format($sr->commission_percentage, 2) }}%</td>
                                        <td>{{ $sr->effective_from->format('d M Y') }} &rarr; {{ $sr->effective_to ? $sr->effective_to->format('d M Y') : 'Active' }}</td>
                                        <td><span class="badge bg-success">{{ ucfirst($sr->status) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-3 bg-light rounded text-muted">
                        No specialized seller-level contract override active. Standard category and SKU rates apply.
                    </div>
                @endif
            </div>
        </div>

        <!-- SKU Specific Agreements -->
        <div class="card radius-10 mb-4 border-start border-0 border-3 border-info">
            <div class="card-header bg-transparent py-3">
                <h6 class="mb-0 font-weight-bold text-info">2. SKU-Specific Contract Rates (Priority 1)</h6>
            </div>
            <div class="card-body">
                @if($skuRules->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>SKU Target</th>
                                    <th>Rule Name</th>
                                    <th>Commission %</th>
                                    <th>Fixed Fee</th>
                                    <th>Effective Period</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($skuRules as $skr)
                                    <tr>
                                        <td class="fw-bold">{{ $skr->scope_label }}</td>
                                        <td>{{ $skr->name }}</td>
                                        <td class="fw-bold text-success">{{ number_format($skr->commission_percentage, 2) }}%</td>
                                        <td>{{ $skr->fixed_fee > 0 ? '₹' . number_format($skr->fixed_fee, 2) : '—' }}</td>
                                        <td>{{ $skr->effective_from->format('d M Y') }} &rarr; {{ $skr->effective_to ? $skr->effective_to->format('d M Y') : 'Active' }}</td>
                                        <td><span class="badge bg-success">{{ ucfirst($skr->status) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-3 bg-light rounded text-muted">
                        No SKU-specific commercial overrides configured. Category-level rules apply.
                    </div>
                @endif
            </div>
        </div>

        <!-- Category Level Rates -->
        <div class="card radius-10 border-start border-0 border-3 border-dark">
            <div class="card-header bg-transparent py-3">
                <h6 class="mb-0 font-weight-bold">3. Applicable Category Commission Rates</h6>
            </div>
            <div class="card-body">
                @if($categoryRules->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th>Standard Commission Rate</th>
                                    <th>Calculation Basis</th>
                                    <th>GST on Platform Fee</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categoryRules as $cr)
                                    <tr>
                                        <td class="fw-bold">{{ $cr->scope_label }}</td>
                                        <td class="fw-bold text-success">{{ number_format($cr->commission_percentage, 2) }}%</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $cr->calculation_basis)) }}</td>
                                        <td>{{ number_format($cr->gst_rate, 2) }}% (GST Invoice Issued)</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-3 bg-light rounded text-muted">
                        Platform default commission rate: <strong>{{ $globalRule ? number_format($globalRule->commission_percentage, 2) : '10.00' }}%</strong>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
