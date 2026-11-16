@extends('layout')
@section('title',$title)
@section('content')
<style>
    .dot {
        height: 18px;
        width: 18px;
        border: 1px solid rgba(0,0,0,0.2);
        margin-right: 6px;
        border-radius: 50%;
        display: inline-block;
        vertical-align: middle;
    }
    div#shadecardbycategoryselected {
        max-height: 380px;
        overflow: auto;
    }
    tr.table-sticky {
        position: sticky;
        top: 0px;
        z-index: 111;
        background: #f9f9f9;
    }
    table.table.table-striped.table-bordered th {
        position: sticky;
        top: 0px;
        z-index: 111;
    }
    .table-col-sticky th {
        position: sticky;
        left: 0;
    }
    .step1-group-card {
        border-left: 4px solid #0d6efd;
        background: #ffffff;
        transition: all 0.2s ease-in-out;
    }
    .shade-check-chip {
        display: inline-flex;
        align-items: center;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 4px 10px;
        margin: 3px 4px 3px 0;
        font-size: 0.82rem;
        cursor: pointer;
        user-select: none;
    }
    .shade-check-chip:hover {
        background: #e9ecef;
    }
    .shade-check-chip.disabled {
        opacity: 0.55;
        cursor: not-allowed;
        background: #f1f3f5;
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
                        <li class="breadcrumb-item"><a href="javascript:;">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('seller/product') }}">Products</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h5 class="card-title fw-bold text-dark">Add New Product</h5>
                <hr/>
                <div class="form-body mt-4">
                    <div class="row">
                        <div class="col-lg-12">
                            <form method="post" enctype='multipart/form-data' id="productStep1Form" onsubmit="return validateStep1Form(event)">
                                @csrf
                                <input type="hidden" name="maincategory" id="maincategoryid" value="">
                                <input type="hidden" name="category" id="categoryid" value="">
                                <input type="hidden" name="selected_groups_json" id="selectedGroupsJsonInput" value="">
                                
                                <div id="hiddenAttributesContainer"></div>

                                <div class="border border-3 p-4 rounded bg-white">
                                    <div class="row">
                                        <div class="mb-3 col-md-10">
                                            <label class="form-label fw-semibold">Product Name</label>
                                            <input type="text" class="form-control" name="name" placeholder="Enter product title" value="{{ old('name', $step1Data['name'] ?? '') }}" required>
                                        </div>
                                        <div class="mb-3 col-md-2">
                                            <label class="form-label fw-semibold">Status</label>
                                            <select class="form-select" id="inputProductType" name="status">
                                                <option value="1" {{ old('status', $step1Data['status'] ?? '') == '1' ? 'selected' : '' }}>Active</option>
                                                <option value="0" {{ old('status', $step1Data['status'] ?? '') == '0' ? 'selected' : '' }}>Deactive</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="mb-3 col-md-4">
                                            <label for="brand" class="form-label fw-semibold">Product Brand</label>
                                            <select class="form-select" id="brand" name="brand" required>
                                                <option value="">Select Brand</option>
                                                @foreach(Helper::getbrand() as $bid)
                                                    <option value="{{$bid->id}}" {{ old('brand', $step1Data['brand'] ?? '') == $bid->id ? 'selected' : '' }}>{{$bid->name}} ({{ Helper::getcatname($bid->category_id) }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label fw-semibold">Main Category</label>
                                            <input type="text" class="form-control" id="maincategory_display" readonly placeholder="Auto-populated from Brand" style="background-color: #f8f9fa;">
                                        </div>
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label fw-semibold">Category</label>
                                            <input type="text" class="form-control" id="category_display" readonly placeholder="Auto-populated from Brand" style="background-color: #f8f9fa;">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label fw-semibold">Product HSN Number</label>
                                            <input type="text" class="form-control" name="hsncode" value="{{ old('hsncode', $step1Data['hsncode'] ?? '') }}" placeholder="Enter product HSN" required>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label fw-semibold d-block">Tax Rate (Default GST)</label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" id="tax5" name="tax" value="5" {{ old('tax', $step1Data['tax'] ?? '') == '5' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="tax5">5%</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" id="tax12" name="tax" value="12" {{ old('tax', $step1Data['tax'] ?? '') == '12' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="tax12">12%</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" id="tax18" name="tax" value="18" {{ old('tax', $step1Data['tax'] ?? '18') == '18' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="tax18">18%</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" id="tax28" name="tax" value="28" {{ old('tax', $step1Data['tax'] ?? '') == '28' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="tax28">28%</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Product Description</label>
                                        <div class="text-editor">
                                            <textarea name="details" class="nic-edit-p" rows="5" style="width:100%">{{ old('details', $step1Data['details'] ?? '') }}</textarea> 
                                        </div>
                                    </div>

                                    <!-- ======================================================== -->
                                    <!-- SHADE GROUP CREATOR SECTION (ASIAN PAINTS RATE CARD)     -->
                                    <!-- ======================================================== -->
                                    <div id="step1GroupSection" class="card border mb-4 shadow-sm" style="display: none;">
                                        <div class="card-header bg-light py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                                            <div>
                                                <h6 class="mb-1 fw-bold text-dark">
                                                    <i class="fa-solid fa-layer-group text-primary me-2"></i>Create & Configure Shade Groups (Asian Paints Style)
                                                </h6>
                                                <p class="text-muted small mb-0">
                                                    Create multiple shade groups (e.g. Gr-0 Whites, Gr-1 Standard, Gr-2 Accent). Checkmark the shades and pack sizes for each group, then proceed with the checkmarked groups to configure pricing.
                                                </p>
                                            </div>
                                            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" onclick="addNewGroupStep1()">
                                                <i class="fa-solid fa-plus me-1"></i> Create Group
                                            </button>
                                        </div>
                                        <div class="card-body p-3">
                                            <!-- Container for groups -->
                                            <div id="step1GroupsContainer" class="space-y-4">
                                                <!-- Dynamic groups rendered here -->
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Collapsible raw matrix table fallback -->
                                    <div class="mb-4" id="tableWrapperSection" style="display: none;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <a class="btn btn-link text-decoration-none p-0 text-muted small" data-bs-toggle="collapse" href="#collapseRawTable" role="button" aria-expanded="false">
                                                <i class="fa-solid fa-table-cells me-1"></i> Or view / edit full shade matrix table &raquo;
                                            </a>
                                        </div>
                                        <div class="collapse" id="collapseRawTable">
                                            <div class="table-responsive border rounded" id="shadecardbycategoryselected"></div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary py-2 fw-bold fs-6">
                                                PROCEED TO STEP 2: CONFIGURE PRICING &raquo;
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end page wrapper -->

<script>
let categoryShades = [];
let categoryPackings = [];
let step1Groups = [];
let activePickerGroupId = null;

$("document").ready(function(){
    $("#brand").change(function(){ 
        var bid = $(this).val();
        if (!bid) {
            $("#step1GroupSection").hide();
            $("#tableWrapperSection").hide();
            return;
        }

        var _token = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            type: 'POST',
            url: '../getmidcidbybid',
            data:{ bid: bid, _token: _token },
            success: function(response){
                let rawHtml = response.listdata; 
                $('#shadecardbycategoryselected').html(rawHtml);
                $("#maincategoryid").val(response.mid);
                $("#categoryid").val(response.cid);
                $("#maincategory_display").val(response.mid_name || 'Main Category #' + response.mid);
                $("#category_display").val(response.cid_name || 'Category #' + response.cid);

                // Store shades & packings arrays
                categoryShades = response.shades || [];
                categoryPackings = response.packings || [];

                // Initialize Group 1: By default, pack sizes and color shades are UNCHECKED
                step1Groups = [
                    {
                        id: 's1_grp_1',
                        name: 'Group 1',
                        enabled: true,
                        pack_ids: [],
                        shades: []
                    }
                ];
                activePickerGroupId = 's1_grp_1';

                renderStep1Groups();
                $("#step1GroupSection").show();
                $("#tableWrapperSection").show();
            }
        });
    });
});

// Helper: Get shades assigned to any group EXCEPT the given groupId
function getAssignedShadesAcrossGroups(excludeGroupId = null) {
    const assigned = new Map(); // shadeId => groupName
    step1Groups.forEach(g => {
        if (!excludeGroupId || g.id !== excludeGroupId) {
            (g.shades || []).forEach(sid => {
                assigned.set(parseInt(sid), g.name);
            });
        }
    });
    return assigned;
}

// "Create Group" action
function addNewGroupStep1() {
    const newId = 's1_grp_' + Date.now();
    const newName = 'Group ' + (step1Groups.length + 1);

    // Default unchecked sizes & shades for newly created group
    step1Groups.push({
        id: newId,
        name: newName,
        enabled: true,
        pack_ids: [],
        shades: []
    });

    activePickerGroupId = newId; // Open the shade picker for this newly created group
    renderStep1Groups();
}

function removeStep1Group(groupId) {
    if (step1Groups.length <= 1) {
        alert('At least one group must exist.');
        return;
    }
    const target = step1Groups.find(g => g.id === groupId);
    if (!target) return;

    if (!confirm(`Are you sure you want to remove '${target.name}'?`)) return;

    step1Groups = step1Groups.filter(g => g.id !== groupId);
    if (activePickerGroupId === groupId) activePickerGroupId = null;
    renderStep1Groups();
}

function updateStep1GroupName(groupId, nameVal) {
    const grp = step1Groups.find(g => g.id === groupId);
    if (grp) {
        grp.name = nameVal.trim() || 'Untitled Group';
    }
}

function toggleStep1GroupEnabled(groupId, isEnabled) {
    const grp = step1Groups.find(g => g.id === groupId);
    if (grp) {
        grp.enabled = isEnabled;
    }
}

function toggleStep1Pack(groupId, packId, isChecked) {
    packId = parseInt(packId);
    const grp = step1Groups.find(g => g.id === groupId);
    if (!grp) return;

    if (isChecked) {
        if (!grp.pack_ids.includes(packId)) grp.pack_ids.push(packId);
    } else {
        grp.pack_ids = grp.pack_ids.filter(pid => pid !== packId);
    }
}

function selectAllPacksForGroup(groupId) {
    const grp = step1Groups.find(g => g.id === groupId);
    if (grp) {
        grp.pack_ids = categoryPackings.map(p => p.id);
        renderStep1Groups();
    }
}

function clearAllPacksForGroup(groupId) {
    const grp = step1Groups.find(g => g.id === groupId);
    if (grp) {
        grp.pack_ids = [];
        renderStep1Groups();
    }
}

function toggleShadePicker(groupId) {
    activePickerGroupId = (activePickerGroupId === groupId ? null : groupId);
    renderStep1Groups();
}

function addShadeToGroup(groupId, shadeId) {
    shadeId = parseInt(shadeId);
    const grp = step1Groups.find(g => g.id === groupId);
    if (!grp) return;

    // Remove from any other group first (strict non-repetition)
    step1Groups.forEach(g => {
        if (g.id !== groupId) {
            g.shades = g.shades.filter(sid => parseInt(sid) !== shadeId);
        }
    });

    if (!grp.shades.includes(shadeId)) {
        grp.shades.push(shadeId);
    }
    renderStep1Groups();
}

function removeShadeFromGroup(groupId, shadeId) {
    shadeId = parseInt(shadeId);
    const grp = step1Groups.find(g => g.id === groupId);
    if (!grp) return;

    grp.shades = grp.shades.filter(sid => parseInt(sid) !== shadeId);
    renderStep1Groups();
}

function selectAllAvailableShadesForGroup(groupId) {
    const grp = step1Groups.find(g => g.id === groupId);
    if (!grp) return;

    const assignedToOthers = getAssignedShadesAcrossGroups(groupId);
    const available = categoryShades.filter(s => !assignedToOthers.has(s.id)).map(s => s.id);
    grp.shades = available;
    renderStep1Groups();
}

function clearAllShadesForGroup(groupId) {
    const grp = step1Groups.find(g => g.id === groupId);
    if (grp) {
        grp.shades = [];
        renderStep1Groups();
    }
}

function filterPickerShades(groupId, query) {
    const q = (query || '').toLowerCase().trim();
    const container = document.getElementById('picker_chips_' + groupId);
    if (!container) return;

    const chips = container.querySelectorAll('[data-shade-name]');
    chips.forEach(chip => {
        const name = chip.getAttribute('data-shade-name') || '';
        chip.style.display = (!q || name.includes(q)) ? 'inline-flex' : 'none';
    });
}

// Render Step 1 Group Cards
function renderStep1Groups() {
    const container = document.getElementById('step1GroupsContainer');
    if (!container) return;

    let html = '';

    step1Groups.forEach((group, gIdx) => {
        const assignedToOthers = getAssignedShadesAcrossGroups(group.id);

        // ONLY shades NOT in any other group are available to pick!
        const availableShades = categoryShades.filter(s => !assignedToOthers.has(s.id) && !group.shades.includes(s.id));

        // Shades included in this group
        const includedShades = categoryShades.filter(s => group.shades.includes(s.id));

        html += `
        <div class="card border step1-group-card mb-3 shadow-sm" id="card_${group.id}">
            <div class="card-header bg-white py-2 px-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="form-check me-2">
                        <input class="form-check-input" type="checkbox" id="chk_enabled_${group.id}" 
                            ${group.enabled ? 'checked' : ''} onchange="toggleStep1GroupEnabled('${group.id}', this.checked)">
                        <label class="form-check-label fw-bold text-dark small" for="chk_enabled_${group.id}">
                            Proceed with this Group
                        </label>
                    </div>

                    <span class="badge bg-primary px-2 py-1 fs-7">#${gIdx + 1}</span>

                    <div class="input-group input-group-sm" style="max-width: 260px;">
                        <span class="input-group-text fw-bold bg-light">Group Name:</span>
                        <input type="text" class="form-control fw-bold text-dark" value="${escapeHtml(group.name)}" 
                            placeholder="e.g. Gr-0 Whites, Gr-1 Deep..." onchange="updateStep1GroupName('${group.id}', this.value)" required>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border small">
                        ${group.shades.length} Shades &bull; ${group.pack_ids.length} Pack Sizes
                    </span>
                    ${step1Groups.length > 1 ? `
                        <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" onclick="removeStep1Group('${group.id}')" title="Delete Group">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    ` : ''}
                </div>
            </div>

            <div class="card-body p-3">
                <!-- 1. Checkmark Pack Sizes for this Group (Unchecked by default) -->
                <div class="mb-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-1">
                        <label class="small fw-bold text-dark mb-0">
                            <i class="fa-solid fa-box text-secondary me-1"></i>Select Pack Sizes for this Group:
                        </label>
                        <div>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none me-2" onclick="selectAllPacksForGroup('${group.id}')">Select All</button>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-danger" onclick="clearAllPacksForGroup('${group.id}')">Clear</button>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 p-2 bg-light rounded border">
                        ${categoryPackings.map(p => `
                            <div class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="checkbox" id="pack_${group.id}_${p.id}" 
                                    value="${p.id}" ${group.pack_ids.includes(p.id) ? 'checked' : ''} 
                                    onchange="toggleStep1Pack('${group.id}', ${p.id}, this.checked)">
                                <label class="form-check-label small fw-semibold" for="pack_${group.id}_${p.id}">
                                    ${escapeHtml(p.name)}
                                </label>
                            </div>
                        `).join('')}
                    </div>
                </div>

                <!-- 2. Color Shades in this Group: ONLY SHOWS INCLUDED SHADES -->
                <div>
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                        <label class="small fw-bold text-dark mb-0">
                            <i class="fa-solid fa-palette text-primary me-1"></i>Included Color Shades in ${escapeHtml(group.name)} (${group.shades.length}):
                        </label>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size: 0.78rem;" 
                                onclick="toggleShadePicker('${group.id}')">
                                <i class="fa-solid fa-plus me-1"></i> Add / Pick Shades (${availableShades.length} Available)
                            </button>
                            ${group.shades.length > 0 ? `
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-danger" style="font-size: 0.78rem;" 
                                    onclick="clearAllShadesForGroup('${group.id}')">Remove All</button>
                            ` : ''}
                        </div>
                    </div>

                    <!-- List of Included Shades Only (Does not show unincluded shades) -->
                    <div class="p-2 bg-white rounded border mb-2" style="min-height: 48px;">
                        ${includedShades.length === 0 ? `
                            <div class="text-muted small fst-italic py-2 text-center">
                                No color shades included yet. Click <strong>"+ Add / Pick Shades"</strong> to select shades for ${escapeHtml(group.name)}.
                            </div>
                        ` : `
                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                ${includedShades.map(s => `
                                    <span class="shade-pill d-inline-flex align-items-center bg-light border rounded-pill px-2 py-1 shadow-sm small">
                                        <span class="dot rounded-circle me-1" style="width: 12px; height: 12px; background-color: ${s.hexcode}; display: inline-block; border: 1px solid rgba(0,0,0,0.15);"></span>
                                        <span class="fw-semibold text-dark me-1">${escapeHtml(s.name)}</span>
                                        <button type="button" class="btn-close" style="font-size: 0.65rem;" onclick="removeShadeFromGroup('${group.id}', ${s.id})" title="Remove from group"></button>
                                    </span>
                                `).join('')}
                            </div>
                        `}
                    </div>

                    <!-- Expandable Shade Picker: ONLY SHOWS SHADES NOT IN ANY OTHER GROUP -->
                    <div id="picker_${group.id}" class="shade-picker-box p-3 bg-light rounded border mb-2" style="${activePickerGroupId === group.id ? 'display:block;' : 'display:none;'}">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2 pb-2 border-bottom">
                            <div class="small fw-bold text-dark">
                                Select Available Shades for ${escapeHtml(group.name)}:
                                <span class="badge bg-secondary ms-1">${availableShades.length} Available</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <input type="text" class="form-control form-control-sm" placeholder="Search available shades..." 
                                    style="max-width: 180px;" oninput="filterPickerShades('${group.id}', this.value)">
                                ${availableShades.length > 0 ? `
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" onclick="selectAllAvailableShadesForGroup('${group.id}')">Add All Available</button>
                                ` : ''}
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="toggleShadePicker('${group.id}')">Close</button>
                            </div>
                        </div>

                        ${availableShades.length === 0 ? `
                            <div class="text-muted small fst-italic py-2 text-center">
                                All category shades are already assigned to other groups. Remove shades from other groups to make them available here.
                            </div>
                        ` : `
                            <div class="d-flex flex-wrap gap-1" id="picker_chips_${group.id}" style="max-height: 200px; overflow-y: auto;">
                                ${availableShades.map(s => `
                                    <label class="shade-check-chip cursor-pointer" data-shade-name="${escapeHtml(s.name).toLowerCase()}">
                                        <input type="checkbox" class="form-check-input me-1" value="${s.id}" 
                                            onchange="addShadeToGroup('${group.id}', ${s.id})">
                                        <span class="dot" style="background-color: ${s.hexcode};"></span>
                                        <span>${escapeHtml(s.name)}</span>
                                    </label>
                                `).join('')}
                            </div>
                        `}
                    </div>
                </div>
            </div>
        </div>
        `;
    });

    container.innerHTML = html;
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

// Validate and populate on submit
function validateStep1Form(event) {
    const enabledGroups = step1Groups.filter(g => g.enabled);

    if (enabledGroups.length === 0) {
        alert('Please checkmark at least one group to proceed.');
        return false;
    }

    let hasEmptyGroup = false;
    let emptyGroupName = '';

    enabledGroups.forEach(grp => {
        if (grp.shades.length === 0) {
            hasEmptyGroup = true;
            emptyGroupName = `${grp.name} has no color shades selected. Please add at least one shade.`;
        }
        if (grp.pack_ids.length === 0) {
            hasEmptyGroup = true;
            emptyGroupName = `${grp.name} has no pack sizes selected. Please select at least one pack size.`;
        }
    });

    if (hasEmptyGroup) {
        alert(emptyGroupName);
        return false;
    }

    // Serialize groups into JSON
    const cleanGroups = enabledGroups.map(g => ({
        id: g.id,
        name: g.name,
        shades: g.shades,
        pack_ids: g.pack_ids
    }));

    document.getElementById('selectedGroupsJsonInput').value = JSON.stringify(cleanGroups);

    // Populate attributes[] container
    const attrContainer = document.getElementById('hiddenAttributesContainer');
    attrContainer.innerHTML = '';

    enabledGroups.forEach(g => {
        g.shades.forEach(shadeId => {
            g.pack_ids.forEach(packId => {
                attrContainer.innerHTML += `
                    <input type="hidden" name="attributes[]" value="${packId}-&-${shadeId}">
                `;
            });
        });
    });

    return true;
}
</script>
@endsection
