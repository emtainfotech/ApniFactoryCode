@extends('layout')
@section('title', $title)
@section('content')
@php
    $effectiveCommission = isset($commissionRate) ? (float)$commissionRate : 25.00;
    $catName = isset($category) && $category ? $category->name : 'General Category';
    $mainCatName = isset($mainCategory) && $mainCategory ? $mainCategory->name : '';
    $defaultTax = isset($formdata['tax']) ? (float)$formdata['tax'] : 18.00;

    // Extract unique pack sizes and unique shades from Step 1 attributes
    $selectedPackMap = [];
    $selectedShadeMap = [];

    if (!empty($formdata['attributes']) && is_array($formdata['attributes'])) {
        foreach ($formdata['attributes'] as $attrStr) {
            $parts = explode('-&-', $attrStr);
            $pId = (int)($parts[0] ?? 0);
            $cId = (int)($parts[1] ?? 0);
            if ($pId && !isset($selectedPackMap[$pId])) {
                $selectedPackMap[$pId] = Helper::getboxpackingnamebyid($pId);
            }
            if ($cId && !isset($selectedShadeMap[$cId])) {
                $cName = Helper::getcolorsnamebyid($cId);
                $sc = \App\Models\ShadeCard::find($cId);
                $selectedShadeMap[$cId] = [
                    'id'      => $cId,
                    'name'    => $cName ?: "Shade #{$cId}",
                    'hexcode' => $sc?->hexcode ?? '#6c757d',
                ];
            }
        }
    }
@endphp

<style>
    .shade-dot {
        height: 14px;
        width: 14px;
        border: 1px solid rgba(0,0,0,0.2);
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
        vertical-align: middle;
    }
    .group-card {
        border-left: 4px solid #0d6efd;
        transition: all 0.2s ease-in-out;
    }
    .group-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .tier-badge {
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .shade-pill {
        display: inline-flex;
        align-items: center;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 20px;
        padding: 3px 10px;
        margin: 2px 4px 2px 0;
        font-size: 0.8rem;
    }
    .shade-pill .remove-shade {
        cursor: pointer;
        margin-left: 6px;
        color: #dc3545;
        font-weight: bold;
    }
    .display-price-blocked {
        background-color: #e9ecef !important;
        cursor: not-allowed !important;
        color: #495057 !important;
        font-weight: 700 !important;
    }
</style>

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
                        <li class="breadcrumb-item active" aria-current="page">Step 2: Shade Groups & Pricing (Asian Paints Style)</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <div>
                        <h4 class="mb-1 text-dark fw-bold">
                            <i class="fa-solid fa-layer-group text-primary me-2"></i>Configure Shade Groups Pricing
                        </h4>
                        <p class="text-muted mb-0 small">
                            All groups checkmarked on Step 1 are shown below. Enter Factory Price per group & pack size. Display price is locked/blocked from manual editing and calculated automatically.
                        </p>
                    </div>
                    <div class="text-end mt-2 mt-md-0 d-flex align-items-center gap-2">
                        <span class="badge bg-light-primary text-primary px-3 py-2 fs-6 rounded-pill border border-primary">
                            <i class="fa-solid fa-percent me-1"></i> Category Commission: <strong>{{ number_format($effectiveCommission, 2) }}%</strong>
                        </span>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="addNewShadeGroup()">
                            <i class="fa-solid fa-plus me-1"></i> + Add Another Group
                        </button>
                    </div>
                </div>

                <!-- Info Alert Banner -->
                <div class="alert alert-info border-0 bg-light-info text-dark alert-dismissible fade show mb-4" role="alert">
                    <div class="d-flex align-items-start">
                        <div class="fs-4 text-info me-3 mt-1"><i class="fa-solid fa-circle-info"></i></div>
                        <div class="small">
                            <strong>Pricing & Admin Review Guidelines:</strong>
                            <ul class="mb-0 mt-1 ps-3">
                                <li><strong>Factory Price:</strong> Base manufacturing rate you receive for all shades in this group.</li>
                                <li><strong>Display Price (Blocked / Non-Editable):</strong> Display price is strictly locked and cannot be edited. It calculates automatically as <code>Factory Price + {{ number_format($effectiveCommission, 2) }}%</code> commission.</li>
                                <li><strong>GST Included:</strong> If GST is included, specify <strong>How much GST (%)</strong> (e.g. 18%, 28%, 12%, 5%). Products with GST included land in the Admin Review Bucket for approval.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <form method="post" action="{{ route('product.store') }}" enctype="multipart/form-data" id="productPricingForm" onsubmit="return handleFormSubmit(event)">
                    @csrf
                    <input type="hidden" name="previousdata" value="{{ json_encode($formdata) }}">
                    <input type="hidden" id="categoryCommissionRate" value="{{ $effectiveCommission }}">
                    <input type="hidden" name="shade_groups_data" id="shadeGroupsDataInput" value="">

                    <!-- Flat attributes container (populated by JS on submit for backward compatibility) -->
                    <div id="hiddenFlatAttributesContainer"></div>

                    <!-- Image Upload Box -->
                    <div class="card border mb-4">
                        <div class="card-header bg-light py-2 fw-semibold d-flex justify-content-between align-items-center">
                            <span><i class="fa-solid fa-images text-secondary me-2"></i>Product Gallery Images</span>
                            <span class="badge bg-secondary">Upload product & packing photos</span>
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

                    <!-- Container for Shade Groups -->
                    <div id="shadeGroupsContainer" class="space-y-4">
                        <!-- Rendered dynamically by JavaScript -->
                    </div>

                    <!-- Bottom Bar / Submit Actions -->
                    <div class="card border mt-4">
                        <div class="card-footer bg-white p-3">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                <div>
                                    <span id="gstGlobalNoticePill" class="badge bg-light-secondary text-secondary px-3 py-2 border">
                                        <i class="fa-solid fa-shield-halved me-1"></i> No GST-inclusive groups selected. Direct publishing enabled.
                                    </span>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ url('seller/product/add') }}" class="btn btn-outline-secondary">
                                        <i class="fa-solid fa-arrow-left me-1"></i> Back
                                    </a>
                                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Product & Pricing
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
// State Management for Shade Groups
const allShades = @json(array_values($selectedShadeMap));
const allPacks = @json($selectedPackMap); // { pack_id: "Pack Name" }
const packIds = Object.keys(allPacks);
const commissionRate = parseFloat("{{ $effectiveCommission }}") || 25.00;
const defaultTaxRate = parseFloat("{{ $defaultTax }}") || 18.00;

// Groups incoming from Step 1 checkmarked groups
const step1Incoming = @json($step1Groups ?? []);

let groups = [];

if (step1Incoming && step1Incoming.length > 0) {
    groups = step1Incoming.map((g, idx) => {
        const packsMap = {};
        const gPackIds = (g.pack_ids && g.pack_ids.length > 0) ? g.pack_ids.map(String) : packIds;
        gPackIds.forEach(pid => {
            packsMap[pid] = { seller_price: '', price: '', mrp: '' };
        });

        return {
            id: g.id || ('group_' + (idx + 1)),
            name: g.name || ('Group ' + (idx + 1)),
            shades: (g.shades || []).map(Number),
            pack_ids: gPackIds.map(Number),
            is_gst_included: false,
            gst_rate: defaultTaxRate,
            packs: packsMap
        };
    });
} else {
    // Default fallback: 1 group with all selected shades & packs
    const defaultPacks = {};
    packIds.forEach(pid => {
        defaultPacks[pid] = { seller_price: '', price: '', mrp: '' };
    });

    groups = [
        {
            id: 'group_1',
            name: 'Group 1',
            shades: allShades.map(s => s.id),
            pack_ids: packIds.map(Number),
            is_gst_included: false,
            gst_rate: defaultTaxRate,
            packs: defaultPacks
        }
    ];
}

// Helper: Get shades assigned to any group EXCEPT the given groupId
function getAssignedShadeIds(excludeGroupId = null) {
    const assigned = new Set();
    groups.forEach(g => {
        if (!excludeGroupId || g.id !== excludeGroupId) {
            (g.shades || []).forEach(sid => assigned.add(parseInt(sid)));
        }
    });
    return assigned;
}

// Helper: Calculate Display Price strictly from factory price
function calcDisplayPrice(factoryPrice) {
    const val = parseFloat(factoryPrice);
    if (isNaN(val) || val <= 0) return '';
    return (val * (1 + (commissionRate / 100))).toFixed(2);
}

// Render All Groups
function renderGroups() {
    const container = document.getElementById('shadeGroupsContainer');
    if (!container) return;

    let html = '';
    let hasAnyGst = false;

    groups.forEach((group, gIdx) => {
        if (group.is_gst_included) hasAnyGst = true;

        const groupShades = allShades.filter(s => group.shades.includes(s.id));
        const otherAssigned = getAssignedShadeIds(group.id);
        const availableToAdd = allShades.filter(s => !otherAssigned.has(s.id) && !group.shades.includes(s.id));
        const groupPackIds = (group.pack_ids && group.pack_ids.length > 0) ? group.pack_ids.map(String) : packIds;

        html += `
        <div class="card border group-card mb-4 shadow-sm" id="card_${group.id}">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary px-2 py-1 fs-6">
                            <i class="fa-solid fa-layer-group me-1"></i> #${gIdx + 1}
                        </span>
                        <div class="input-group input-group-sm" style="max-width: 280px;">
                            <span class="input-group-text fw-bold bg-light">Group Name:</span>
                            <input type="text" class="form-control fw-bold text-dark" value="${escapeHtml(group.name)}" 
                                onchange="updateGroupName('${group.id}', this.value)" placeholder="e.g. Gr-1, Whites, Standard..." required>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center gap-3">
                        <!-- GST Included Checkbox & Rate Input -->
                        <div class="d-flex align-items-center gap-2 bg-light px-3 py-1.5 rounded border">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="gst_${group.id}" 
                                    ${group.is_gst_included ? 'checked' : ''} onchange="toggleGroupGst('${group.id}', this.checked)">
                                <label class="form-check-label small fw-bold text-dark" for="gst_${group.id}">
                                    GST Included?
                                </label>
                            </div>
                            
                            <div id="gstRateBox_${group.id}" class="d-flex align-items-center gap-1 ms-2" style="${group.is_gst_included ? '' : 'display: none !important;'}">
                                <label class="small text-muted mb-0">Rate:</label>
                                <div class="input-group input-group-sm" style="width: 85px;">
                                    <input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm text-center fw-bold" 
                                        value="${group.gst_rate || defaultTaxRate}" onchange="updateGroupGstRate('${group.id}', this.value)">
                                    <span class="input-group-text p-1">%</span>
                                </div>
                            </div>
                        </div>

                        ${groups.length > 1 ? `
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeGroup('${group.id}')" title="Delete Group">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>

                <!-- GST Notice Banner if checked -->
                ${group.is_gst_included ? `
                    <div class="alert alert-warning py-1 px-3 mt-2 mb-0 small border-warning d-flex align-items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-warning"></i>
                        <span><strong>GST Included:</strong> All packings in this group will be routed to Admin Review Bucket for Display Price approval with <strong>${group.gst_rate}%</strong> GST.</span>
                    </div>
                ` : ''}

                <!-- Assigned Shades Chips Container -->
                <div class="mt-3 pt-2 border-top">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                        <div class="small fw-bold text-secondary">
                            <i class="fa-solid fa-palette me-1 text-primary"></i> Assigned Shades (${groupShades.length}):
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            ${availableToAdd.length > 0 ? `
                                <div class="dropdown">
                                    <button class="btn btn-outline-primary btn-sm dropdown-toggle py-0 px-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.78rem;">
                                        <i class="fa-solid fa-plus me-1"></i> Add Shade (${availableToAdd.length} Available)
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow" style="max-height: 240px; overflow-y: auto;">
                                        <li><h6 class="dropdown-header">Select shade to assign</h6></li>
                                        ${availableToAdd.map(s => `
                                            <li>
                                                <a class="dropdown-item d-flex align-items-center small py-1" href="javascript:;" onclick="addShadeToGroup('${group.id}', ${s.id})">
                                                    <span class="shade-dot" style="background-color: ${s.hexcode};"></span>
                                                    ${s.name}
                                                </a>
                                            </li>
                                        `).join('')}
                                    </ul>
                                </div>
                            ` : `
                                <span class="badge bg-light text-muted border" style="font-size: 0.72rem;">All shades assigned</span>
                            `}
                        </div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center">
                        ${groupShades.length > 0 ? groupShades.map(s => `
                            <span class="shade-pill">
                                <span class="shade-dot" style="background-color: ${s.hexcode};"></span>
                                ${s.name}
                                <span class="remove-shade" onclick="removeShadeFromGroup('${group.id}', ${s.id})" title="Remove shade from group">&times;</span>
                            </span>
                        `).join('') : `
                            <span class="text-danger small fst-italic py-1">No shades assigned to this group yet. Use '+ Add Shade' above.</span>
                        `}
                    </div>
                </div>
            </div>

            <!-- Rate Matrix Table (Asian Paints Rate Card Style) -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 text-center" style="width: 100%;">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 220px; text-align: left;" class="ps-3">Pricing Tier</th>
                                ${groupPackIds.map(pid => `
                                    <th style="min-width: 120px;">
                                        <div class="fw-bold">${escapeHtml(allPacks[pid] || 'Pack #' + pid)}</div>
                                    </th>
                                `).join('')}
                            </tr>
                        </thead>
                        <tbody>
                            <!-- ROW 1: Factory Price (₹) -->
                            <tr class="table-light">
                                <td class="text-start ps-3 fw-bold text-dark">
                                    <span class="badge bg-secondary me-1 tier-badge">1. Factory Price</span>
                                    <div class="small text-muted fw-normal">Base manufacturing rate you receive</div>
                                </td>
                                ${groupPackIds.map(pid => {
                                    const pVal = group.packs[pid] ? group.packs[pid].seller_price : '';
                                    return `
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end fw-semibold factory-input" 
                                                    value="${pVal !== undefined ? pVal : ''}" 
                                                    placeholder="0.00" 
                                                    oninput="handleFactoryPriceChange('${group.id}', '${pid}', this.value)" required>
                                            </div>
                                        </td>
                                    `;
                                }).join('')}
                            </tr>

                            <!-- ROW 2: Display Price (Auto-calculated, BLOCKED & NOT EDITABLE) -->
                            <tr style="background-color: #f8f9fa;">
                                <td class="text-start ps-3 fw-bold text-primary">
                                    <span class="badge bg-primary me-1 tier-badge">2. Display Price</span>
                                    <span class="badge bg-light text-secondary border ms-1" style="font-size:0.68rem;"><i class="fa-solid fa-lock me-1"></i>Blocked</span>
                                    <div class="small text-muted fw-normal">+${commissionRate}% comm. (Auto-calculated)</div>
                                </td>
                                ${groupPackIds.map(pid => {
                                    const dVal = group.packs[pid] ? group.packs[pid].price : '';
                                    return `
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light text-muted fw-bold">₹</span>
                                                <input type="number" step="0.01" class="form-control form-control-sm text-end fw-bold text-dark display-price-blocked" 
                                                    id="disp_${group.id}_${pid}"
                                                    value="${dVal !== undefined ? dVal : ''}" 
                                                    placeholder="Auto" 
                                                    readonly 
                                                    tabindex="-1"
                                                    required>
                                            </div>
                                        </td>
                                    `;
                                }).join('')}
                            </tr>

                            <!-- ROW 3: MRP Price (₹) Optional -->
                            <tr>
                                <td class="text-start ps-3 text-muted">
                                    <span class="badge bg-light text-dark border me-1 tier-badge">3. MRP Price</span>
                                    <div class="small text-muted fw-normal">Maximum Retail Price (Optional)</div>
                                </td>
                                ${groupPackIds.map(pid => {
                                    const mVal = group.packs[pid] ? group.packs[pid].mrp : '';
                                    return `
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end text-muted" 
                                                    value="${mVal !== undefined && mVal !== null ? mVal : ''}" 
                                                    placeholder="MRP" 
                                                    oninput="handleMrpChange('${group.id}', '${pid}', this.value)">
                                            </div>
                                        </td>
                                    `;
                                }).join('')}
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        `;
    });

    container.innerHTML = html;

    // Update global GST notice pill
    const globalNoticePill = document.getElementById('gstGlobalNoticePill');
    if (globalNoticePill) {
        if (hasAnyGst) {
            globalNoticePill.className = 'badge bg-light-warning text-dark px-3 py-2 border border-warning';
            globalNoticePill.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> <strong>GST Included marked in groups:</strong> Product will be sent to Admin Review Bucket for display price approval.';
        } else {
            globalNoticePill.className = 'badge bg-light-secondary text-secondary px-3 py-2 border';
            globalNoticePill.innerHTML = '<i class="fa-solid fa-shield-halved me-1"></i> No GST-inclusive groups selected. Direct publishing enabled.';
        }
    }
}

// Group Actions
function addNewShadeGroup() {
    const newId = 'group_' + Date.now();
    const newName = 'Group ' + (groups.length + 1);

    const packsMap = {};
    packIds.forEach(pid => {
        packsMap[pid] = { seller_price: '', price: '', mrp: '' };
    });

    const assigned = getAssignedShadeIds(null);
    const unassignedShades = allShades.filter(s => !assigned.has(s.id)).map(s => s.id);

    groups.push({
        id: newId,
        name: newName,
        shades: unassignedShades,
        pack_ids: packIds.map(Number),
        is_gst_included: false,
        gst_rate: defaultTaxRate,
        packs: packsMap
    });

    renderGroups();
}

function removeGroup(groupId) {
    if (groups.length <= 1) {
        alert('At least one shade group must exist.');
        return;
    }
    const targetGroup = groups.find(g => g.id === groupId);
    if (!targetGroup) return;

    if (!confirm(`Are you sure you want to delete '${targetGroup.name}'? Its assigned shades will become unassigned.`)) {
        return;
    }

    groups = groups.filter(g => g.id !== groupId);
    renderGroups();
}

function updateGroupName(groupId, newName) {
    const grp = groups.find(g => g.id === groupId);
    if (grp) {
        grp.name = newName.trim() || 'Untitled Group';
    }
}

function toggleGroupGst(groupId, isChecked) {
    const grp = groups.find(g => g.id === groupId);
    if (grp) {
        grp.is_gst_included = isChecked;
        const rateBox = document.getElementById('gstRateBox_' + groupId);
        if (rateBox) {
            rateBox.style.display = isChecked ? 'flex' : 'none';
        }
        renderGroups();
    }
}

function updateGroupGstRate(groupId, rateVal) {
    const grp = groups.find(g => g.id === groupId);
    if (grp) {
        grp.gst_rate = parseFloat(rateVal) || defaultTaxRate;
    }
}

function addShadeToGroup(groupId, shadeId) {
    shadeId = parseInt(shadeId);
    groups.forEach(g => {
        g.shades = g.shades.filter(sid => parseInt(sid) !== shadeId);
    });

    const targetGrp = groups.find(g => g.id === groupId);
    if (targetGrp && !targetGrp.shades.includes(shadeId)) {
        targetGrp.shades.push(shadeId);
    }
    renderGroups();
}

function removeShadeFromGroup(groupId, shadeId) {
    shadeId = parseInt(shadeId);
    const targetGrp = groups.find(g => g.id === groupId);
    if (targetGrp) {
        targetGrp.shades = targetGrp.shades.filter(sid => parseInt(sid) !== shadeId);
    }
    renderGroups();
}

// Pricing Input Handlers: Auto-calculates blocked display price
function handleFactoryPriceChange(groupId, packId, val) {
    const grp = groups.find(g => g.id === groupId);
    if (!grp) return;

    if (!grp.packs[packId]) {
        grp.packs[packId] = { seller_price: '', price: '', mrp: '' };
    }
    grp.packs[packId].seller_price = val;

    // Auto-calculate Display Price directly below Factory Price (Locked / Non-editable)
    const displayVal = calcDisplayPrice(val);
    grp.packs[packId].price = displayVal;

    const dispInput = document.getElementById(`disp_${groupId}_${packId}`);
    if (dispInput) {
        dispInput.value = displayVal;
    }
}

function handleMrpChange(groupId, packId, val) {
    const grp = groups.find(g => g.id === groupId);
    if (grp && grp.packs[packId]) {
        grp.packs[packId].mrp = val;
    }
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Form Submission Handler
function handleFormSubmit(event) {
    let hasError = false;
    let errorMessage = '';

    groups.forEach(grp => {
        if (grp.shades.length > 0) {
            const gPackIds = (grp.pack_ids && grp.pack_ids.length > 0) ? grp.pack_ids.map(String) : packIds;
            gPackIds.forEach(pid => {
                const sp = grp.packs[pid] ? grp.packs[pid].seller_price : '';
                if (!sp || parseFloat(sp) <= 0) {
                    hasError = true;
                    errorMessage = `Please enter a valid Factory Price for '${allPacks[pid] || 'Pack #' + pid}' in '${grp.name}'.`;
                }
            });
        }
    });

    if (hasError) {
        alert(errorMessage);
        return false;
    }

    // 2. Serialize Groups JSON into shade_groups_data
    const cleanGroups = groups.map(g => ({
        group_id: g.id,
        name: g.name,
        shades: g.shades,
        pack_ids: g.pack_ids,
        is_gst_included: g.is_gst_included ? 1 : 0,
        gst_rate: g.gst_rate || defaultTaxRate,
        packs: g.packs
    }));

    document.getElementById('shadeGroupsDataInput').value = JSON.stringify(cleanGroups);

    // 3. Generate flat attributes for backward compatibility
    const flatContainer = document.getElementById('hiddenFlatAttributesContainer');
    flatContainer.innerHTML = '';

    cleanGroups.forEach(grp => {
        const isGst = grp.is_gst_included ? 1 : 0;
        const gstRate = grp.gst_rate;
        const gName = grp.name;
        const gPackIds = (grp.pack_ids && grp.pack_ids.length > 0) ? grp.pack_ids.map(String) : packIds;

        grp.shades.forEach(shadeId => {
            gPackIds.forEach(packId => {
                const pData = grp.packs[packId] || {};
                const sPrice = pData.seller_price || 0;
                const dPrice = pData.price || calcDisplayPrice(sPrice);
                const mrp = pData.mrp || '';

                flatContainer.innerHTML += `
                    <input type="hidden" name="boxpacking[]" value="${packId}">
                    <input type="hidden" name="color[]" value="${shadeId}">
                    <input type="hidden" name="seller_price[]" value="${sPrice}">
                    <input type="hidden" name="price[]" value="${dPrice}">
                    <input type="hidden" name="is_gst_included[]" value="${isGst}">
                    <input type="hidden" name="gst_rate[]" value="${gstRate}">
                    <input type="hidden" name="group_name[]" value="${escapeHtml(gName)}">
                    <input type="hidden" name="mrp[]" value="${mrp}">
                `;
            });
        });
    });

    return true;
}

// Initial render
document.addEventListener('DOMContentLoaded', function () {
    renderGroups();
});
</script>
@endsection
