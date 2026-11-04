@extends('layout')
@section('title', 'Bulk Catalogue Manager')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Seller Portal</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ url('seller/dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('product.list') }}">Products</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Bulk Catalogue Manager</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('seller.catalogue.bulk.history') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-clock-rotate-left me-1"></i> Bulk Operations History
                </a>
                <a href="{{ route('seller.catalogue.export') }}" class="btn btn-outline-primary">
                    <i class="fa-solid fa-file-arrow-down me-1"></i> Export Catalogue
                </a>
            </div>
        </div>
        <!--end breadcrumb-->

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Filter Card -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('seller.catalogue.bulk') }}" class="row g-2 align-items-end" id="filterForm">
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Search Keywords</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="SKU, Product Title..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="Archived" {{ request('status') === 'Archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Category</label>
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">Brand</label>
                        <select name="brand_id" class="form-select form-select-sm">
                            <option value="">All Brands</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary flex-fill">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('seller.catalogue.bulk') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bulk Action Controls Card -->
        <div class="card mb-3 border-top border-3 border-primary">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-primary px-3 py-2 font-14" id="selectionCountBadge">0 items selected</span>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="radio" name="scopeChoice" id="scopeSelected" value="ids" checked>
                            <label class="form-check-label small" for="scopeSelected">Selected items only</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="radio" name="scopeChoice" id="scopeFiltered" value="filter">
                            <label class="form-check-label small" for="scopeFiltered">All matching active filters (Max 500)</label>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <!-- Status Update Button -->
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bulkStatusModal">
                            <i class="fa-solid fa-power-off me-1"></i> Change Status
                        </button>
                        <!-- Price Update Button -->
                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#bulkPriceModal">
                            <i class="fa-solid fa-tags me-1"></i> Adjust Pricing
                        </button>
                        <!-- Stock Update Button -->
                        <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#bulkStockModal">
                            <i class="fa-solid fa-boxes-stacked me-1"></i> Adjust Stock
                        </button>
                        <!-- Order Config Button -->
                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#bulkOrderConfigModal">
                            <i class="fa-solid fa-sliders me-1"></i> Order & MOQ
                        </button>
                        <!-- Archive Button -->
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="triggerDirectOperation('archive')">
                            <i class="fa-solid fa-box-archive me-1"></i> Archive
                        </button>
                        <!-- Restore Button -->
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="triggerDirectOperation('restore')">
                            <i class="fa-solid fa-arrow-rotate-left me-1"></i> Restore
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Master Form for Submitting Bulk Preview -->
        <form id="masterBulkForm" method="POST" action="{{ route('seller.catalogue.bulk.preview') }}">
            @csrf
            <input type="hidden" name="operation_type" id="masterOperationType" value="">
            <input type="hidden" name="selection_mode" id="masterSelectionMode" value="ids">
            <input type="hidden" name="selected_ids" id="masterSelectedIds" value="">
            <input type="hidden" name="target_entity" value="listing">
            <!-- Active filters passed along for filter mode -->
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input type="hidden" name="category_id" value="{{ request('category_id') }}">
            <input type="hidden" name="brand_id" value="{{ request('brand_id') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <!-- Dynamic Payload Fields Injected by Modals -->
            <div id="dynamicPayloadContainer"></div>
        </form>

        <!-- Listings Table Card -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">
                                    <input type="checkbox" class="form-check-input" id="checkAll">
                                </th>
                                <th>SKU & ID</th>
                                <th>Product & Category</th>
                                <th>Variant</th>
                                <th>Pricing</th>
                                <th>Stock</th>
                                <th>Listing Status</th>
                                <th>Parent Product</th>
                                <th class="text-end">Quick Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($listings as $listing)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input row-checkbox" value="{{ $listing->id }}">
                                    </td>
                                    <td>
                                        <strong class="font-monospace text-primary">{{ $listing->seller_sku ?: 'SKU-'.$listing->id }}</strong>
                                        <div class="text-muted small">ID: #{{ $listing->id }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $listing->product->name ?? $listing->product->title ?? 'N/A' }}</div>
                                        <div class="small text-muted">
                                            <span>Cat: {{ $listing->product->category->name ?? 'N/A' }}</span> |
                                            <span>Brand: {{ $listing->product->brand->name ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($listing->variant)
                                            <span class="badge bg-light text-dark border">{{ $listing->variant->variant_name ?: $listing->variant->variant_sku }}</span>
                                        @else
                                            <span class="text-muted small">Standard Listing</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div><small class="text-muted">Seller:</small> ₹{{ number_format($listing->seller_price, 2) }}</div>
                                        <div><small class="text-muted">Selling:</small> ₹{{ number_format($listing->selling_price, 2) }}</div>
                                        <div><small class="text-muted">MRP:</small> ₹{{ number_format($listing->mrp, 2) }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $listing->stock_quantity > 10 ? 'bg-success' : ($listing->stock_quantity > 0 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                            {{ $listing->stock_quantity }} units
                                        </span>
                                    </td>
                                    <td>
                                        @if($listing->status === 'Active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($listing->status === 'Archived')
                                            <span class="badge bg-secondary">Archived</span>
                                        @else
                                            <span class="badge bg-warning text-dark">{{ $listing->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $prodStatus = $listing->product->status ?? 'Active';
                                        @endphp
                                        @if(in_array($prodStatus, ['Active', '1', 1]))
                                            <span class="text-success small"><i class="fa-solid fa-circle-check me-1"></i>Active</span>
                                        @else
                                            <span class="text-danger small"><i class="fa-solid fa-circle-xmark me-1"></i>{{ $prodStatus }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            @if($listing->status === 'Active')
                                                <button type="button" class="btn btn-outline-warning" title="Deactivate" onclick="previewSingle({{ $listing->id }}, 'status_update', {status: 'Inactive'})">
                                                    <i class="fa-solid fa-pause"></i>
                                                </button>
                                            @elseif($listing->status === 'Archived')
                                                <button type="button" class="btn btn-outline-secondary" title="Restore" onclick="previewSingle({{ $listing->id }}, 'restore', {})">
                                                    <i class="fa-solid fa-arrow-rotate-left"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-outline-success" title="Activate" onclick="previewSingle({{ $listing->id }}, 'status_update', {status: 'Active'})">
                                                    <i class="fa-solid fa-play"></i>
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-outline-danger" title="Archive" onclick="previewSingle({{ $listing->id }}, 'archive', {})">
                                                <i class="fa-solid fa-box-archive"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-box-open font-30 mb-2 d-block"></i>
                                        No product listings found matching the applied criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($listings->hasPages())
                <div class="card-footer bg-white border-top-0 d-flex justify-content-end py-3">
                    {{ $listings->links() }}
                </div>
            @endif
        </div>

        <!-- Recent Operations Log Card -->
        @if($recentOperations->isNotEmpty())
            <div class="card mt-4">
                <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 text-muted"><i class="fa-solid fa-clock-rotate-left me-2"></i>Recent Bulk Operations</h6>
                    <a href="{{ route('seller.catalogue.bulk.history') }}" class="small">View Full History</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Operation ID</th>
                                    <th>Type</th>
                                    <th>Scope</th>
                                    <th>Counts</th>
                                    <th>Status</th>
                                    <th>Initiated By</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOperations as $op)
                                    <tr>
                                        <td><strong>#{{ $op->id }}</strong></td>
                                        <td><span class="badge bg-light text-dark border">{{ strtoupper(str_replace('_', ' ', $op->operation_type)) }}</span></td>
                                        <td><small class="text-muted">{{ strtoupper($op->selection_mode) }} ({{ $op->total_matching }})</small></td>
                                        <td>
                                            <span class="text-success small fw-bold">{{ $op->success_count }} succeeded</span>,
                                            <span class="text-danger small">{{ $op->failed_count }} failed</span>
                                        </td>
                                        <td>
                                            @if($op->status === 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($op->status === 'processing')
                                                <span class="badge bg-info text-dark">Processing</span>
                                            @elseif($op->status === 'previewed')
                                                <span class="badge bg-warning text-dark">Previewed</span>
                                            @else
                                                <span class="badge bg-danger">{{ $op->status }}</span>
                                            @endif
                                        </td>
                                        <td><small>{{ $op->user->name ?? 'User #'.$op->user_id }}</small></td>
                                        <td><small class="text-muted">{{ $op->created_at->diffForHumans() }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- 1. Bulk Status Modal -->
<div class="modal fade" id="bulkStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-power-off me-2 text-primary"></i>Bulk Status Change</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Select the target status for all eligible items. A Stage 1 diagnostic preview will be generated before any records are committed.</p>
                <div class="mb-3">
                    <label class="form-label font-weight-bold">Target Status <span class="text-danger">*</span></label>
                    <select id="modalStatusSelect" class="form-select">
                        <option value="Active">Active (Publish live to marketplace)</option>
                        <option value="Inactive">Inactive (Deactivate from marketplace)</option>
                        <option value="Archived">Archived (Move to archive)</option>
                    </select>
                </div>
                <div class="alert alert-info py-2 small mb-0">
                    <i class="fa-solid fa-info-circle me-1"></i> Note: Activating listings requires pricing > 0 and an active parent product.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitStatusModal()">
                    <i class="fa-solid fa-eye me-1"></i> Generate Preview
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 2. Bulk Pricing Modal -->
<div class="modal fade" id="bulkPriceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-tags me-2 text-success"></i>Bulk Price Adjustment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Adjust seller prices across selected items. Selling price and commissions are automatically recalibrated.</p>
                <div class="mb-3">
                    <label class="form-label font-weight-bold">Adjustment Type</label>
                    <select id="modalPriceMode" class="form-select" onchange="togglePriceFields()">
                        <option value="percentage_increase">Percentage Increase (+ %)</option>
                        <option value="percentage_decrease">Percentage Decrease (- %)</option>
                        <option value="fixed_set">Set Fixed Seller Price (₹)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label font-weight-bold" id="modalPriceValueLabel">Percentage Value (%) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" class="form-control" id="modalPriceValue" placeholder="e.g. 5.00">
                </div>
                <div class="mb-3">
                    <label class="form-label font-weight-bold">MRP Adjustment (Optional)</label>
                    <input type="number" step="0.01" min="0" class="form-control" id="modalMrpValue" placeholder="Optional fixed MRP or leave blank to keep existing">
                    <small class="text-muted">MRP must always be greater than or equal to the seller price.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="submitPriceModal()">
                    <i class="fa-solid fa-eye me-1"></i> Generate Preview
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 3. Bulk Stock Modal -->
<div class="modal fade" id="bulkStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-boxes-stacked me-2 text-warning"></i>Bulk Stock Adjustment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label font-weight-bold">Adjustment Mode</label>
                    <select id="modalStockMode" class="form-select">
                        <option value="set">Set Fixed Stock Quantity</option>
                        <option value="increase">Add Stock (+ units)</option>
                        <option value="decrease">Reduce Stock (- units)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label font-weight-bold">Quantity (Units) <span class="text-danger">*</span></label>
                    <input type="number" min="0" step="1" class="form-control" id="modalStockQuantity" placeholder="e.g. 50">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="submitStockModal()">
                    <i class="fa-solid fa-eye me-1"></i> Generate Preview
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 4. Bulk Order Configuration Modal -->
<div class="modal fade" id="bulkOrderConfigModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-sliders me-2 text-info"></i>Bulk Order & MOQ Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label font-weight-bold">Minimum Order Quantity (MOQ)</label>
                    <input type="number" min="1" step="1" class="form-control" id="modalMoq" placeholder="Leave blank to preserve existing">
                </div>
                <div class="mb-3">
                    <label class="form-label font-weight-bold">Maximum Order Quantity</label>
                    <input type="number" min="1" step="1" class="form-control" id="modalMaxOrderQty" placeholder="Leave blank to preserve existing">
                </div>
                <div class="mb-3">
                    <label class="form-label font-weight-bold">Dispatch Lead Time (Days)</label>
                    <input type="number" min="0" step="1" class="form-control" id="modalLeadTime" placeholder="e.g. 2">
                </div>
                <div class="mb-3">
                    <label class="form-label font-weight-bold">Allow Backorders?</label>
                    <select id="modalAllowBackorders" class="form-select">
                        <option value="">Keep Existing</option>
                        <option value="0">No (Only sell on-hand stock)</option>
                        <option value="1">Yes (Allow orders when out of stock)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info" onclick="submitOrderConfigModal()">
                    <i class="fa-solid fa-eye me-1"></i> Generate Preview
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Selection state management
    const checkAll = document.getElementById('checkAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const selectionBadge = document.getElementById('selectionCountBadge');

    function getSelectedIds() {
        const ids = [];
        rowCheckboxes.forEach(cb => {
            if (cb.checked) ids.push(cb.value);
        });
        return ids;
    }

    function updateSelectionCount() {
        const count = getSelectedIds().length;
        selectionBadge.textContent = `${count} items selected`;
    }

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            rowCheckboxes.forEach(cb => cb.checked = this.checked);
            updateSelectionCount();
        });
    }

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectionCount);
    });

    function getSelectionMode() {
        const radio = document.querySelector('input[name="scopeChoice"]:checked');
        return radio ? radio.value : 'ids';
    }

    function validateSelectionOrFilter() {
        const mode = getSelectionMode();
        if (mode === 'ids') {
            const ids = getSelectedIds();
            if (ids.length === 0) {
                alert('Please select at least one item from the table or choose "All matching active filters".');
                return null;
            }
            return ids;
        }
        return true;
    }

    function populateAndSubmit(opType, payload) {
        const masterForm = document.getElementById('masterBulkForm');
        const mode = getSelectionMode();
        const ids = mode === 'ids' ? getSelectedIds() : [];

        document.getElementById('masterOperationType').value = opType;
        document.getElementById('masterSelectionMode').value = mode;
        document.getElementById('masterSelectedIds').value = ids.join(',');

        const container = document.getElementById('dynamicPayloadContainer');
        container.innerHTML = '';

        for (const [key, val] of Object.entries(payload)) {
            if (val !== null && val !== undefined && val !== '') {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = `update_payload[${key}]`;
                hiddenInput.value = val;
                container.appendChild(hiddenInput);
            }
        }

        masterForm.submit();
    }

    // Direct Operations (Archive, Restore)
    function triggerDirectOperation(opType) {
        if (!validateSelectionOrFilter()) return;
        const opName = opType.toUpperCase();
        if (!confirm(`Are you sure you want to generate a preview to ${opName} the selected items?`)) return;
        populateAndSubmit(opType, {});
    }

    // Preview Single Item Shortcut
    function previewSingle(id, opType, payload) {
        const masterForm = document.getElementById('masterBulkForm');
        document.getElementById('masterOperationType').value = opType;
        document.getElementById('masterSelectionMode').value = 'ids';
        document.getElementById('masterSelectedIds').value = id.toString();

        const container = document.getElementById('dynamicPayloadContainer');
        container.innerHTML = '';
        for (const [key, val] of Object.entries(payload)) {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = `update_payload[${key}]`;
            hiddenInput.value = val;
            container.appendChild(hiddenInput);
        }

        masterForm.submit();
    }

    // Modal Submissions
    function submitStatusModal() {
        if (!validateSelectionOrFilter()) return;
        const targetStatus = document.getElementById('modalStatusSelect').value;
        const opType = targetStatus === 'Archived' ? 'archive' : 'status_update';
        populateAndSubmit(opType, { status: targetStatus });
    }

    function togglePriceFields() {
        const mode = document.getElementById('modalPriceMode').value;
        const label = document.getElementById('modalPriceValueLabel');
        if (mode === 'fixed_set') {
            label.innerHTML = 'Fixed Seller Price (₹) <span class="text-danger">*</span>';
        } else {
            label.innerHTML = 'Percentage Value (%) <span class="text-danger">*</span>';
        }
    }

    function submitPriceModal() {
        if (!validateSelectionOrFilter()) return;
        const mode = document.getElementById('modalPriceMode').value;
        const val = parseFloat(document.getElementById('modalPriceValue').value);
        const mrp = document.getElementById('modalMrpValue').value;

        if (isNaN(val) || val <= 0) {
            alert('Please enter a valid positive numeric value.');
            return;
        }

        const payload = {};
        if (mode === 'percentage_increase') {
            payload.price_adjustment_type = 'percentage_increase';
            payload.price_adjustment_value = val;
        } else if (mode === 'percentage_decrease') {
            payload.price_adjustment_type = 'percentage_decrease';
            payload.price_adjustment_value = val;
        } else {
            payload.seller_price = val;
        }

        if (mrp && !isNaN(parseFloat(mrp))) {
            payload.mrp = parseFloat(mrp);
        }

        populateAndSubmit('field_update', payload);
    }

    function submitStockModal() {
        if (!validateSelectionOrFilter()) return;
        const mode = document.getElementById('modalStockMode').value;
        const qty = parseInt(document.getElementById('modalStockQuantity').value, 10);

        if (isNaN(qty) || qty < 0) {
            alert('Please enter a valid stock quantity.');
            return;
        }

        const payload = {
            stock_adjustment_type: mode,
            stock_quantity: qty
        };

        populateAndSubmit('field_update', payload);
    }

    function submitOrderConfigModal() {
        if (!validateSelectionOrFilter()) return;
        const moq = document.getElementById('modalMoq').value;
        const maxQty = document.getElementById('modalMaxOrderQty').value;
        const lead = document.getElementById('modalLeadTime').value;
        const backorders = document.getElementById('modalAllowBackorders').value;

        const payload = {};
        if (moq) payload.min_order_qty = parseInt(moq, 10);
        if (maxQty) payload.max_order_qty = parseInt(maxQty, 10);
        if (lead) payload.dispatch_lead_time_days = parseInt(lead, 10);
        if (backorders !== '') payload.allow_backorders = parseInt(backorders, 10);

        if (Object.keys(payload).length === 0) {
            alert('Please configure at least one field to update.');
            return;
        }

        populateAndSubmit('field_update', payload);
    }
</script>
@endpush
@endsection