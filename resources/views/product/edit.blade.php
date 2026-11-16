@extends('layout')
@section('title',$title)
@section('content')
@php
    $companydetail = DB::table("companies")->select("comission")->where("user_id",Auth::user()->id)->first();
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
								<li class="breadcrumb-item"><a href="javascript:;">Home</a>
								</li>
								<li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
							</ol>
						</nav>
					</div>
					<div class="ms-auto">
						
					</div>
				</div>
				<!--end breadcrumb-->
                <div class="card">
				  <div class="card-body p-4">
					  <div class="d-flex justify-content-between align-items-center mb-3">
						  <h5 class="card-title mb-0">Edit Product</h5>
						  <a href="{{ route('seller.paint-pricing.index', ['product_id' => $detail->id]) }}" class="btn btn-outline-primary shadow-sm">
							  <i class="fa-solid fa-paint-roller me-2"></i> Open Smart Paint Pricing
						  </a>
					  </div>
					  <hr/>
                       <div class="form-body mt-4">
					    <div class="row">
                           
						   <div class="col-lg-12">
						       @if(session('success'))
                                        <div class="alert alert-success" style="color: green; background: #e6ffed; padding: 10px; margin-bottom: 15px;">
                                            {{ session('success') }}
                                        </div>
                                    @endif
                                    
                                    @if(session('error'))
                                        <div class="alert alert-danger" style="color: red; background: #ffeef0; padding: 10px; margin-bottom: 15px;">
                                            {{ session('error') }}
                                        </div>
                                    @endif
						       <form method="post" enctype='multipart/form-data'>
						           @csrf
                           <div class="border border-3 p-4 rounded row">
							<div class="mb-3 col-md-6">
								<label for="inputProductTitle" class="form-label">Product Name</label>
								<input type="text" class="form-control" name="name" value="{{$detail->name}}">
							</div>
                              <div class="mb-3 col-md-6">
                                <label for="inputProductType" class="form-label">Product Brand</label>
                                <select class="form-select" id="inputProductType" name="brand">
                                    @foreach(Helper::getbrand() as $bid)
                                    <option value="{{$bid->id}}" @if($detail->brand_id==$bid->id) selected @else '' @endif>{{$bid->name}}</option>
                                    @endforeach
                                  </select>
                              </div>
                              <div class="mb-3 col-md-6">
                                <label for="inputProductType" class="form-label">Main Category</label>
                                <select class="form-select" id="maincategoryforajaxforproduct" name="maincategory">
                                     @foreach(Helper::getmaincat() as $mid)
                                    <option value="{{$mid->id}}"  @if($detail->maincategory_id==$mid->id) selected @else '' @endif>{{$mid->name}}</option>
                                    @endforeach
                                  </select>
                              </div>
                              <div class="mb-3 col-md-6">
                                <label for="inputProductType" class="form-label"> Category</label>
                               <select class="form-select" id="subcatforajax" name="category"  >
                                     <option value="{{$detail->category_id}}">{{Helper::getcatname($detail->category_id)}}</option>
                                  </select>
                              </div>
                              <div class="mb-3 col-md-6">
                                <label for="inputProductType" class="form-label">Status</label>
                                <select class="form-select" id="inputProductType" name="status">
                                    <option value="1"  @if($detail->status=='1') selected @else '' @endif>Active</option>
                                    <option value="0" @if($detail->status=='0') selected @else '' @endif>Deactive</option>
                                  </select>
                              </div>
                              	<div class="mb-3 col-md-6">
								<label for="inputProductTitle" class="form-label">Product HSN Code</label>
								<input type="text" class="form-control" name="hsncode" value="{{$detail->hsncode}}">
							</div>
							  <div class="mb-3">
								<label for="inputProductDescription" class="form-label">Product Description</label>
								 <div class="text-editor">
                                            <textarea name="details" class="nic-edit-p" rows="10" style="width:100%">{{$detail->description}}</textarea> 
                                        </div>
							  </div>
                             
                            <div class="mb-3">
    <label class="form-label">Product Images</label>
    
    <div id="existing-images-container" class="d-flex flex-wrap gap-3 mb-3">
        @if(!empty($detail->multipleimages))
            @php
                $imgList = is_string($detail->multipleimages) ? (json_decode($detail->multipleimages, true) ?: []) : (is_array($detail->multipleimages) ? $detail->multipleimages : []);
            @endphp
            @foreach($imgList as $index => $dmtp)
                <div class="image-preview-card" style="position: relative; display: inline-block; margin-right: 10px;">
                    <input type="hidden" name="current_images[]" value="{{ $dmtp }}">
                    
                    <img src="{{ Helper::imageUrl($dmtp) }}" onerror="this.onerror=null;this.src='{{ asset('img/Apni-Factory-3.png') }}';" width="150px" height="150px" style="object-fit: cover; border: 1px solid #ddd; padding: 5px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);"/>
                    
                    <button type="button" class="btn btn-danger btn-sm remove-image-btn" onclick="this.parentElement.remove()" style="position: absolute; top: 5px; right: 5px; border-radius: 50%; width: 26px; height: 26px; padding: 0; line-height: 24px; font-weight: bold;">
                        &times;
                    </button>
                </div>
            @endforeach
        @endif
    </div>

    <div class="mt-2">
        <label for="new_images" class="form-label">Add More Images:</label>
        <input type="file" name="new_images[]" id="new_images" class="form-control" multiple accept="image/*">
    </div>
</div>
                              <div class="mb-3">
                                <div class="d-grid">
                                   <button type="submit" class="btn btn-primary">Update</button>
                                </div>
                            </div>
                            </div>
                            </form>

                              <div class="card mt-4 border">
				  <div class="card-body p-4">
                      <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                          <div>
                              <h5 class="card-title mb-1">Product Variants & Pricing</h5>
                              <p class="text-muted small mb-0">Manage factory prices, GST inclusion status, display prices, and MRP across pack sizes and color shades.</p>
                          </div>
                          <div class="d-flex align-items-center gap-2">
                              <span class="badge bg-primary px-3 py-2 fs-6">
                                  Category Commission Rate: +{{ number_format($commissionRate ?? 0, 2) }}%
                              </span>
                              <button type="button" class="btn btn-outline-secondary btn-sm" onclick="copyFirstPriceToAll()" title="Copy the first variant factory price to all other color shades of matching pack size">
                                  <i class="fa fa-copy me-1"></i> Copy First Price to All Shades
                              </button>
                          </div>
                      </div>
					  <hr/>
                   <form method="GET" class="row align-items-end g-2 mb-3">
                        @csrf
                        <div class="form-group col-md-3">
                            <label for="srchshade" class="form-label small fw-semibold">Filter by Shade Card:</label>
                            <select class="form-select form-select-sm" id="srchshade" name="srchshade">
                                <option value="">All Shades</option>
                                @foreach($shadecard as $colr)
                                    <option value="{{$colr->id}}" {{ request('srchshade') == $colr->id ? 'selected' : '' }}>{{$colr->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <button type="submit" class="btn btn-warning btn-sm w-100">Filter</button>
                        </div>
                        @if(request('srchshade'))
                        <div class="form-group col-md-2">
                            <a href="{{ route('product.edit', $detail->id) }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
                        </div>
                        @endif
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-bordered align-middle mb-0" style="width:100%" id="variantsPricingTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Packing</th>
                                    <th>Color / Shade</th>
                                    <th style="min-width: 140px;">Factory Price (₹)</th>
                                    <th class="text-center" style="min-width: 110px;">GST Included?</th>
                                    <th style="min-width: 150px;">Display Price (+{{ number_format($commissionRate ?? 0, 2) }}%)</th>
                                    <th style="min-width: 130px;">MRP Price (₹)</th>
                                    <th style="min-width: 150px;" class="text-center">Actions</th>
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
                                    @endphp
                                    <tr data-row-id="{{ $bxp->id }}" data-pack-id="{{ $bxp->quantity }}" data-pack-name="{{ $packName }}">
                                        <td>
                                            <span class="badge bg-light text-dark border px-2 py-1 fw-bold">
                                                {{ $packName }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $colorName }}</span>
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
                                                <label class="form-check-label small text-muted" for="gst_included{{$bxp->id}}">Incl. GST</label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light-primary text-primary fw-bold">₹</span>
                                                <input type="number" step="0.01" min="0" value="{{ $bxp->price }}" name="price" id="display_price{{$bxp->id}}" class="form-control display-price-input fw-bold text-primary" placeholder="Display Price">
                                            </div>
                                            <small class="text-muted d-block" style="font-size: 0.72rem;">Auto-calculated, editable</small>
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
                                        <td colspan="7" class="text-center text-muted py-3">No variants found. Add a variant below.</td>
                                    </tr>
                                @endif

                                <!-- Add New Variant Row -->
                                <tr class="table-info">
                                    <form method="post" action="{{route('product.addproductattribute')}}">
                                        @csrf
                                        <input type="hidden" name="productid" value="{{$detail->id}}">
                                        <td>
                                            <select class="form-select form-select-sm" name="packingid" id="new_packingid" required>
                                                <option value="">Select Packing</option>
                                                @foreach(Helper::getboxpacking() as $boxpck)
                                                    <option value="{{$boxpck->id}}">{{$boxpck->name}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm" name="colorid" id="new_colorid" required>
                                                <option value="">Select Color</option>
                                                @foreach($shadecard as $colr)
                                                    <option value="{{$colr->id}}">{{$colr->name}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" step="0.01" min="0" name="seller_price" id="new_factory_price" class="form-control" placeholder="Factory Price" required oninput="calculateNewDisplayPrice()">
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check d-flex justify-content-center align-items-center gap-1 mb-0">
                                                <input type="checkbox" name="is_gst_included" id="new_gst_included" value="1" class="form-check-input">
                                                <label class="form-check-label small text-muted" for="new_gst_included">Incl. GST</label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light-primary text-primary fw-bold">₹</span>
                                                <input type="number" step="0.01" min="0" name="price" id="new_display_price" class="form-control fw-bold text-primary" placeholder="Display Price" required>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">₹</span>
                                                <input type="number" step="0.01" min="0" name="mrp" id="new_mrp" class="form-control" placeholder="Optional">
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                                <i class="fa fa-plus me-1"></i> Add Variant
                                            </button>
                                        </td>
                                    </form>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
						   </div>
					   </div><!--end row-->
					</div>
				  </div>
			  </div>
			</div>
		</div>
		<!--end page wrapper -->
 <script>
    const categoryCommissionRate = parseFloat("{{ $commissionRate ?? 0 }}") || 0;

    // Auto-calculate Display Price from Factory Price: Display = Factory * (1 + commission/100)
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

    // Auto-calculate Display Price for the bottom "Add Variant" row
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

    // Copy the first entered factory price of each pack size to all other color shades of matching pack size
    function copyFirstPriceToAll() {
        const packPriceMap = {};
        const packGstMap = {};
        const packMrpMap = {};

        const rows = document.querySelectorAll('#variantsPricingTable tbody tr[data-row-id]');
        
        // Pass 1: Collect first non-empty prices per packId
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

        // Pass 2: Apply to all rows sharing that packId
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
</script>
@endsection