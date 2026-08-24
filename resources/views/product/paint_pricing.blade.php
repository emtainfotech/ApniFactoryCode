@extends('layout')
@section('title', $title)
@section('content')

<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">

        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">ApniFactory</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ url('seller/dashboard') }}"><i class="fa-solid fa-house"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ url('seller/product') }}">Products</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->

        @if(session('success'))
            <div class="alert alert-success border-0 bg-success alert-dismissible fade show py-2 text-white">
                <div class="d-flex align-items-center">
                    <div class="font-35 text-white"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="ms-3">
                        <h6 class="mb-0 text-white">Success</h6>
                        <div>{{ session('success') }}</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show py-2 text-white">
                <div class="d-flex align-items-center">
                    <div class="font-35 text-white"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div class="ms-3">
                        <h6 class="mb-0 text-white">Error</h6>
                        <div>{{ session('error') }}</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Family Selection Header -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="productFamilySelect" class="form-label fw-bold text-dark">
                            <i class="fa-solid fa-paint-roller text-primary me-2"></i>Select Paint Product / Family:
                        </label>
                        <select id="productFamilySelect" class="form-select form-select-lg border-primary">
                            <option value="">-- Choose Paint Product --</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}" {{ (isset($selectedProduct) && $selectedProduct->id == $prod->id) ? 'selected' : '' }}>
                                    {{ $prod->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-primary px-3 py-2 text-uppercase">Two-Price Engine</span>
                                    <h6 class="mt-2 mb-0 fw-bold">Factory Price vs Customer Price</h6>
                                    <small class="text-muted">Seller enters Base Factory Price. ApniFactory applies marketplace markup/commission automatically.</small>
                                </div>
                                <div class="text-end">
                                    <span class="fs-4 fw-bold text-success">{{ $company->comission ?? 25 }}%</span>
                                    <div class="text-muted small">Current Markup</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($selectedProduct))
        <!-- Smart Pricing Workspace -->
        <div class="row">
            <!-- Left Controls: Adjustment & Scope -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-dark text-white py-3">
                        <h6 class="mb-0 fw-bold text-white"><i class="fa-solid fa-sliders me-2"></i>1. Configure Bulk Adjustment</h6>
                    </div>
                    <div class="card-body p-3">
                        <!-- Adjustment Type -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Adjustment Type:</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="adjustment_type" id="type_per_litre" value="per_litre" checked>
                                <label class="btn btn-outline-primary py-2" for="type_per_litre" title="Multiplied by pack litrage (1L = 1x, 4L = 4x, 10L = 10x, 20L = 20x)">
                                    <i class="fa-solid fa-bottle-droplet me-1"></i> ₹ / Litre
                                </label>

                                <input type="radio" class="btn-check" name="adjustment_type" id="type_percentage" value="percentage">
                                <label class="btn btn-outline-primary py-2" for="type_percentage" title="Percentage increase/decrease on current factory price">
                                    <i class="fa-solid fa-percent me-1"></i> Percentage (%)
                                </label>

                                <input type="radio" class="btn-check" name="adjustment_type" id="type_fixed" value="fixed">
                                <label class="btn btn-outline-primary py-2" for="type_fixed" title="Flat rupee addition/reduction on all SKUs">
                                    <i class="fa-solid fa-indian-rupee-sign me-1"></i> Fixed (₹)
                                </label>
                            </div>
                        </div>

                        <!-- Adjustment Value -->
                        <div class="mb-3">
                            <label for="adjustmentValue" class="form-label fw-bold">Adjustment Value:</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light" id="valSymbol">₹/L</span>
                                <input type="number" step="0.01" class="form-control form-control-lg fw-bold" id="adjustmentValue" placeholder="e.g. 1.00 or -0.50" value="1.00">
                            </div>
                            <small class="text-muted" id="adjHelpText">
                                Enter positive value to increase, or negative (e.g. -1.00) to decrease.
                            </small>
                        </div>

                        <hr/>

                        <!-- Scope Selection -->
                        <div class="mb-3">
                            <label class="form-label fw-bold"><i class="fa-solid fa-filter me-1 text-primary"></i>2. Select Scope:</label>
                            <select id="scopeType" class="form-select mb-3">
                                <option value="family">Entire Paint Family (All Shades & Sizes)</option>
                                <option value="shades">Specific Shades Only</option>
                                <option value="packings">Specific Pack Sizes Only</option>
                            </select>

                            <!-- Shade Checkboxes -->
                            <div id="shadeScopeBox" class="p-3 bg-light rounded border mb-3" style="display: none; max-height: 200px; overflow-y: auto;">
                                <label class="form-label small fw-bold text-uppercase text-muted">Select Shades:</label>
                                @if(!empty($shades))
                                    @foreach($shades as $shd)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input shade-checkbox" type="checkbox" value="{{ $shd->id }}" id="shd_{{ $shd->id }}">
                                            <label class="form-check-label d-flex align-items-center" for="shd_{{ $shd->id }}">
                                                <span class="d-inline-block rounded-circle me-2 border shadow-sm" style="width: 16px; height: 16px; background-color: {{ $shd->hexcode }};"></span>
                                                {{ $shd->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <!-- Pack Size Checkboxes -->
                            <div id="packScopeBox" class="p-3 bg-light rounded border mb-3" style="display: none; max-height: 200px; overflow-y: auto;">
                                <label class="form-label small fw-bold text-uppercase text-muted">Select Pack Sizes:</label>
                                @if(!empty($packings))
                                    @foreach($packings as $pck)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input pack-checkbox" type="checkbox" value="{{ $pck->id }}" id="pck_{{ $pck->id }}">
                                            <label class="form-check-label fw-bold" for="pck_{{ $pck->id }}">
                                                {{ $pck->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <button type="button" id="btnPreview" class="btn btn-primary btn-lg shadow-sm">
                                <i class="fa-solid fa-calculator me-2"></i> Calculate Preview
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Audit History Widget -->
                @if(isset($adjustments) && count($adjustments) > 0)
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light py-3">
                        <h6 class="mb-0 fw-bold"><i class="fa-solid fa-clock-rotate-left me-2"></i>Recent Price Adjustments</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($adjustments as $adj)
                                <div class="list-group-item p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-secondary text-capitalize">{{ str_replace('_', ' ', $adj->adjustment_type) }}</span>
                                        <span class="badge {{ $adj->adjustment_value >= 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $adj->adjustment_value >= 0 ? '+' : '' }}{{ $adj->adjustment_value }}
                                        </span>
                                    </div>
                                    <div class="small text-muted">
                                        Affected <strong>{{ $adj->affected_count }}</strong> SKUs on {{ $adj->created_at->format('d M Y, h:i A') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Right Preview & SKU Matrix -->
            <div class="col-lg-8">
                <!-- Preview / Apply Card -->
                <div class="card shadow-sm border-0 mb-4" id="previewCard" style="display: none;">
                    <div class="card-header bg-success text-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-white fw-bold"><i class="fa-solid fa-table me-2"></i>Proposed Price Revision Preview</h6>
                        <span class="badge bg-white text-success fs-6" id="previewCountBadge">0 SKUs affected</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-info border-0 bg-light-info mb-3">
                            <i class="fa-solid fa-shield-halved text-info me-2"></i>
                            <strong>Safety Rule:</strong> No prices have been modified yet. Please review the old vs proposed prices below before confirming.
                        </div>

                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-bordered table-hover align-middle mb-0">
                                <thead class="table-dark sticky-top">
                                    <tr>
                                        <th>Shade</th>
                                        <th>Pack Size</th>
                                        <th>Old Factory Price</th>
                                        <th>Adjustment</th>
                                        <th>New Factory Price</th>
                                        <th>New Customer Price</th>
                                    </tr>
                                </thead>
                                <tbody id="previewTableBody">
                                    <!-- Dynamic Rows -->
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-secondary px-4" id="btnCancelPreview">Discard</button>
                            <button type="button" class="btn btn-success btn-lg px-5 shadow" id="btnApplyChanges">
                                <i class="fa-solid fa-check-double me-2"></i> Confirm & Apply Changes
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Current SKU Catalog Matrix Card -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="fa-solid fa-boxes-stacked me-2"></i>Active SKU Matrix & Direct Overrides</h6>
                        <a href="{{ route('product.edit', $selectedProduct->id) }}" class="btn btn-sm btn-outline-dark">
                            <i class="fa-solid fa-pen-to-square me-1"></i> Edit Product
                        </a>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle" id="skuCatalogTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Shade</th>
                                        <th>Pack Size</th>
                                        <th>Capacity (L)</th>
                                        <th>Seller Factory Price (₹)</th>
                                        <th>Customer Price (₹)</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(!empty($skus))
                                        @foreach($skus as $sku)
                                            @php
                                                $shd = $shades->firstWhere('id', $sku->color);
                                                $pck = $packings->firstWhere('id', $sku->quantity);
                                                $commRate = $company->comission ?? 25;
                                                $factoryPrice = $sku->seller_price ?: ($sku->price ? round($sku->price / (1 + ($commRate/100)), 2) : 0);
                                                $custPrice = $sku->price ?: round($factoryPrice * (1 + ($commRate/100)), 2);
                                            @endphp
                                            <tr id="sku_row_{{ $sku->id }}">
                                                <td>
                                                    <span class="d-inline-block rounded-circle me-2 border" style="width: 14px; height: 14px; background-color: {{ $shd->hexcode ?? '#ccc' }};"></span>
                                                    <strong>{{ $shd->name ?? 'Shade #'.$sku->color }}</strong>
                                                </td>
                                                <td>{{ $pck->name ?? 'Pack #'.$sku->quantity }}</td>
                                                <td><span class="badge bg-light text-dark border">{{ $sku->pack_litres ?: 1.0 }} L</span></td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">₹</span>
                                                        <input type="number" step="0.01" class="form-control fw-bold" id="sku_seller_price_{{ $sku->id }}" value="{{ $factoryPrice }}" oninput="updateRowCustomerPrice({{ $sku->id }}, {{ $commRate }})">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">₹</span>
                                                        <input type="text" class="form-control bg-light" id="sku_customer_price_{{ $sku->id }}" value="{{ $custPrice }}" readonly>
                                                    </div>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary" onclick="saveSingleSku({{ $sku->id }})">
                                                        <i class="fa-solid fa-floppy-disk me-1"></i> Save
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="card p-5 text-center shadow-sm border-0">
            <div class="text-muted">
                <i class="fa-solid fa-paint-roller fa-3x mb-3 text-secondary"></i>
                <h5>Select a paint product family above to start managing prices</h5>
            </div>
        </div>
        @endif

    </div>
</div>
<!--end page wrapper -->

<script>
    let currentPreviewData = null;

    // Change Product Family
    document.getElementById('productFamilySelect').addEventListener('change', function() {
        if (this.value) {
            window.location.href = "{{ route('seller.paint-pricing.index') }}?product_id=" + this.value;
        }
    });

    // Scope Selector toggle
    const scopeTypeSelect = document.getElementById('scopeType');
    if (scopeTypeSelect) {
        scopeTypeSelect.addEventListener('change', function() {
            const val = this.value;
            document.getElementById('shadeScopeBox').style.display = (val === 'shades') ? 'block' : 'none';
            document.getElementById('packScopeBox').style.display = (val === 'packings') ? 'block' : 'none';
        });
    }

    // Adjustment Type radio toggle
    document.querySelectorAll('input[name="adjustment_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const sym = document.getElementById('valSymbol');
            const help = document.getElementById('adjHelpText');
            if (this.value === 'per_litre') {
                sym.innerText = '₹/L';
                help.innerText = 'Adjusts each SKU according to pack litrage (e.g. +₹1 for 1L, +₹4 for 4L, +₹10 for 10L, +₹20 for 20L).';
            } else if (this.value === 'percentage') {
                sym.innerText = '%';
                help.innerText = 'Adjusts each selected SKU proportionally by % of factory price.';
            } else {
                sym.innerText = '₹';
                help.innerText = 'Adds/subtracts flat rupee amount on all selected SKUs.';
            }
        });
    });

    // Calculate Preview Button Click
    const btnPreview = document.getElementById('btnPreview');
    if (btnPreview) {
        btnPreview.addEventListener('click', function() {
            const productId = "{{ $selectedProduct->id ?? '' }}";
            const adjType = document.querySelector('input[name="adjustment_type"]:checked').value;
            const adjValue = parseFloat(document.getElementById('adjustmentValue').value) || 0;
            const scopeType = document.getElementById('scopeType').value;

            const selectedShades = [];
            document.querySelectorAll('.shade-checkbox:checked').forEach(cb => selectedShades.push(cb.value));

            const selectedPacks = [];
            document.querySelectorAll('.pack-checkbox:checked').forEach(cb => selectedPacks.push(cb.value));

            btnPreview.disabled = true;
            btnPreview.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Calculating...';

            fetch("{{ route('seller.paint-pricing.preview') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    product_id: productId,
                    adjustment_type: adjType,
                    adjustment_value: adjValue,
                    scope_type: scopeType,
                    shades: selectedShades,
                    packings: selectedPacks
                })
            })
            .then(async res => {
                const isJson = res.headers.get('content-type')?.includes('application/json');
                const data = isJson ? await res.json() : null;
                if (!res.ok) {
                    throw new Error((data && data.message) || `Server returned ${res.status}`);
                }
                return data;
            })
            .then(res => {
                btnPreview.disabled = false;
                btnPreview.innerHTML = '<i class="fa-solid fa-calculator me-2"></i> Calculate Preview';

                if (res && res.status && res.data && res.data.items) {
                    currentPreviewData = res.data;
                    renderPreviewTable(res.data);
                } else {
                    alert((res && res.message) || 'No SKUs matched the selected scope.');
                }
            })
            .catch(err => {
                btnPreview.disabled = false;
                btnPreview.innerHTML = '<i class="fa-solid fa-calculator me-2"></i> Calculate Preview';
                alert('Error computing preview: ' + err.message);
            });
        });
    }

    function renderPreviewTable(data) {
        const tbody = document.getElementById('previewTableBody');
        tbody.innerHTML = '';

        data.items.forEach(item => {
            const deltaBadge = item.adjustment_delta >= 0 
                ? `<span class="badge bg-success">+₹${item.adjustment_delta.toFixed(2)}</span>`
                : `<span class="badge bg-danger">-₹${Math.abs(item.adjustment_delta).toFixed(2)}</span>`;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <span class="d-inline-block rounded-circle me-2 border" style="width: 12px; height: 12px; background-color: ${item.hexcode};"></span>
                    <strong>${item.shade_name}</strong>
                </td>
                <td>${item.packing_name} <small class="text-muted">(${item.pack_litres}L)</small></td>
                <td class="text-muted">₹${item.old_seller_price.toFixed(2)}</td>
                <td>${deltaBadge}</td>
                <td><strong class="text-primary fs-6">₹${item.new_seller_price.toFixed(2)}</strong></td>
                <td><strong class="text-success fs-6">₹${item.new_customer_price.toFixed(2)}</strong></td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('previewCountBadge').innerText = `${data.affected_count} SKUs affected`;
        document.getElementById('previewCard').style.display = 'block';
        document.getElementById('previewCard').scrollIntoView({ behavior: 'smooth' });
    }

    // Cancel Preview
    const btnCancelPreview = document.getElementById('btnCancelPreview');
    if (btnCancelPreview) {
        btnCancelPreview.addEventListener('click', function() {
            document.getElementById('previewCard').style.display = 'none';
            currentPreviewData = null;
        });
    }

    // Apply Changes
    const btnApplyChanges = document.getElementById('btnApplyChanges');
    if (btnApplyChanges) {
        btnApplyChanges.addEventListener('click', function() {
            if (!currentPreviewData) return;

            if (!confirm(`Are you sure you want to apply these price updates across ${currentPreviewData.affected_count} SKUs?`)) {
                return;
            }

            btnApplyChanges.disabled = true;
            btnApplyChanges.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Applying...';

            fetch("{{ route('seller.paint-pricing.apply') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    product_id: currentPreviewData.product_id,
                    adjustment_type: currentPreviewData.adjustment_type,
                    adjustment_value: currentPreviewData.adjustment_value,
                    scope_type: currentPreviewData.scope.type || 'family',
                    shades: currentPreviewData.scope.shades || [],
                    packings: currentPreviewData.scope.packings || [],
                    skus: currentPreviewData.scope.skus || []
                })
            })
            .then(async res => {
                const isJson = res.headers.get('content-type')?.includes('application/json');
                const data = isJson ? await res.json() : null;
                if (!res.ok) {
                    throw new Error((data && data.message) || `Server returned ${res.status}`);
                }
                return data;
            })
            .then(res => {
                if (res && res.status) {
                    alert((res.data && res.data.message) || res.message || 'Prices successfully updated!');
                    window.location.reload();
                } else {
                    btnApplyChanges.disabled = false;
                    btnApplyChanges.innerHTML = '<i class="fa-solid fa-check-double me-2"></i> Confirm & Apply Changes';
                    alert((res && res.message) || 'Error applying changes.');
                }
            })
            .catch(err => {
                btnApplyChanges.disabled = false;
                btnApplyChanges.innerHTML = '<i class="fa-solid fa-check-double me-2"></i> Confirm & Apply Changes';
                alert('Error applying changes: ' + err.message);
            });
        });
    }

    // Live Row Calculation on Single SKU Edit
    function updateRowCustomerPrice(skuId, commissionRate) {
        const factoryInput = document.getElementById(`sku_seller_price_${skuId}`);
        const custInput = document.getElementById(`sku_customer_price_${skuId}`);
        const val = parseFloat(factoryInput.value) || 0;
        custInput.value = (val * (1 + (commissionRate / 100))).toFixed(2);
    }

    // Save Single SKU Override
    function saveSingleSku(skuId) {
        const factoryPrice = parseFloat(document.getElementById(`sku_seller_price_${skuId}`).value) || 0;

        fetch("{{ route('seller.paint-pricing.sku-override') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                sku_id: skuId,
                seller_price: factoryPrice
            })
        })
        .then(async res => {
            const isJson = res.headers.get('content-type')?.includes('application/json');
            const data = isJson ? await res.json() : null;
            if (!res.ok) {
                throw new Error((data && data.message) || `Server returned ${res.status}`);
            }
            return data;
        })
        .then(res => {
            if (res && res.status) {
                const row = document.getElementById(`sku_row_${skuId}`);
                row.classList.add('table-success');
                setTimeout(() => row.classList.remove('table-success'), 2000);
            } else {
                alert((res && res.message) || 'Failed to update SKU.');
            }
        })
        .catch(err => alert('Error saving SKU: ' + err.message));
    }
</script>

@endsection
