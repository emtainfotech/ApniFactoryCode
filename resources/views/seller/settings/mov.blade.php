@extends('layout')
@section('title', 'Minimum Order Value (MOV) Configuration')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Seller Settings</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('seller.catalogue.analytics') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Minimum Order Value (MOV)</li>
                    </ol>
                </nav>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 bg-success alert-dismissible fade show">
                <div class="text-white">{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show">
                <div class="text-white">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Current MOV Setting Card -->
            <div class="col-12 col-lg-5">
                <div class="card radius-10 border-start border-0 border-3 border-primary shadow-sm">
                    <div class="card-header bg-transparent py-3">
                        <h6 class="mb-0 font-weight-bold text-primary">Configure Minimum Order Value (MOV)</h6>
                    </div>
                    <div class="card-body">
                        <div class="p-3 mb-3 bg-light rounded">
                            <span class="text-secondary small text-uppercase fw-semibold">Current Configured Threshold</span>
                            <h2 class="text-primary mt-1 mb-0">₹{{ number_format($company->getRawMinOrderValue(), 2) }}</h2>
                            <p class="text-muted small mt-1 mb-0">
                                Buyers must have at least this subtotal of your products in their cart to checkout.
                            </p>
                        </div>

                        <form action="{{ route('seller.settings.mov.update') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="min_order_value" class="form-label fw-bold">New Minimum Order Value (₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" min="0" max="10000000" class="form-control" id="min_order_value" name="min_order_value" value="{{ old('min_order_value', $company->getRawMinOrderValue()) }}" required>
                                </div>
                                <div class="form-text">Set to 0 if you do not want to enforce a minimum order threshold.</div>
                            </div>

                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason for Change (Audit Trail)</label>
                                <textarea class="form-control" id="reason" name="reason" rows="2" placeholder="e.g. Revised commercial policy for factory direct orders">{{ old('reason') }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bx bx-save"></i> Save MOV Setting
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Audit Trail Card -->
            <div class="col-12 col-lg-7">
                <div class="card radius-10 shadow-sm">
                    <div class="card-header bg-transparent py-3">
                        <h6 class="mb-0 font-weight-bold text-dark">Audit Trail & Change History</h6>
                    </div>
                    <div class="card-body">
                        @if($auditHistory->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Timestamp</th>
                                            <th>Updated By</th>
                                            <th>Action / Summary</th>
                                            <th>Previous MOV</th>
                                            <th>New MOV</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($auditHistory as $log)
                                            <tr>
                                                <td class="small">{{ $log->created_at->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</td>
                                                <td><span class="badge bg-light text-dark border">{{ $log->actor_name }}</span></td>
                                                <td class="small">{{ $log->description ?: $log->title }}</td>
                                                <td>₹{{ number_format($log->old_values['minordervalue'] ?? 0, 2) }}</td>
                                                <td class="fw-bold text-success">₹{{ number_format($log->new_values['minordervalue'] ?? 0, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="bx bx-history fs-1 text-secondary"></i>
                                <p class="mt-2 mb-0">No recorded changes to Minimum Order Value yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
