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
            @foreach(json_decode($detail->multipleimages) as $index => $dmtp)
                <div class="image-preview-card" style="position: relative; display: inline-block; margin-right: 10px;">
                    <input type="hidden" name="current_images[]" value="{{ $dmtp }}">
                    
                    <img src="{{ env('imagepath').'/'.$dmtp }}" width="150px" height="150px" style="object-fit: cover; border: 1px solid #ddd; padding: 5px; border-radius: 4px;"/>
                    
                    <button type="button" class="btn btn-danger btn-sm remove-image-btn" onclick="this.parentElement.remove()" style="position: absolute; top: 5px; right: 5px;">
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
                             <div class="card">
				  <div class="card-body p-4"> <h5 class="card-title"> {{$title}} Filter</h5>
					  <hr/>
                   <form method="GET" class="row" >
                        @csrf
                        <div class="form-group col-md-3">
                            <label for="status">Shade Card:</label>
                            <select class="form-control" id="" name="srchshade">
                                <option value="">Select Shade</option>
                                  @foreach($shadecard as $colr)<option value="{{$colr->id}}">{{$colr->name}}</option>@endforeach</select></td>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                        <button type="submit" class="btn btn-warning" style="margin-top: 20px;">Search</button>
                        </div>
                    </form>
                    </div>
                    </div>
                              <div class="mb-3">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered" style="width:100%">
                                        <thead><th>Color</th><th>Quantity</th><th>Price</th><th>You Receive</th></thead>
                                        <tbody>
                                            @if(!empty($attributelist))
                                            @foreach($attributelist as $bxp)
                                                <tr>
                                                <td>{{Helper::getcolorsnamebyid($bxp->color)}}</td>
                                                <td>{{Helper::getboxpackingnamebyid($bxp->quantity)}}</td>
                                                <td><input type="text" value="{{$bxp->price}}" name="price" id="price{{$bxp->id}}" oninput="calculateGetPrice('{{$bxp->id}}')">
                                                <td><input type="text" name="get_price" id="get_price{{$bxp->id}}" readonly></td>
                                                <td><input type="hidden" name="attributeid" value="{{$bxp->id}}" >
                                                <input type="button" name="update" value="Update" class="btn-success" onClick="updateattributeprice('{{$bxp->id}}')"> </td>
                                                <td>
                                                        <form action="{{ route('product.filter.delete', $bxp->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this color variant?');" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden"  name="attributid" value="{{$bxp->id}}">
                                                        <input type="submit" name="Delete" value="Delete" class="btn-danger">
                                                        </form>
                                                </td>
                                                </tr>
                                            @endforeach
                                            @endif
                                            <tr>
                                                <form method="post" action="{{route('product.addproductattribute')}}">
                                                    @csrf
                                                <td><select class="form-control" name="colorid"><option value="">Select Color</option>
                                                @foreach($shadecard as $colr)<option value="{{$colr->id}}">{{$colr->name}}</option>@endforeach</select></td>
                                                <td><select class="form-control" name="packingid"><option value="">Select Packing</option>@foreach(Helper::getboxpacking() as $boxpck)<option value="{{$boxpck->id}}">{{$boxpck->name}}</option>@endforeach</select></td>
                                                <td><input type="text"  name="price" id="new_price" placeholder="Enter price" oninput="calculateNewRowPrice()"></td>
                                                <td><input type="text" name="get_price" id="new_get_price" readonly>
                                                <input type="hidden"  name="productid" value="{{$detail->id}}">
                                                <input type="submit" name="update" value="Add" class="btn-primary"></td>
                                                </form>
                                            </tr>
                                       </tbody>
                                        </table>
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
    // Pass the PHP commission variable directly into JavaScript
    // Assuming $commission is a numeric string or float (e.g., 5 or 10)
    const commission = parseFloat("{{  $companydetail->comission  ?? 0 }}") || 0;

    // 1. Handles calculations for existing loops
   function calculateGetPrice(id) {
    const priceInput = document.getElementById('price' + id);
    const getPriceInput = document.getElementById('get_price' + id);
    
    const priceValue = parseFloat(priceInput.value) || 0;
    
    if (priceValue > 0) {
        // Auto-calculate: Price minus Commission Percentage
        const commissionAmount = priceValue * (commission / 100);
        getPriceInput.value = (priceValue - commissionAmount).toFixed(2);
    } else {
        getPriceInput.value = '';
    }
}

// 2. Handles calculations for the "Add New Item" bottom row
function calculateNewRowPrice() {
    const priceInput = document.getElementById('new_price');
    const getPriceInput = document.getElementById('new_get_price');
    
    const priceValue = parseFloat(priceInput.value) || 0;
    
    if (priceValue > 0) {
        // Auto-calculate: Price minus Commission Percentage
        const commissionAmount = priceValue * (commission / 100);
        getPriceInput.value = (priceValue - commissionAmount).toFixed(2);
    } else {
        getPriceInput.value = '';
    }
}

    // Optional: Run on page load to fill values for pre-existing prices
    document.addEventListener("DOMContentLoaded", function() {
        @if(!empty($attributelist))
            @foreach($attributelist as $bxp)
                calculateGetPrice('{{$bxp->id}}');
            @endforeach
        @endif
    });
</script>
@endsection