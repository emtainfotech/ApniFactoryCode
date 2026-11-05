@extends('layout')
@section('title', 'New Dynamic Price Revision')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Pricing Engine</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('seller.pricing.revisions.index') }}">Revisions</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create Revision</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="{{ route('seller.pricing.revisions.index') }}" class="btn btn-outline-secondary px-3">
                    <i class="bx bx-arrow-back me-1"></i> Back to Revisions
                </a>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show py-2">
                <div class="d-flex align-items-center">
                    <div class="font-35 text-white"><i class="bx bxs-message-square-x"></i></div>
                    <div class="ms-3 text-white">{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form id="priceRevisionForm" method="POST" action="{{ route('seller.pricing.revisions.store') }}">
            @csrf
            <input type="hidden" name="preview_token" id="previewTokenInput" value="">

            <div class="row">
                <!-- Left: Configuration & Filters -->
                <div class="col-xl-4 col-lg-5">
                    <div class="card radius-10">
                        <div class="card-header bg-transparent py-3">
                            <h6 class="mb-0 font-weight-bold">1. Revision Method & Settings</h6>
                        </div>
                        <div class="card-body">
                            <!-- Method -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Revision Method <span class="text-danger">*</span></label>
                                <select name="revision_method" id="revisionMethod" class="form-select" required>
                                    <option value="percentage">Percentage Revision (+/- %)</option>
                                    <option value="flat_per_unit">Flat per Unit (+/- ₹/L or ₹/Kg)</option>
                                    <option value="explicit_list">Explicit Pack Price List</option>
                                </select>
                            </div>

                            <!-- Direction -->
                            <div class="mb-3" id="directionContainer">
                                <label class="form-label fw-bold">Direction <span class="text-danger">*</span></label>
                                <select name="revision_direction" id="revisionDirection" class="form-select">
                                    <option value="increase">Increase (+ Price Hike)</option>
                                    <option value="decrease">Decrease (- Price Reduction)</option>
                                </select>
                            </div>

                            <!-- Value Input -->
                            <div class="mb-3" id="valueContainer">
                                <label class="form-label fw-bold" id="valueLabel">Percentage Value (%) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text" id="valuePrefix">%</span>
                                    <input type="number" step="0.01" min="0.01" max="1000" name="revision_value" id="revisionValue" class="form-control" placeholder="e.g. 4.00" value="4.00">
                                </div>
                                <small class="text-muted" id="valueHelp">e.g. Enter 4 for a +4% increase across eligible SKUs.</small>
                            </div>

                            <!-- Price Basis Unit (for Flat per Unit) -->
                            <div class="mb-3" id="unitContainer" style="display: none;">
                                <label class="form-label fw-bold">Price Basis Unit <span class="text-danger">*</span></label>
                                <select name="price_basis_unit" id="priceBasisUnit" class="form-select">
                                    <option value="Litre">Litre (₹/L)</option>
                                    <option value="Kg">Kilogram (₹/Kg)</option>
                                    <option value="Piece">Piece (₹/Pc)</option>
                                    <option value="Box">Box (₹/Box)</option>
                                    <option value="Meter">Meter (₹/M)</option>
                                </select>
                                <small class="text-danger d-block mt-1">
                                    <i class="bx bx-info-circle"></i> Strict validation: SKUs not matching this basis unit will be flagged and excluded (no silent unit conversion).
                                </small>
                            </div>

                            <!-- Rounding Rule -->
                            <div class="mb-3" id="roundingContainer">
                                <label class="form-label fw-bold">Rounding Rule</label>
                                <select name="rounding_rule" id="roundingRule" class="form-select">
                                    <option value="round_half" selected>Round to nearest 0.50 (e.g. ₹104.50)</option>
                                    <option value="round_integer">Round to whole Rupee (e.g. ₹105.00)</option>
                                    <option value="round_to_99">Psychological .99 (e.g. ₹104.99)</option>
                                    <option value="none">Standard 2 Decimals (No rounding)</option>
                                </select>
                            </div>

                            <hr>
                            <h6 class="mb-3 font-weight-bold">2. SKU Selection & Filters</h6>

                            <!-- Category Filter -->
                            <div class="mb-3">
                                <label class="form-label">Category Filter</label>
                                <select name="filters[category_id]" id="filterCategory" class="form-select">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Brand Filter -->
                            <div class="mb-3">
                                <label class="form-label">Brand Filter</label>
                                <select name="filters[brand_id]" id="filterBrand" class="form-select">
                                    <option value="">All Brands</option>
                                    @foreach($brands as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="button" id="btnPreview" class="btn btn-outline-primary w-100 py-2">
                                <i class="bx bx-calculator me-1"></i> Generate Non-Mutating Preview
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right: Preview Table & Scheduling -->
                <div class="col-xl-8 col-lg-7">
                    <!-- Preview Summary Card -->
                    <div class="card radius-10 mb-3" id="previewSummaryCard" style="display: none;">
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-3 border-end">
                                    <span class="text-secondary small">Evaluated SKUs</span>
                                    <h4 class="mb-0 text-dark" id="summaryEvaluated">0</h4>
                                </div>
                                <div class="col-3 border-end">
                                    <span class="text-secondary small">Eligible SKUs</span>
                                    <h4 class="mb-0 text-success" id="summaryEligible">0</h4>
                                </div>
                                <div class="col-3 border-end">
                                    <span class="text-secondary small">Incompatible Units</span>
                                    <h4 class="mb-0 text-warning" id="summaryIncompatible">0</h4>
                                </div>
                                <div class="col-3">
                                    <span class="text-secondary small">Invalid / Capped</span>
                                    <h4 class="mb-0 text-danger" id="summaryInvalid">0</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table Card -->
                    <div class="card radius-10">
                        <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 font-weight-bold">3. Proposed SKU Price Preview</h6>
                            <span class="badge bg-light-info text-info" id="previewStatusBadge">Awaiting Preview Calculation</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                                <table class="table table-hover table-striped align-middle mb-0" id="previewTable">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>SKU / Product</th>
                                            <th>Unit</th>
                                            <th>Old Seller Price</th>
                                            <th>Proposed Seller Price</th>
                                            <th>Round Diff</th>
                                            <th>Selling Price</th>
                                            <th>Compatibility</th>
                                        </tr>
                                    </thead>
                                    <tbody id="previewTableBody">
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <i class="bx bx-calculator fs-1 d-block mb-2"></i>
                                                Configure parameters on the left and click <strong>"Generate Non-Mutating Preview"</strong> to view calculated prices.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Scheduling & Confirmation Card -->
                    <div class="card radius-10 mt-3">
                        <div class="card-header bg-transparent py-3">
                            <h6 class="mb-0 font-weight-bold">4. Schedule & Confirm Activation</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Effective Date & Time (IST) <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="effective_at" id="effectiveAt" class="form-control" required value="{{ now()->addHour()->format('Y-m-d\TH:i') }}">
                                    <small class="text-muted">Mandatory IST timestamp when price revision activates in the marketplace catalogue.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Revision Reason / Circular Ref</label>
                                    <input type="text" name="reason" class="form-control" placeholder="e.g. Q4 Raw Material Cost Revision - Ref #RM-2026-08">
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="approval_required" value="1" id="approvalRequiredCheck">
                                        <label class="form-check-label" for="approvalRequiredCheck">
                                            Require Platform Admin / Organization Approval before activation
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12 text-end mt-4">
                                    <button type="submit" id="btnSubmitRevision" class="btn btn-success px-5 py-2" disabled>
                                        <i class="bx bx-calendar-check me-1"></i> Confirm & Schedule Revision
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const methodSelect = document.getElementById('revisionMethod');
    const directionContainer = document.getElementById('directionContainer');
    const valueContainer = document.getElementById('valueContainer');
    const valueLabel = document.getElementById('valueLabel');
    const valuePrefix = document.getElementById('valuePrefix');
    const unitContainer = document.getElementById('unitContainer');
    const btnPreview = document.getElementById('btnPreview');
    const btnSubmit = document.getElementById('btnSubmitRevision');
    const previewSummaryCard = document.getElementById('previewSummaryCard');
    const previewTableBody = document.getElementById('previewTableBody');
    const previewStatusBadge = document.getElementById('previewStatusBadge');
    const previewTokenInput = document.getElementById('previewTokenInput');

    function updateFormVisibility() {
        const method = methodSelect.value;
        if (method === 'percentage') {
            directionContainer.style.display = 'block';
            valueContainer.style.display = 'block';
            valueLabel.textContent = 'Percentage Value (%) *';
            valuePrefix.textContent = '%';
            unitContainer.style.display = 'none';
        } else if (method === 'flat_per_unit') {
            directionContainer.style.display = 'block';
            valueContainer.style.display = 'block';
            valueLabel.textContent = 'Flat Amount per Unit (₹) *';
            valuePrefix.textContent = '₹';
            unitContainer.style.display = 'block';
        } else if (method === 'explicit_list') {
            directionContainer.style.display = 'none';
            valueContainer.style.display = 'none';
            unitContainer.style.display = 'none';
        }
    }

    methodSelect.addEventListener('change', updateFormVisibility);
    updateFormVisibility();

    btnPreview.addEventListener('click', function() {
        btnPreview.disabled = true;
        btnPreview.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Calculating...';

        const formData = new FormData(document.getElementById('priceRevisionForm'));
        const payload = {
            revision_method: formData.get('revision_method'),
            revision_direction: formData.get('revision_direction'),
            revision_value: formData.get('revision_value'),
            price_basis_unit: formData.get('price_basis_unit'),
            rounding_rule: formData.get('rounding_rule'),
            filters: {
                category_id: formData.get('filters[category_id]') || null,
                brand_id: formData.get('filters[brand_id]') || null,
            }
        };

        fetch('{{ route("seller.pricing.revisions.preview") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(response => {
            btnPreview.disabled = false;
            btnPreview.innerHTML = '<i class="bx bx-calculator me-1"></i> Generate Non-Mutating Preview';

            if (!response.status) {
                alert('Preview Error: ' + (response.message || 'Unable to calculate preview.'));
                return;
            }

            const data = response.data;
            previewTokenInput.value = data.preview_token;

            // Update summary
            document.getElementById('summaryEvaluated').textContent = data.summary.total_evaluated;
            document.getElementById('summaryEligible').textContent = data.summary.total_eligible;
            document.getElementById('summaryIncompatible').textContent = data.summary.total_incompatible;
            document.getElementById('summaryInvalid').textContent = data.summary.total_invalid;
            previewSummaryCard.style.display = 'block';

            previewStatusBadge.className = 'badge bg-light-success text-success';
            previewStatusBadge.textContent = `Preview Ready: ${data.summary.total_eligible} Eligible SKUs`;

            // Populate table
            previewTableBody.innerHTML = '';
            if (data.items.length === 0) {
                previewTableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No matching listings found.</td></tr>';
                btnSubmit.disabled = true;
                return;
            }

            data.items.forEach(item => {
                const tr = document.createElement('tr');
                if (!item.is_unit_compatible) {
                    tr.className = 'table-warning';
                }

                const diffDisplay = item.rounding_difference > 0
                    ? `+₹${item.rounding_difference.toFixed(2)}`
                    : (item.rounding_difference < 0 ? `-₹${Math.abs(item.rounding_difference).toFixed(2)}` : '₹0.00');

                tr.innerHTML = `
                    <td>
                        <span class="fw-bold">${item.seller_sku || 'SKU#' + item.seller_listing_id}</span>
                        <div class="small text-muted">${item.product_name} (${item.variant_sku})</div>
                    </td>
                    <td><span class="badge bg-light-dark text-dark">${item.unit_of_measurement || 'N/A'}</span></td>
                    <td>₹${item.old_seller_price.toFixed(2)}</td>
                    <td class="fw-bold text-primary">₹${item.new_seller_price.toFixed(2)}</td>
                    <td class="small text-muted">${diffDisplay}</td>
                    <td>₹${item.new_selling_price.toFixed(2)}</td>
                    <td>
                        ${item.is_unit_compatible
                            ? '<span class="badge bg-success">Eligible</span>'
                            : `<span class="badge bg-danger" title="${item.validation_error || 'Incompatible'}">Incompatible</span>`
                        }
                    </td>
                `;
                previewTableBody.appendChild(tr);
            });

            btnSubmit.disabled = data.summary.total_eligible === 0;
        })
        .catch(err => {
            btnPreview.disabled = false;
            btnPreview.innerHTML = '<i class="bx bx-calculator me-1"></i> Generate Non-Mutating Preview';
            alert('Failed to connect to preview service: ' + err.message);
        });
    });
});
</script>
@endsection
