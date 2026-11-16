@extends('layout')
@section('title', $title)
@section('content')
<div class="page-wrapper">
    <div class="page-content">

        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Seller Portal</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ url('seller/dashboard') }}"><i class="bx bx-home-alt"></i> Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('seller/product') }}">Products</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Category Pricing & Offers</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <span class="badge bg-light-success text-success border border-success px-3 py-2 rounded-pill fs-7">
                    <i class="fa-solid fa-building me-1"></i> {{ $company->name ?? 'Your Company' }}
                </span>
            </div>
        </div>

        <!-- Header Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h4 class="mb-1 text-dark fw-bold">
                            <i class="fa-solid fa-tags text-warning me-2"></i>Category Product Pricing & Offer Manager
                        </h4>
                        <p class="text-muted mb-0 small">
                            Update your <strong>Factory Price</strong> to configure offers. Display prices recalculate automatically using the category commission structure without affecting other companies.
                        </p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-light-primary text-primary px-3 py-2 fs-6 rounded-pill border border-primary">
                            <i class="fa-solid fa-percent me-1"></i> Commission: <strong>{{ number_format($effectiveCommissionRate, 2) }}%</strong>
                        </span>
                    </div>
                </div>

                <!-- Info Banner -->
                <div class="alert alert-info border-0 bg-light-info text-dark d-flex align-items-center mt-3 mb-0">
                    <i class="fa-solid fa-shield-halved fs-4 text-info me-3"></i>
                    <div class="small">
                        <strong>Manufacturer Price Sovereignty:</strong> You edit the <strong>Factory Price</strong> (your base manufacturing payout). The customer display price is dynamically updated using the category commission rate. Changes strictly apply to your products only.
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Selector Pills -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-2 overflow-auto py-1">
                    <span class="text-muted small fw-bold text-nowrap me-2"><i class="fa-solid fa-filter me-1"></i>Select Category:</span>
                    @forelse($categories as $cat)
                        <a href="{{ route('seller.category-pricing.index', ['category_id' => $cat->id]) }}" 
                           class="btn btn-sm {{ (int)$selectedCategoryId === (int)$cat->id ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill text-nowrap">
                            {{ $cat->name }}
                        </a>
                    @empty
                        <span class="text-muted small">No products configured under any category yet.</span>
                    @endforelse
                </div>
            </div>
        </div>

        @if($selectedCategory)
            <!-- Bulk Category Offer Toolbar -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fa-solid fa-bolt text-warning me-2"></i>Quick Bulk Category Offer / Price Adjustment
                    </h6>
                </div>
                <div class="card-body">
                    <form id="bulkCategoryOfferForm" class="row g-3 align-items-end">
                        @csrf
                        <input type="hidden" name="category_id" value="{{ $selectedCategory->id }}">
                        
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">Adjustment Type</label>
                            <select name="adjustment_type" class="form-select form-select-sm" id="bulkAdjustmentType">
                                <option value="percentage">Percentage Offer (% on Factory Price)</option>
                                <option value="fixed_per_unit">Flat Amount (₹ per unit on Factory Price)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">Offer Value (Use minus for discount, e.g. -5 for 5% off)</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text" id="bulkAdjUnitPrefix">±</span>
                                <input type="number" step="0.01" name="adjustment_value" class="form-control" placeholder="e.g. -5 or 10" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-warning btn-sm fw-bold w-100" id="btnApplyBulkOffer">
                                <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Apply Offer to All {{ $products->count() }} Products
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Product SKUs Table -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center">
                    <div class="fw-bold text-dark">
                        <i class="fa-solid fa-box-open text-primary me-2"></i>Products in {{ $selectedCategory->name }} ({{ $products->count() }} Products)
                    </div>
                    <div class="small text-muted">
                        Category Commission Rate: <strong>{{ number_format($effectiveCommissionRate, 2) }}%</strong>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="categoryPricingTable" data-commission="{{ $effectiveCommissionRate }}">
                            <thead class="table-dark">
                                <tr>
                                    <th style="min-width: 220px;">Product</th>
                                    <th style="min-width: 120px;">Packing</th>
                                    <th style="min-width: 130px;">Color / Shade</th>
                                    <th style="min-width: 170px;">Factory Price (₹)</th>
                                    <th style="min-width: 130px; text-align: center;">Commission</th>
                                    <th style="min-width: 170px;">Display Price (₹)</th>
                                    <th style="min-width: 120px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $prod)
                                    @php $skus = $prod->product_attributes; @endphp
                                    @forelse($skus as $sku)
                                        @php
                                            $packName = $sku->boxPacking?->name ?? 'Pack #' . $sku->quantity;
                                            $colorName = $sku->shadeCard?->name ?? 'Color #' . $sku->color;
                                            $factoryPrice = $sku->seller_price ?: ($sku->price ? round($sku->price / (1 + ($effectiveCommissionRate / 100)), 2) : 0);
                                            $displayPrice = $sku->price ?: round($factoryPrice * (1 + ($effectiveCommissionRate / 100)), 2);
                                        @endphp
                                        <tr data-sku-id="{{ $sku->id }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($prod->image)
                                                        <img src="{{ asset('storage/' . $prod->image) }}" class="rounded me-2 border" style="width: 40px; height: 40px; object-fit: cover;">
                                                    @else
                                                        <div class="rounded me-2 border d-flex align-items-center justify-content-center bg-light text-muted" style="width: 40px; height: 40px;">
                                                            <i class="fa-solid fa-paint-roller"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-bold text-dark">{{ $prod->name }}</div>
                                                        <small class="text-muted">{{ $prod->brand?->name ?? 'No Brand' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border px-2 py-1">
                                                    <i class="fa-solid fa-box me-1 text-secondary"></i>{{ $packName }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="d-inline-flex align-items-center">
                                                    <i class="fa-solid fa-palette text-primary me-1"></i>{{ $colorName }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">₹</span>
                                                    <input type="number" step="0.01" min="0" 
                                                        class="form-control fw-bold seller-factory-input" 
                                                        value="{{ number_format($factoryPrice, 2, '.', '') }}" 
                                                        placeholder="0.00">
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light-primary text-primary border border-primary px-2 py-1">
                                                    +{{ number_format($effectiveCommissionRate, 2) }}%
                                                </span>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light-success text-success fw-bold">₹</span>
                                                    <input type="text" class="form-control calculated-display-input bg-light fw-bold" 
                                                        value="{{ number_format($displayPrice, 2, '.', '') }}" readonly>
                                                </div>
                                                <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Auto-calculated</small>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-primary btn-save-sku" 
                                                    data-sku-id="{{ $sku->id }}">
                                                    <i class="fa-solid fa-floppy-disk me-1"></i> Save
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-muted text-center py-2">No variants found for product {{ $prod->name }}.</td>
                                        </tr>
                                    @endforelse
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No products found in this category.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="card shadow-sm border-0 text-center py-5">
                <div class="card-body">
                    <i class="fa-solid fa-tags text-muted fs-1 mb-3"></i>
                    <h5 class="text-dark">No Category Selected</h5>
                    <p class="text-muted">Please select a category above to view and manage your product offers and factory prices.</p>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('categoryPricingTable');
    if (!table) return;

    const commissionRate = parseFloat(table.getAttribute('data-commission')) || 25.00;

    // Real-time Display Price calculation as seller edits Factory Price
    table.addEventListener('input', function (e) {
        if (e.target.classList.contains('seller-factory-input')) {
            const row = e.target.closest('tr');
            const factoryVal = parseFloat(e.target.value);
            const displayInput = row.querySelector('.calculated-display-input');

            if (!isNaN(factoryVal) && factoryVal >= 0) {
                const calculatedDisplay = factoryVal * (1 + (commissionRate / 100));
                displayInput.value = calculatedDisplay.toFixed(2);
            } else {
                displayInput.value = '';
            }
        }
    });

    // Save individual SKU factory price
    table.addEventListener('click', function (e) {
        const btnSave = e.target.closest('.btn-save-sku');
        if (!btnSave) return;

        const row = btnSave.closest('tr');
        const skuId = btnSave.getAttribute('data-sku-id');
        const factoryInput = row.querySelector('.seller-factory-input');
        const factoryVal = parseFloat(factoryInput.value);

        if (isNaN(factoryVal) || factoryVal < 0) {
            alert('Please enter a valid Factory Price.');
            return;
        }

        const originalHtml = btnSave.innerHTML;
        btnSave.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        btnSave.disabled = true;

        fetch("{{ route('seller.category-pricing.update-sku') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                sku_id: skuId,
                seller_price: factoryVal
            })
        })
        .then(res => res.json())
        .then(data => {
            btnSave.disabled = false;
            if (data.status) {
                btnSave.innerHTML = '<i class="fa-solid fa-check text-success"></i> Saved';
                btnSave.classList.replace('btn-primary', 'btn-success');
                setTimeout(() => {
                    btnSave.innerHTML = originalHtml;
                    btnSave.classList.replace('btn-success', 'btn-primary');
                }, 1500);

                row.classList.add('table-success');
                setTimeout(() => row.classList.remove('table-success'), 1200);
            } else {
                btnSave.innerHTML = originalHtml;
                alert(data.message || 'Error updating price.');
            }
        })
        .catch(err => {
            btnSave.disabled = false;
            btnSave.innerHTML = originalHtml;
            alert('Request failed. Check connection.');
        });
    });

    // Bulk Category Offer Form Submit
    const bulkForm = document.getElementById('bulkCategoryOfferForm');
    if (bulkForm) {
        bulkForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!confirm('Are you sure you want to apply this offer across all products in this category? Customer display prices will be recalculated automatically.')) {
                return;
            }

            const btn = document.getElementById('btnApplyBulkOffer');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Applying Offer...';
            btn.disabled = true;

            const formData = new FormData(bulkForm);
            const payload = {
                category_id: formData.get('category_id'),
                adjustment_type: formData.get('adjustment_type'),
                adjustment_value: formData.get('adjustment_value')
            };

            fetch("{{ route('seller.category-pricing.bulk-apply') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                if (data.status) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to apply offer.');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                alert('Request failed. Check connection.');
            });
        });
    }
});
</script>
@endsection

