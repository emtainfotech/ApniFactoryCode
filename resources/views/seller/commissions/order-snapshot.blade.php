@extends('layout')
@section('title', 'Order Commission Details: #' . $orderId)
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Seller Portal</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('seller.commissions.agreements') }}">Commissions</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Order #{{ $orderId }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Summary Totals -->
        <div class="row row-cols-1 row-cols-md-4 mb-3">
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-info">
                    <div class="card-body">
                        <p class="mb-0 text-secondary">Taxable Order Base</p>
                        <h4 class="my-1 text-info">₹{{ number_format($snapshots->sum('base_amount'), 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-danger">
                    <div class="card-body">
                        <p class="mb-0 text-secondary">Platform Commission</p>
                        <h4 class="my-1 text-danger">₹{{ number_format($snapshots->sum('commission_amount'), 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-warning">
                    <div class="card-body">
                        <p class="mb-0 text-secondary">GST on Service (18%)</p>
                        <h4 class="my-1 text-warning">₹{{ number_format($snapshots->sum('gst_amount'), 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-success">
                    <div class="card-body">
                        <p class="mb-0 text-secondary">Net Seller Payable</p>
                        <h4 class="my-1 text-success">₹{{ number_format($snapshots->sum('seller_payable'), 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card radius-10">
            <div class="card-header bg-transparent py-3">
                <h6 class="mb-0 font-weight-bold">Order #{{ $orderId }} Line Item Breakdown</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item / SKU</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Base Amount</th>
                                <th>Commission %</th>
                                <th>Platform Fee</th>
                                <th>GST (18%)</th>
                                <th>Net Payable</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($snapshots as $item)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $item->listing ? $item->listing->seller_sku : 'Listing #' . $item->seller_listing_id }}</div>
                                        <div class="small text-muted">{{ $item->product ? $item->product->name : 'N/A' }}</div>
                                    </td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>₹{{ number_format($item->unit_price, 2) }}</td>
                                    <td>₹{{ number_format($item->base_amount, 2) }}</td>
                                    <td><span class="badge bg-light-primary text-primary">{{ number_format($item->commission_percentage, 2) }}%</span></td>
                                    <td class="fw-bold text-danger">₹{{ number_format($item->commission_amount, 2) }}</td>
                                    <td class="small text-muted">₹{{ number_format($item->gst_amount, 2) }}</td>
                                    <td class="fw-bold text-success">₹{{ number_format($item->seller_payable, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
