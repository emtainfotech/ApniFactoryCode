@extends('layout')
@section('title', $title)
@section('content')
@php
    $effectiveCommission = isset($commissionRate) ? (float)$commissionRate : 25.00;
    $catName = isset($category) && $category ? $category->name : 'General Category';
    $mainCatName = isset($mainCategory) && $mainCategory ? $mainCategory->name : '';
@endphp
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">

        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Apnifactory</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i> Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('seller/product') }}">Products</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Step 2: Pricing & Packaging</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <div>
                        <h4 class="mb-1 text-dark fw-bold"><i class="fa-solid fa-calculator text-primary me-2"></i>Configure Product Pricing & Pack Sizes</h4>
                        <p class="text-muted mb-0 small">Enter factory prices for your packaging variants. Display prices will be calculated automatically based on admin commission structure.</p>
                    </div>
                    <div class="text-end mt-2 mt-md-0">
                        <span class="badge bg-light-primary text-primary px-3 py-2 fs-6 rounded-pill border border-primary">
                            <i class="fa-solid fa-percent me-1"></i> Category Commission: <strong>{{ number_format($effectiveCommission, 2) }}%</strong>
                        </span>
                    </div>
                </div>

                <!-- Info Alert Banner -->
                <div class="alert alert-info border-0 bg-light-info text-dark alert-dismissible fade show mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <div class="fs-3 text-info me-3"><i class="fa-solid fa-circle-info"></i></div>
                        <div>
                            <strong>Pricing & Admin Review Guidelines:</strong>
                            <ul class="mb-0 mt-1 small ps-3">
                                <li><strong>Factory Price:</strong> Base manufacturing amount you receive.</li>
                                <li><strong>GST Included:</strong> If marked, this product will be routed to the <strong>Admin Panel Review Bucket</strong> for display price review and approval.</li>
                                <li><strong>Display Price:</strong> Auto-calculated as <code>Factory Price + {{ number_format($effectiveCommission, 2) }}%</code> commission. You can also manually fine-tune it.</li>
                                <li><strong>Shade Auto-Fill:</strong> Use the <em>"Apply to all shades"</em> button to quickly copy pricing across colors for the same pack size.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <form method="post" action="{{ route('product.store') }}" enctype="multipart/form-data" id="productPricingForm">
                    @csrf
                    <input type="hidden" name="previousdata" value="{{ json_encode($formdata) }}">
                    <input type="hidden" id="categoryCommissionRate" value="{{ $effectiveCommission }}">

                    <!-- Image Upload Box -->
                    <div class="card border mb-4">
                        <div class="card-header bg-light py-2 fw-semibold">
                            <i class="fa-solid fa-images text-secondary me-2"></i>Product Gallery Images
                        </div>
                        <div class="card-body">
                            <div class="upload__box">
                                <div class="upload__btn-box">
                                    <label class="upload__btn">
                                        <p class="mb-0"><i class="fa-solid fa-cloud-arrow-up me-1"></i> Click to Upload Images</p>
                                        <input type="file" multiple="" data-max_length="20" class="upload__inputfile" name="images[]" required="">
                                    </label>
                                </div>
                                <div class="upload__img-wrap mt-2"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Table -->
                    <div class="card border">
                        <div class="card-header bg-light py-3 d-flex flex-wrap justify-content-between align-items-center">
                            <div class="fw-semibold text-dark">
                                <i class="fa-solid fa-table-list text-primary me-2"></i>Variant Pricing Table ({{ count($formdata['attributes'] ?? []) }} Combinations)
                            </div>
                            <div class="small text-muted">
                                Category: <strong>{{ $mainCatName ? $mainCatName . ' > ' : '' }}{{ $catName }}</strong>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="pricingTable" class="table table-hover table-striped align-middle mb-0" style="width:100%" data-commission="{{ $effectiveCommission }}">
                                    <thead class="table-dark">
                                        <tr>
                                            <th style="min-width: 120px;">Packing</th>
                                            <th style="min-width: 140px;">Color / Shade</th>
                                            <th style="min-width: 160px;">Factory Price (₹) <span class="text-danger">*</span></th>
                                            <th style="min-width: 130px; text-align: center;">GST Included?</th>
                                            <th style="min-width: 160px;">Display Price (+{{ number_format($effectiveCommission, 2) }}%) <span class="text-danger">*</span></th>
                                            <th style="min-width: 140px;">MRP Price (₹) <span class="text-muted small">(Optional)</span></th>
                                            <th style="min-width: 120px; text-align: center;">Quick Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($formdata['attributes'] as $key => $attr)
                                            @php
                                                $dt = explode('-&-', $attr);
                                                $packId = $dt[0] ?? 0;
                                                $colorId = $dt[1] ?? 0;
                                                $packName = Helper::getboxpackingnamebyid($packId);
                                                $colorName = Helper::getcolorsnamebyid($colorId);
                                            @endphp
                                            <tr data-row-index="{{ $key }}" data-pack-id="{{ $packId }}" data-pack-name="{{ $packName }}">
                                                <input type="hidden" value="{{ $packId }}" name="boxpacking[]">
                                                <input type="hidden" value="{{ $colorId }}" name="color[]">

                                                <!-- 1. Packing -->
                                                <td>
                                                    <span class="badge bg-light text-dark border px-2 py-1 fw-bold fs-7">
                                                        <i class="fa-solid fa-box me-1 text-secondary"></i>{{ $packName }}
                                                    </span>
                                                </td>

                                                <!-- 2. Color -->
                                                <td>
                                                    <span class="d-inline-flex align-items-center">
                                                        <i class="fa-solid fa-palette me-2 text-primary"></i>
                                                        <span class="fw-semibold">{{ $colorName }}</span>
                                                    </span>
                                                </td>

                                                <!-- 3. Factory Price (Enter Price) -->
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">₹</span>
                                                        <input type="number" name="seller_price[]" class="form-control factory-price-input" step="0.01" min="0" placeholder="Factory Price" required>
                                                    </div>
                                                </td>

                                                <!-- 4. GST Included Checkbox -->
                                                <td class="text-center">
                                                    <div class="form-check d-flex justify-content-center align-items-center gap-1 mb-0">
                                                        <input type="hidden" name="is_gst_included[{{ $key }}]" value="0">
                                                        <input type="checkbox" name="is_gst_included[{{ $key }}]" value="1" class="form-check-input gst-checkbox" id="gstCheck_{{ $key }}">
                                                        <label class="form-check-label small text-muted" for="gstCheck_{{ $key }}">Incl. GST</label>
                                                    </div>
                                                </td>

                                                <!-- 5. Display Price (Auto calculated, editable) -->
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text bg-light-primary text-primary fw-bold">₹</span>
                                                        <input type="number" name="price[]" class="form-control display-price-input fw-bold" step="0.01" min="0" placeholder="Display Price" required>
                                                    </div>
                                                    <small class="text-muted d-block mt-1 fst-italic" style="font-size: 0.72rem;">Auto-calculated, editable</small>
                                                </td>

                                                <!-- 6. MRP Price (Optional) -->
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">₹</span>
                                                        <input type="number" name="mrp[]" class="form-control mrp-input" step="0.01" min="0" placeholder="MRP">
                                                    </div>
                                                </td>

                                                <!-- 7. Quick Copy Action -->
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-outline-primary btn-sm btn-copy-pack" title="Copy this price to all {{ $packName }} shades">
                                                        <i class="fa-solid fa-clone me-1"></i> Copy to {{ $packName }}
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card-footer bg-white p-3">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                <div class="text-muted small">
                                    <span id="gstNoticePill" class="badge bg-light-secondary text-secondary px-3 py-2 border">
                                        <i class="fa-solid fa-shield-halved me-1"></i> No GST-inclusive variants selected. Direct publishing enabled.
                                    </span>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ url('seller/product/add') }}" class="btn btn-outline-secondary">
                                        <i class="fa-solid fa-arrow-left me-1"></i> Back
                                    </a>
                                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Product
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--end page wrapper -->

<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('pricingTable');
    if (!table) return;

    const commissionRate = parseFloat(table.getAttribute('data-commission')) || 25.00;
    const gstNoticePill = document.getElementById('gstNoticePill');

    // Function to calculate Display Price: round(FactoryPrice * (1 + commission / 100), 2)
    function calculateDisplayPrice(factoryPrice) {
        if (isNaN(factoryPrice) || factoryPrice <= 0) return '';
        const calculated = factoryPrice * (1 + (commissionRate / 100));
        return calculated.toFixed(2);
    }

    // Update GST review banner state
    function updateGstNoticeState() {
        const gstCheckboxes = table.querySelectorAll('.gst-checkbox');
        let hasGstChecked = false;
        gstCheckboxes.forEach(cb => {
            if (cb.checked) hasGstChecked = true;
        });

        if (hasGstChecked) {
            gstNoticePill.className = 'badge bg-light-warning text-dark px-3 py-2 border border-warning';
            gstNoticePill.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> <strong>GST Included marked:</strong> Product will be sent to Admin Review Bucket for display price approval.';
        } else {
            gstNoticePill.className = 'badge bg-light-secondary text-secondary px-3 py-2 border';
            gstNoticePill.innerHTML = '<i class="fa-solid fa-shield-halved me-1"></i> No GST-inclusive variants selected. Direct publishing enabled.';
        }
    }

    // Real-time calculation on Factory Price input
    table.addEventListener('input', function (event) {
        if (event.target.classList.contains('factory-price-input')) {
            const row = event.target.closest('tr');
            const factoryVal = parseFloat(event.target.value);
            const displayInput = row.querySelector('.display-price-input');
            if (displayInput) {
                displayInput.value = calculateDisplayPrice(factoryVal);
            }
        }
    });

    // GST Checkbox change listener
    table.addEventListener('change', function (event) {
        if (event.target.classList.contains('gst-checkbox')) {
            updateGstNoticeState();
        }
    });

    // "Copy to all shades of this pack size" button handler
    table.addEventListener('click', function (event) {
        const btn = event.target.closest('.btn-copy-pack');
        if (!btn) return;

        const sourceRow = btn.closest('tr');
        const packId = sourceRow.getAttribute('data-pack-id');
        const packName = sourceRow.getAttribute('data-pack-name');

        const factoryVal = sourceRow.querySelector('.factory-price-input').value;
        const isGst = sourceRow.querySelector('.gst-checkbox').checked;
        const displayVal = sourceRow.querySelector('.display-price-input').value;
        const mrpVal = sourceRow.querySelector('.mrp-input').value;

        if (!factoryVal || parseFloat(factoryVal) <= 0) {
            alert('Please enter a valid Factory Price before copying.');
            return;
        }

        // Apply to all rows sharing the same packId
        const matchingRows = table.querySelectorAll(`tr[data-pack-id="${packId}"]`);
        let copiedCount = 0;

        matchingRows.forEach(row => {
            if (row !== sourceRow) {
                row.querySelector('.factory-price-input').value = factoryVal;
                row.querySelector('.gst-checkbox').checked = isGst;
                row.querySelector('.display-price-input').value = displayVal;
                row.querySelector('.mrp-input').value = mrpVal;
                copiedCount++;

                // Visual highlight effect
                row.classList.add('table-success');
                setTimeout(() => row.classList.remove('table-success'), 900);
            }
        });

        updateGstNoticeState();

        // Temporary toast / button feedback
        const originalHtml = btn.innerHTML;
        btn.innerHTML = `<i class="fa-solid fa-check text-success me-1"></i> Copied to ${copiedCount}!`;
        btn.classList.replace('btn-outline-primary', 'btn-success');
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.replace('btn-success', 'btn-outline-primary');
        }, 1500);
    });

    // Initial check
    updateGstNoticeState();
});
</script>
@endsection
