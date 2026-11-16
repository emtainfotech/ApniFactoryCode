@extends('layout')
@section('title',$title)
@section('content')
@php
    $effectiveCommission = isset($commissionRate) ? (float)$commissionRate : 0.00;
    $defaultTax = isset($detail->tax) ? (float)$detail->tax : 18.00;
    $groupsList = $shadeGroups ?? [];

    // Find all assigned shade IDs across all groups to prevent duplicates
    $allAssignedShadeIds = [];
    foreach ($groupsList as $grp) {
        foreach ((array)($grp['shades'] ?? []) as $sid) {
            $allAssignedShadeIds[] = (int)$sid;
        }
    }
    $allAssignedShadeIds = array_unique($allAssignedShadeIds);

    // Unassigned shades in this product's category
    $unassignedCategoryShades = $shadecard->filter(function($colr) use ($allAssignedShadeIds) {
        return !in_array((int)$colr->id, $allAssignedShadeIds);
    });

    // Available pack sizes for this product
    $distinctPackIds = !empty($distinctPackIds) ? $distinctPackIds : (!empty($attributelist) ? $attributelist->pluck('quantity')->unique()->filter()->values()->all() : []);
    if (empty($distinctPackIds) && !empty($boxPackings)) {
        $distinctPackIds = $boxPackings->pluck('id')->take(6)->all();
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
    .shade-pill .remove-shade-btn {
        background: none;
        border: none;
        color: #dc3545;
        font-weight: bold;
        padding: 0 0 0 5px;
        line-height: 1;
        cursor: pointer;
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
                        <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('seller.paint-pricing.index', ['product_id' => $detail->id]) }}" class="btn btn-outline-primary btn-sm shadow-sm">
                    <i class="fa-solid fa-paint-roller me-1"></i> Smart Paint Pricing
                </a>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <h5 class="card-title mb-1 fw-bold text-dark">Edit Product Details</h5>
                        <p class="text-muted small mb-0">Update core product specifications, branding, category and images.</p>
                    </div>
                    <div>
                        <span class="badge {{ $detail->approval_status === 'approved' ? 'bg-success' : 'bg-warning text-dark' }} px-3 py-2 rounded-pill">
                            Status: {{ ucfirst(str_replace('_', ' ', $detail->approval_status ?? 'draft')) }}
                        </span>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success border-0 bg-light-success text-success alert-dismissible fade show mb-3" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger border-0 bg-light-danger text-danger alert-dismissible fade show mb-3" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form method="post" enctype='multipart/form-data'>
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Product Name</label>
                            <input type="text" class="form-control" name="name" value="{{$detail->name}}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Product Brand</label>
                            <select class="form-select" name="brand">
                                @foreach(Helper::getbrand() as $bid)
                                    <option value="{{$bid->id}}" {{ $detail->brand_id == $bid->id ? 'selected' : '' }}>{{$bid->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Main Category</label>
                            <select class="form-select" id="maincategoryforajaxforproduct" name="maincategory">
                                @foreach(Helper::getmaincat() as $mid)
                                    <option value="{{$mid->id}}" {{ $detail->maincategory_id == $mid->id ? 'selected' : '' }}>{{$mid->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Category</label>
                            <select class="form-select" id="subcatforajax" name="category">
                                @php
                                    $catsForMain = Helper::getcatbymid($detail->maincategory_id) ?: [];
                                @endphp
                                @if(!empty($catsForMain) && count($catsForMain) > 0)
                                    @foreach($catsForMain as $catItem)
                                        <option value="{{$catItem->id}}" {{ $detail->category_id == $catItem->id ? 'selected' : '' }}>{{$catItem->name}}</option>
                                    @endforeach
                                @else
                                    <option value="{{$detail->category_id}}">{{Helper::getcatname($detail->category_id)}}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Product Status</label>
                            <select class="form-select" name="status">
                                <option value="Active" {{ $detail->status == 'Active' || $detail->status == '1' ? 'selected' : '' }}>Active</option>
                                <option value="Deactive" {{ $detail->status == 'Deactive' || $detail->status == '0' ? 'selected' : '' }}>Deactive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Product HSN Code</label>
                            <input type="text" class="form-control" name="hsncode" value="{{$detail->hsncode}}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tax Rate</label>
                            <input type="text" class="form-control" readonly value="{{ $detail->tax ?: 18 }}% (Default GST)" style="background: #f8f9fa;">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Product Description</label>
                            <div class="text-editor">
                                <textarea name="details" class="form-control" rows="4">{{$detail->description}}</textarea> 
                            </div>
                        </div>

                        <!-- Product Images -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Product Images</label>
                            <div id="existing-images-container" class="d-flex flex-wrap gap-3 mb-2">
                                @if(!empty($detail->multipleimages))
                                    @php
                                        $imgList = is_string($detail->multipleimages) ? (json_decode($detail->multipleimages, true) ?: []) : (is_array($detail->multipleimages) ? $detail->multipleimages : []);
                                    @endphp
                                    @foreach($imgList as $index => $dmtp)
                                        <div class="image-preview-card" style="position: relative; display: inline-block;">
                                            <input type="hidden" name="current_images[]" value="{{ $dmtp }}">
                                            <img src="{{ Helper::imageUrl($dmtp) }}" onerror="this.onerror=null;this.src='{{ asset('img/Apni-Factory-3.png') }}';" width="110px" height="110px" style="object-fit: cover; border: 1px solid #ddd; padding: 3px; border-radius: 6px;"/>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()" style="position: absolute; top: -6px; right: -6px; border-radius: 50%; width: 22px; height: 22px; padding: 0; line-height: 20px; font-weight: bold;">
                                                &times;
                                            </button>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <div class="row g-2 mt-2">
                                <div class="col-md-6">
                                    <label for="new_images" class="form-label small text-muted">Add More Gallery Images:</label>
                                    <input type="file" name="new_images[]" id="new_images" class="form-control form-control-sm" multiple accept="image/*">
                                </div>
                                <div class="col-md-6">
                                    <label for="home_image" class="form-label small text-muted">Custom Home Screen Display Image (Optional):</label>
                                    <input type="file" name="home_image" id="home_image" class="form-control form-control-sm" accept="image/*">
                                    @if(!empty($detail->home_image))
                                        <div class="mt-1 d-flex align-items-center gap-2">
                                            <span class="small text-muted">Current Home Image:</span>
                                            <img src="{{ Helper::imageUrl($detail->home_image) }}" width="36" height="36" class="rounded border" style="object-fit: cover;">
                                        </div>
                                    @endif
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="show_on_home" value="1" id="show_on_home" {{ ($detail->show_on_home ?? 1) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold text-dark small" for="show_on_home">
                                            <i class="fa-solid fa-house-chimney text-primary me-1"></i> Feature & display this product on Customer Mobile App Home Screen
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary px-4 fw-bold">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Update Product Info
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- SECTION 1: SHADE GROUPS PRICING MATRIX (ASIAN PAINTS STYLE) -->
        <!-- ======================================================== -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <div>
                        <h5 class="card-title mb-1 fw-bold text-dark">
                            <i class="fa-solid fa-layer-group text-primary me-2"></i>Shade Groups Pricing Matrix (Asian Paints Style)
                        </h5>
                        <p class="text-muted small mb-0">
                            Color shades are organized into pricing groups (Gr-0, Gr-1, etc.). Enter Factory Price once per group; display prices calculate automatically directly below.
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light-primary text-primary px-3 py-2 fs-6 rounded-pill border border-primary">
                            Commission: <strong>+{{ number_format($commissionRate ?? 0, 2) }}%</strong>
                        </span>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" data-bs-toggle="collapse" data-bs-target="#newGroupCollapse" aria-expanded="false">
                            <i class="fa-solid fa-plus me-1"></i> Add New Shade Group
                        </button>
                    </div>
                </div>

                <!-- Collapse: Create New Shade Group -->
                <div class="collapse mb-4" id="newGroupCollapse">
                    <div class="card card-body bg-light border">
                        <h6 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-folder-plus me-2"></i>Create New Shade Group</h6>
                        <form method="POST" action="{{ route('seller.product.create-shade-group', $detail->id) }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Group Name</label>
                                    <input type="text" name="group_name" class="form-control form-control-sm" placeholder="e.g. Gr-2 Accent, Metallic..." required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">GST Included?</label>
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="is_gst_included" value="1" id="new_grp_gst">
                                        <label class="form-check-label small" for="new_grp_gst">Yes, include GST (Sends to Review Bucket)</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">GST Rate (%)</label>
                                    <input type="number" step="0.01" name="gst_rate" class="form-control form-control-sm" value="{{ $detail->tax ?: 18 }}" placeholder="18">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Assign Shades (Only unassigned shades shown):</label>
                                    <div class="d-flex flex-wrap gap-2 p-2 bg-white rounded border" style="max-height: 140px; overflow-y: auto;">
                                        @forelse($unassignedCategoryShades as $ushade)
                                            <div class="form-check form-check-inline mb-0">
                                                <input class="form-check-input" type="checkbox" name="shades[]" value="{{ $ushade->id }}" id="ushade_{{ $ushade->id }}">
                                                <label class="form-check-label small" for="ushade_{{ $ushade->id }}">
                                                    <span class="shade-dot" style="background-color: {{ $ushade->hexcode ?: '#6c757d' }};"></span>
                                                    {{ $ushade->name }}
                                                </label>
                                            </div>
                                        @empty
                                            <span class="text-muted small fst-italic">All category shades are already assigned to existing groups.</span>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Pack Sizes Pricing Matrix for New Group -->
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Base Factory Prices for Pack Sizes:</label>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered bg-white text-center mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Tier</th>
                                                    @foreach($distinctPackIds as $pid)
                                                        <th>{{ Helper::getboxpackingnamebyid($pid) }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="text-start ps-2 fw-bold text-dark">Factory Price (₹)</td>
                                                    @foreach($distinctPackIds as $pid)
                                                        <td>
                                                            <input type="number" step="0.01" min="0" name="packs[{{ $pid }}][seller_price]" id="new_grp_fact_{{ $pid }}" oninput="calcNewGroupDisplayPrice('{{ $pid }}')" class="form-control form-control-sm text-end" placeholder="0.00">
                                                        </td>
                                                    @endforeach
                                                </tr>
                                                <tr>
                                                    <td class="text-start ps-2 fw-bold text-primary">
                                                        Display Price (₹)
                                                        <div class="small text-muted fw-normal" style="font-size: 10px;">(Locked)</div>
                                                    </td>
                                                    @foreach($distinctPackIds as $pid)
                                                        <td>
                                                            <input type="number" step="0.01" min="0" name="packs[{{ $pid }}][price]" id="new_grp_disp_{{ $pid }}" class="form-control form-control-sm text-end text-primary fw-bold" placeholder="Auto" readonly tabindex="-1" style="background-color: #e9ecef !important; cursor: not-allowed; color: #495057 !important;">
                                                        </td>
                                                    @endforeach
                                                </tr>
                                                <tr>
                                                    <td class="text-start ps-2 text-muted">MRP (₹)</td>
                                                    @foreach($distinctPackIds as $pid)
                                                        <td>
                                                            <input type="number" step="0.01" min="0" name="packs[{{ $pid }}][mrp]" class="form-control form-control-sm text-end text-muted" placeholder="Optional">
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">
                                        <i class="fa-solid fa-plus me-1"></i> Create Group
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Existing Groups Loop -->
                @forelse($groupsList as $gIdx => $group)
                    @php
                        $groupId = $group['group_id'] ?? ('group_' . ($gIdx + 1));
                        $groupName = $group['name'] ?? ('Group ' . ($gIdx + 1));
                        $isGst = !empty($group['is_gst_included']) && ($group['is_gst_included'] === 1 || $group['is_gst_included'] === '1' || $group['is_gst_included'] === true);
                        $gstRate = !empty($group['gst_rate']) ? (float)$group['gst_rate'] : $defaultTax;
                        $shadesInfo = $group['shades_info'] ?? [];
                        $groupShadeIds = (array)($group['shades'] ?? []);
                        $packs = $group['packs'] ?? [];

                        // Pack IDs present in this group or product
                        $groupPackIds = array_keys($packs);
                        if (empty($groupPackIds)) {
                            $groupPackIds = $distinctPackIds;
                        }
                    @endphp

                    <div class="card border group-card mb-4 shadow-sm" id="edit_group_card_{{ $groupId }}">
                        <form method="POST" action="{{ route('seller.product.update-group-pricing', $detail->id) }}">
                            @csrf
                            <input type="hidden" name="group_id" value="{{ $groupId }}">

                            <div class="card-header bg-white py-3 border-bottom">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary px-2 py-1 fs-6">
                                            #{{ $gIdx + 1 }}
                                        </span>
                                        <div class="input-group input-group-sm" style="max-width: 300px;">
                                            <span class="input-group-text fw-bold bg-light">Group:</span>
                                            <input type="text" name="group_name" class="form-control fw-bold text-dark" value="{{ $groupName }}" required>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-3">
                                        <!-- GST Included & Rate -->
                                        <div class="d-flex align-items-center gap-2 bg-light px-3 py-1.5 rounded border">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" name="is_gst_included" value="1" id="edit_gst_{{ $groupId }}" {{ $isGst ? 'checked' : '' }} onchange="document.getElementById('edit_gst_rate_{{ $groupId }}').style.display = this.checked ? 'flex' : 'none'">
                                                <label class="form-check-label small fw-bold text-dark" for="edit_gst_{{ $groupId }}">
                                                    GST Included?
                                                </label>
                                            </div>
                                            <div id="edit_gst_rate_{{ $groupId }}" class="d-flex align-items-center gap-1 ms-2" style="{{ $isGst ? '' : 'display: none !important;' }}">
                                                <label class="small text-muted mb-0">Rate:</label>
                                                <div class="input-group input-group-sm" style="width: 85px;">
                                                    <input type="number" step="0.01" min="0" max="100" name="gst_rate" class="form-control form-control-sm text-center fw-bold" value="{{ $gstRate }}">
                                                    <span class="input-group-text p-1">%</span>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-success btn-sm px-3 fw-bold shadow-sm">
                                            <i class="fa-solid fa-check me-1"></i> Update Group Pricing
                                        </button>
                                    </div>
                                </div>

                                <!-- Assigned Shades Chips & Add Shade Dropdown -->
                                <div class="mt-3 pt-2 border-top">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                        <div class="small fw-bold text-secondary">
                                            <i class="fa-solid fa-palette me-1 text-primary"></i> Assigned Shades ({{ count($shadesInfo) }}):
                                        </div>
                                        
                                        <!-- Add Shade to this Group Dropdown -->
                                        @if($unassignedCategoryShades->count() > 0)
                                            <div class="dropdown">
                                                <button class="btn btn-outline-primary btn-sm dropdown-toggle py-0 px-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.78rem;">
                                                    <i class="fa-solid fa-plus me-1"></i> Add Shade ({{ $unassignedCategoryShades->count() }} Available)
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow" style="max-height: 240px; overflow-y: auto;">
                                                    <li><h6 class="dropdown-header">Select shade to assign to {{ $groupName }}</h6></li>
                                                    @foreach($unassignedCategoryShades as $availShade)
                                                        <li>
                                                            <a class="dropdown-item d-flex align-items-center small py-1" href="javascript:;" onclick="submitAddShade('{{ $detail->id }}', '{{ $groupName }}', '{{ $availShade->id }}')">
                                                                <span class="shade-dot" style="background-color: {{ $availShade->hexcode ?: '#6c757d' }};"></span>
                                                                {{ $availShade->name }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @else
                                            <span class="badge bg-light text-muted border" style="font-size: 0.72rem;">All category shades assigned</span>
                                        @endif
                                    </div>

                                    <div class="d-flex flex-wrap align-items-center">
                                        @forelse($shadesInfo as $sInfo)
                                            <span class="shade-pill">
                                                <span class="shade-dot" style="background-color: {{ $sInfo['hexcode'] ?? '#6c757d' }};"></span>
                                                {{ $sInfo['name'] ?? 'Shade #' . $sInfo['id'] }}
                                                <button type="button" class="remove-shade-btn" onclick="submitRemoveShade('{{ $detail->id }}', '{{ $groupName }}', '{{ $sInfo['id'] }}')" title="Remove shade from group">&times;</button>
                                            </span>
                                        @empty
                                            <span class="text-danger small fst-italic py-1">No shades assigned to this group.</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <!-- Asian Paints Rate Matrix Table -->
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle mb-0 text-center" style="width: 100%;">
                                        <thead class="table-dark">
                                            <tr>
                                                <th style="width: 200px; text-align: left;" class="ps-3">Pricing Tier</th>
                                                @foreach($groupPackIds as $pid)
                                                    <th style="min-width: 120px;">
                                                        <div class="fw-bold">{{ Helper::getboxpackingnamebyid($pid) }}</div>
                                                    </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- ROW 1: Factory Price (₹) -->
                                            <tr class="table-light">
                                                <td class="text-start ps-3 fw-bold text-dark">
                                                    <span class="badge bg-secondary me-1 tier-badge">1. Factory Price</span>
                                                    <div class="small text-muted fw-normal">Amount seller receives</div>
                                                </td>
                                                @foreach($groupPackIds as $pid)
                                                    @php
                                                        $pData = $packs[$pid] ?? [];
                                                        $sellerP = isset($pData['seller_price']) ? $pData['seller_price'] : ($pData['price'] ?? '');
                                                    @endphp
                                                    <td>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text">₹</span>
                                                            <input type="number" step="0.01" min="0" 
                                                                name="packs[{{ $pid }}][seller_price]" 
                                                                id="fact_edit_{{ $groupId }}_{{ $pid }}"
                                                                class="form-control form-control-sm text-end fw-semibold" 
                                                                value="{{ $sellerP }}" 
                                                                oninput="calcEditDisplayPrice('{{ $groupId }}', '{{ $pid }}')" 
                                                                placeholder="0.00" required>
                                                        </div>
                                                    </td>
                                                @endforeach
                                            </tr>

                                            <!-- ROW 2: Display Price (Auto-calculated, Placed JUST BELOW Factory Price - Locked/Blocked from Manual Edit) -->
                                            <tr style="background-color: #f0f7ff;">
                                                <td class="text-start ps-3 fw-bold text-primary">
                                                    <span class="badge bg-primary me-1 tier-badge">2. Display Price</span>
                                                    <div class="small text-muted fw-normal">+{{ number_format($commissionRate, 2) }}% comm. (Locked)</div>
                                                </td>
                                                @foreach($groupPackIds as $pid)
                                                    @php
                                                        $pData = $packs[$pid] ?? [];
                                                        $dispP = isset($pData['price']) ? $pData['price'] : '';
                                                    @endphp
                                                    <td>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text bg-light-primary text-primary fw-bold">₹</span>
                                                            <input type="number" step="0.01" min="0" 
                                                                name="packs[{{ $pid }}][price]" 
                                                                id="disp_edit_{{ $groupId }}_{{ $pid }}"
                                                                class="form-control form-control-sm text-end fw-bold text-primary" 
                                                                value="{{ $dispP }}" 
                                                                placeholder="Auto" required readonly tabindex="-1"
                                                                style="background-color: #e9ecef !important; cursor: not-allowed; color: #495057 !important;">
                                                        </div>
                                                    </td>
                                                @endforeach
                                            </tr>

                                            <!-- ROW 3: MRP Price (₹) Optional -->
                                            <tr>
                                                <td class="text-start ps-3 text-muted">
                                                    <span class="badge bg-light text-dark border me-1 tier-badge">3. MRP Price</span>
                                                    <div class="small text-muted fw-normal">Maximum Retail Price</div>
                                                </td>
                                                @foreach($groupPackIds as $pid)
                                                    @php
                                                        $pData = $packs[$pid] ?? [];
                                                        $mrpP = isset($pData['mrp']) ? $pData['mrp'] : '';
                                                    @endphp
                                                    <td>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text">₹</span>
                                                            <input type="number" step="0.01" min="0" 
                                                                name="packs[{{ $pid }}][mrp]" 
                                                                class="form-control form-control-sm text-end text-muted" 
                                                                value="{{ $mrpP }}" 
                                                                placeholder="Optional">
                                                        </div>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </form>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted bg-light rounded border">
                        <i class="fa-solid fa-layer-group fs-3 mb-2 text-secondary"></i>
                        <h6>No Shade Groups Configured</h6>
                        <p class="small mb-3">Click "+ Add New Shade Group" above to group shades and price them in a single field.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- SECTION 2: PRODUCT PRICE REVISION & REVIEW AUDIT HISTORY (MOVED TO TOP OF SHADES TABLE) -->
        <!-- ======================================================== -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center pb-2 border-bottom mb-3">
                    <div>
                        <h5 class="card-title mb-1 fw-bold text-dark">
                            <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Product Price Revision & Review Audit History
                        </h5>
                        <p class="text-muted small mb-0">Complete historical timeline of pricing adjustments, shade group changes, and admin approvals for this product.</p>
                    </div>
                    <span class="badge bg-light text-dark border px-3 py-2">
                        Total Logs: <strong>{{ count($reviewHistories ?? []) }}</strong>
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0 text-left small">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 160px;">Date & Time</th>
                                <th style="width: 180px;">Action</th>
                                <th style="width: 150px;">Performed By</th>
                                <th style="width: 140px;">Status Change</th>
                                <th>Notes & Price Change Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviewHistories ?? [] as $hist)
                                @php
                                    $actionLabel = match($hist->action) {
                                        'submitted_for_gst_review' => 'Submitted for GST Review',
                                        'pricing_adjusted_by_admin' => 'Pricing Adjusted by Admin',
                                        'approved', 'approved_and_published' => 'Approved & Published Live',
                                        'seller_updated_group_pricing' => 'Seller Updated Group Pricing',
                                        'seller_added_shade_to_group' => 'Added Shade to Group',
                                        'seller_removed_shade_from_group' => 'Removed Shade from Group',
                                        'seller_created_shade_group' => 'Created Shade Group',
                                        'variant_price_updated' => 'Variant Price Updated',
                                        default => ucfirst(str_replace('_', ' ', $hist->action)),
                                    };
                                    $actionBadgeClass = match($hist->action) {
                                        'approved', 'approved_and_published' => 'bg-success text-white',
                                        'pricing_adjusted_by_admin' => 'bg-info text-dark',
                                        'submitted_for_gst_review' => 'bg-warning text-dark',
                                        default => 'bg-light text-dark border',
                                    };
                                @endphp
                                <tr>
                                    <td class="text-muted whitespace-nowrap">
                                        {{ $hist->created_at ? $hist->created_at->format('d M Y, h:i A') : 'N/A' }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $actionBadgeClass }} px-2 py-1">
                                            {{ $actionLabel }}
                                        </span>
                                    </td>
                                    <td class="fw-semibold">
                                        {{ $hist->reviewer?->name ?? ($hist->seller?->name ?? 'System') }}
                                    </td>
                                    <td>
                                        <span class="small text-muted">{{ $hist->previous_status ?: 'draft' }}</span> 
                                        &rarr; 
                                        <span class="fw-bold text-dark">{{ $hist->new_status ?: 'pending' }}</span>
                                    </td>
                                    <td>
                                        <div class="text-dark">{{ $hist->notes }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No revision history logs recorded for this product yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- SECTION 3: INDIVIDUAL VARIANTS & FILTERABLE TABLE       -->
        <!-- ======================================================== -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="card-title mb-1 fw-bold text-dark">Individual Variant SKU List</h5>
                        <p class="text-muted small mb-0">View or fine-tune individual variant SKUs. Use the quick add form on top to add custom pack/color combinations.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="copyFirstPriceToAll()" title="Copy first factory price to all shades of matching pack size">
                            <i class="fa fa-copy me-1"></i> Copy First Price to All Shades
                        </button>
                    </div>
                </div>

                <!-- ADD NEW VARIANT / SKU (MOVED TO TOP) -->
                <div class="card border border-primary border-opacity-25 bg-light mb-4 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="fw-bold text-primary small">
                                <i class="fa fa-plus-circle me-1"></i> Add New Shade / SKU Combination (Top Form)
                            </div>
                            <span class="badge bg-light-primary text-primary border border-primary small" style="font-size: 11px;">
                                Display Price (+{{ number_format($commissionRate ?? 0, 2) }}%) Auto-Calculated & Locked
                            </span>
                        </div>
                        <form method="post" action="{{route('product.addproductattribute')}}">
                            @csrf
                            <input type="hidden" name="productid" value="{{$detail->id}}">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-2 col-6">
                                    <label class="form-label small fw-semibold mb-1">Select Packing <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" name="packingid" id="new_packingid" required>
                                        <option value="">Choose Packing</option>
                                        @foreach(Helper::getboxpacking() as $boxpck)
                                            <option value="{{$boxpck->id}}">{{$boxpck->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label small fw-semibold mb-1">Select Color / Shade <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" name="colorid" id="new_colorid" required>
                                        <option value="">Choose Color / Shade</option>
                                        @foreach($shadecard as $colr)
                                            <option value="{{$colr->id}}" data-hex="{{ $colr->hexcode }}">{{$colr->name}} {{ $colr->hexcode ? "({$colr->hexcode})" : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 col-6">
                                    <label class="form-label small fw-semibold mb-1">Factory Price (₹) <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="0.01" min="0" name="seller_price" id="new_factory_price" class="form-control" placeholder="0.00" required oninput="calculateNewDisplayPrice()">
                                    </div>
                                </div>
                                <div class="col-md-2 col-6">
                                    <label class="form-label small fw-semibold mb-1">Display Price (₹) <span class="text-muted">(Locked)</span></label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light-primary text-primary fw-bold">₹</span>
                                        <input type="number" step="0.01" min="0" name="price" id="new_display_price" class="form-control fw-bold text-primary" placeholder="Auto" required readonly tabindex="-1" style="background-color: #e9ecef !important; cursor: not-allowed; color: #495057 !important;">
                                    </div>
                                </div>
                                <div class="col-md-1 col-6">
                                    <label class="form-label small fw-semibold mb-1">MRP (₹)</label>
                                    <input type="number" step="0.01" min="0" name="mrp" id="new_mrp" class="form-control form-control-sm" placeholder="Optional">
                                </div>
                                <div class="col-md-2 col-6 d-flex align-items-center gap-2">
                                    <div class="form-check mb-0">
                                        <input type="checkbox" name="is_gst_included" id="new_gst_included" value="1" class="form-check-input">
                                        <label class="form-check-label small" for="new_gst_included">Incl. GST</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold flex-grow-1">
                                        <i class="fa fa-plus me-1"></i> Add SKU
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- FILTER BAR & PAGINATION CONTROLS -->
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 bg-light p-3 rounded border">
                    <form method="GET" class="d-flex flex-wrap align-items-center gap-2 mb-0">
                        <div class="d-flex align-items-center gap-1">
                            <label for="srchshade" class="form-label small fw-semibold mb-0 text-nowrap">Shade:</label>
                            <select class="form-select form-select-sm" id="srchshade" name="srchshade" style="min-width: 140px;">
                                <option value="">All Shades</option>
                                @foreach($shadecard as $colr)
                                    <option value="{{$colr->id}}" {{ request('srchshade') == $colr->id ? 'selected' : '' }}>{{$colr->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex align-items-center gap-1">
                            <label for="srchpack" class="form-label small fw-semibold mb-0 text-nowrap">Pack Size:</label>
                            <select class="form-select form-select-sm" id="srchpack" name="srchpack" style="min-width: 130px;">
                                <option value="">All Packs</option>
                                @foreach(Helper::getboxpacking() as $bp)
                                    <option value="{{$bp->id}}" {{ request('srchpack') == $bp->id ? 'selected' : '' }}>{{$bp->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex align-items-center gap-1">
                            <label for="per_page" class="form-label small fw-semibold mb-0 text-nowrap">Show:</label>
                            <select class="form-select form-select-sm" id="per_page" name="per_page" onchange="this.form.submit()" style="width: 75px;">
                                <option value="15" {{ ($perPage ?? 25) == 15 ? 'selected' : '' }}>15</option>
                                <option value="25" {{ ($perPage ?? 25) == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ ($perPage ?? 25) == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ ($perPage ?? 25) == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-warning btn-sm px-3 fw-semibold">
                            <i class="fa fa-filter me-1"></i> Filter
                        </button>

                        @if(request('srchshade') || request('srchpack') || request('per_page'))
                            <a href="{{ route('product.edit', $detail->id) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fa fa-times me-1"></i> Reset
                            </a>
                        @endif
                    </form>

                    <div class="d-flex align-items-center gap-2">
                        @if($attributelist instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            <span class="badge bg-white text-dark border px-3 py-2 small">
                                Showing <strong>{{ $attributelist->firstItem() ?? 0 }}</strong> - <strong>{{ $attributelist->lastItem() ?? 0 }}</strong> of <strong>{{ $attributelist->total() }}</strong> Variants
                            </span>
                        @endif
                    </div>
                </div>

                @if($attributelist instanceof \Illuminate\Pagination\LengthAwarePaginator && $attributelist->hasPages())
                    <div class="d-flex justify-content-end mb-2">
                        {{ $attributelist->links('pagination::bootstrap-4') }}
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered align-middle mb-0" style="width:100%" id="variantsPricingTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Packing</th>
                                <th>Color / Shade</th>
                                <th>Group</th>
                                <th style="min-width: 140px;">Factory Price (₹)</th>
                                <th class="text-center" style="min-width: 130px;">GST Included?</th>
                                <th style="min-width: 150px;">Display Price (+{{ number_format($commissionRate ?? 0, 2) }}%)</th>
                                <th style="min-width: 120px;">MRP Price (₹)</th>
                                <th style="min-width: 130px;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($attributelist) && count($attributelist) > 0)
                            @foreach($attributelist as $bxp)
                                @php
                                    $packName = Helper::getboxpackingnamebyid($bxp->quantity);
                                    $colorName = Helper::getcolorsnamebyid($bxp->color);
                                    $factPrice = $bxp->seller_price ?: $bxp->price;
                                    $isGst = (int)($bxp->is_gst_included ?? 0);
                                    $gRate = $bxp->gst_rate ?: $defaultTax;

                                    $hex = trim(Helper::getcolorshexcodebyid($bxp->color) ?: '');
                                    if (!empty($hex) && !str_starts_with($hex, '#')) {
                                        $hex = '#' . $hex;
                                    }
                                @endphp
                                <tr data-row-id="{{ $bxp->id }}" data-pack-id="{{ $bxp->quantity }}" data-pack-name="{{ $packName }}">
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1 fw-bold">
                                            {{ $packName }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if(!empty($hex))
                                                @php
                                                    $cleanHex = ltrim($hex, '#');
                                                    $r = hexdec(substr($cleanHex, 0, 2) ?: 'ff');
                                                    $g = hexdec(substr($cleanHex, 2, 2) ?: 'ff');
                                                    $b = hexdec(substr($cleanHex, 4, 2) ?: 'ff');
                                                    $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
                                                    $textColor = ($yiq >= 140) ? '#111827' : '#ffffff';
                                                @endphp
                                                <div class="d-inline-flex align-items-center gap-2 px-2 py-1 rounded shadow-sm border" style="background-color: {{ $hex }}; color: {{ $textColor }}; min-width: 140px;">
                                                    <span class="fw-bold">{{ $colorName }}</span>
                                                    <span class="badge" style="background: rgba(0,0,0,0.22); color: inherit; font-family: monospace; font-size: 11px;">{{ $hex }}</span>
                                                </div>
                                            @else
                                                <span class="fw-semibold text-dark">{{ $colorName }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light-primary text-primary border">
                                            {{ $bxp->group_name ?: 'Default' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" step="0.01" min="0" value="{{ $factPrice }}" name="seller_price" id="factory_price{{$bxp->id}}" class="form-control factory-price-input" oninput="calculateDisplayPrice('{{$bxp->id}}')" placeholder="Factory Price">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-flex justify-content-center align-items-center gap-1 mb-0">
                                            <input type="checkbox" class="form-check-input gst-checkbox" id="gst_included{{$bxp->id}}" value="1" {{ $isGst === 1 ? 'checked' : '' }}>
                                            <label class="form-check-label small text-muted" for="gst_included{{$bxp->id}}">
                                                Incl. GST ({{ $gRate }}%)
                                            </label>
                                            <input type="hidden" id="gst_rate{{$bxp->id}}" value="{{ $gRate }}">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light-primary text-primary fw-bold">₹</span>
                                            <input type="number" step="0.01" min="0" value="{{ $bxp->price }}" name="price" id="display_price{{$bxp->id}}" class="form-control display-price-input fw-bold text-primary" placeholder="Display Price" readonly tabindex="-1" style="background-color: #e9ecef !important; cursor: not-allowed; color: #495057 !important;">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" step="0.01" min="0" value="{{ $bxp->mrp ?? $bxp->oldprice ?? '' }}" name="mrp" id="mrp{{$bxp->id}}" class="form-control mrp-input" placeholder="Optional">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-success" onClick="updateattributeprice('{{$bxp->id}}')" title="Save updated pricing for this variant">
                                                <i class="fa fa-save me-1"></i> Update
                                            </button>
                                            <form action="{{ route('product.filter.delete', $bxp->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this variant?');" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="attributid" value="{{$bxp->id}}">
                                                <button type="submit" class="btn btn-danger" title="Delete variant">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            @else
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No variants found matching criteria. Use the quick add form above to add variants.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if($attributelist instanceof \Illuminate\Pagination\LengthAwarePaginator && $attributelist->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                        <div class="text-muted small">
                            Showing page {{ $attributelist->currentPage() }} of {{ $attributelist->lastPage() }} (Total {{ $attributelist->total() }} variants)
                        </div>
                        <div>
                            {{ $attributelist->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
<!--end page wrapper -->

<!-- Hidden Forms for Add/Remove Shade AJAX/POST -->
<form id="addShadeForm" method="POST" action="{{ route('seller.product.add-shade-to-group', $detail->id) }}" style="display:none;">
    @csrf
    <input type="hidden" name="group_name" id="addShadeGroupName">
    <input type="hidden" name="color_id" id="addShadeColorId">
</form>

<form id="removeShadeForm" method="POST" action="{{ route('seller.product.remove-shade-from-group', $detail->id) }}" style="display:none;">
    @csrf
    <input type="hidden" name="group_name" id="removeShadeGroupName">
    <input type="hidden" name="color_id" id="removeShadeColorId">
</form>

<script>
    const categoryCommissionRate = parseFloat("{{ $commissionRate ?? 0 }}") || 0;

    // Auto-calculate Display Price in Group Matrix (Row 2 just below Row 1)
    function calcEditDisplayPrice(groupId, packId) {
        const factInput = document.getElementById(`fact_edit_${groupId}_${packId}`);
        const dispInput = document.getElementById(`disp_edit_${groupId}_${packId}`);
        if (!factInput || !dispInput) return;

        const val = parseFloat(factInput.value);
        if (!isNaN(val) && val > 0) {
            const calculated = val * (1 + (categoryCommissionRate / 100));
            dispInput.value = (Math.round(calculated * 100) / 100).toFixed(2);
        }
    }

    // Auto-calculate Display Price for New Group Modal (Locked / Non-editable)
    function calcNewGroupDisplayPrice(packId) {
        const factInput = document.getElementById(`new_grp_fact_${packId}`);
        const dispInput = document.getElementById(`new_grp_disp_${packId}`);
        if (!factInput || !dispInput) return;

        const val = parseFloat(factInput.value);
        if (!isNaN(val) && val > 0) {
            const calculated = val * (1 + (categoryCommissionRate / 100));
            dispInput.value = (Math.round(calculated * 100) / 100).toFixed(2);
        } else {
            dispInput.value = '';
        }
    }

    // Individual variant display price
    function calculateDisplayPrice(id) {
        const factoryInput = document.getElementById('factory_price' + id);
        const displayInput = document.getElementById('display_price' + id);
        if (!factoryInput || !displayInput) return;

        const factVal = parseFloat(factoryInput.value);
        if (!isNaN(factVal) && factVal > 0) {
            const calculated = factVal * (1 + (categoryCommissionRate / 100));
            displayInput.value = (Math.round(calculated * 100) / 100).toFixed(2);
        }
    }

    // Add variant row display price
    function calculateNewDisplayPrice() {
        const factoryInput = document.getElementById('new_factory_price');
        const displayInput = document.getElementById('new_display_price');
        if (!factoryInput || !displayInput) return;

        const factVal = parseFloat(factoryInput.value);
        if (!isNaN(factVal) && factVal > 0) {
            const calculated = factVal * (1 + (categoryCommissionRate / 100));
            displayInput.value = (Math.round(calculated * 100) / 100).toFixed(2);
        }
    }

    // Submit Add Shade to Group
    function submitAddShade(productId, groupName, colorId) {
        document.getElementById('addShadeGroupName').value = groupName;
        document.getElementById('addShadeColorId').value = colorId;
        document.getElementById('addShadeForm').submit();
    }

    // Submit Remove Shade from Group
    function submitRemoveShade(productId, groupName, colorId) {
        if (!confirm('Remove this shade from ' + groupName + '?')) return;
        document.getElementById('removeShadeGroupName').value = groupName;
        document.getElementById('removeShadeColorId').value = colorId;
        document.getElementById('removeShadeForm').submit();
    }

    // Copy first price
    function copyFirstPriceToAll() {
        const packPriceMap = {};
        const packGstMap = {};
        const packMrpMap = {};

        const rows = document.querySelectorAll('#variantsPricingTable tbody tr[data-row-id]');
        
        rows.forEach(function(row) {
            const rowId = row.getAttribute('data-row-id');
            const packId = row.getAttribute('data-pack-id');
            const factInput = document.getElementById('factory_price' + rowId);
            const gstCheckbox = document.getElementById('gst_included' + rowId);
            const mrpInput = document.getElementById('mrp' + rowId);

            if (factInput && packId && !packPriceMap[packId]) {
                const val = parseFloat(factInput.value);
                if (!isNaN(val) && val > 0) {
                    packPriceMap[packId] = val;
                    packGstMap[packId] = gstCheckbox ? gstCheckbox.checked : false;
                    packMrpMap[packId] = mrpInput ? mrpInput.value : '';
                }
            }
        });

        let appliedCount = 0;
        rows.forEach(function(row) {
            const rowId = row.getAttribute('data-row-id');
            const packId = row.getAttribute('data-pack-id');
            const factInput = document.getElementById('factory_price' + rowId);
            const gstCheckbox = document.getElementById('gst_included' + rowId);
            const mrpInput = document.getElementById('mrp' + rowId);

            if (packId && packPriceMap[packId] !== undefined) {
                if (factInput) {
                    factInput.value = packPriceMap[packId].toFixed(2);
                    calculateDisplayPrice(rowId);
                }
                if (gstCheckbox && packGstMap[packId] !== undefined) {
                    gstCheckbox.checked = packGstMap[packId];
                }
                if (mrpInput && packMrpMap[packId]) {
                    mrpInput.value = packMrpMap[packId];
                }
                appliedCount++;
            }
        });

        alert("Copied base prices across " + appliedCount + " variant shade rows! Click [Update] on rows or submit to save.");
    }

    // AJAX: Update single attribute variant pricing with robust validation and feedback
    function updateattributeprice(id) {
        const btn = (typeof event !== 'undefined' && event) ? (event.currentTarget || event.target) : null;
        let origHtml = '';
        if (btn) {
            origHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
            btn.disabled = true;
        }

        const factoryInput = document.getElementById('factory_price' + id);
        const displayInput = document.getElementById('display_price' + id);
        const gstCheckbox = document.getElementById('gst_included' + id);
        const gstRateInput = document.getElementById('gst_rate' + id);
        const mrpInput = document.getElementById('mrp' + id);

        const factoryVal = factoryInput ? factoryInput.value.trim() : '';
        let displayVal = displayInput ? displayInput.value.trim() : '';
        const isGst = gstCheckbox && gstCheckbox.checked ? 1 : 0;
        const gstRate = gstRateInput ? gstRateInput.value : 18;
        const mrpVal = mrpInput ? mrpInput.value.trim() : '';

        if (!factoryVal || parseFloat(factoryVal) <= 0) {
            alert('Please enter a valid Factory Price greater than 0.');
            if (btn) { btn.innerHTML = origHtml; btn.disabled = false; }
            return;
        }

        if (!displayVal || parseFloat(displayVal) <= 0) {
            displayVal = (parseFloat(factoryVal) * (1 + (categoryCommissionRate / 100))).toFixed(2);
            if (displayInput) displayInput.value = displayVal;
        }

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        fetch('/seller/updateattributeprice', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                aid: id,
                seller_price: factoryVal,
                price: displayVal,
                is_gst_included: isGst,
                gst_rate: gstRate,
                mrp: mrpVal,
                _token: token
            })
        })
        .then(res => res.json())
        .then(data => {
            if (btn) { btn.innerHTML = origHtml; btn.disabled = false; }
            if (data.status) {
                let msg = 'Pricing successfully updated!';
                if (isGst == 1) {
                    msg += '\n\n(Product routed to Admin Review Bucket for display price verification)';
                }
                alert(msg);
                if (displayInput && data.price) {
                    displayInput.value = parseFloat(data.price).toFixed(2);
                }
            } else {
                alert('Update failed: ' + (data.message || 'Something went wrong'));
            }
        })
        .catch(err => {
            if (btn) { btn.innerHTML = origHtml; btn.disabled = false; }
            console.error(err);
            alert('Error updating pricing. Please check connection and try again.');
        });
    }
</script>
@endsection
