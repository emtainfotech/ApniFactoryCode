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
					  <h5 class="card-title">Add New Product</h5>
					  <hr/>
                       <div class="form-body mt-4">
					    <div class="row">
                            <div class="col-lg-2">
						
                            </div>
						   <div class="col-lg-8">
						       <form method="post" action="{{route('product.store')}}" enctype='multipart/form-data'>
						           @csrf
						           <input type="hidden" name="previousdata" value="{{json_encode($formdata)}}">
                           <div class="border border-3 p-4 rounded">
                               
							  <div class="mb-3">
							<div class="upload__box">
                              <div class="upload__btn-box">
                                <label class="upload__btn">
                                  <p>Upload images</p>
                                  <input type="file" multiple="" data-max_length="20" class="upload__inputfile" name="images[]" required="">
                                </label>
                         </div>
                              <div class="upload__img-wrap"></div>
                            </div>
							  </div>
                              <div class="mb-3">
                                <div class="table-responsive">
                                    <table id="example" class="table table-striped table-bordered" style="width:100%" data-commission="{{ $companydetail->comission }}">
                                        <tbody>
                                        <tr>
                                            <th>Packing</th>
                                            <th>Color</th>
                                            <th>Price</th>
                                            <th>You Receive(-{{$companydetail->comission}})% </th>
                                        </tr>
                                        @foreach($formdata['attributes'] as $key=>$attr)
                                           <tr>
                                         @php  $dt = explode('-&-',$attr);@endphp
						                    <input type="hidden" value="{{$dt[0]}}" name="boxpacking[]">
						                    <input type="hidden"  value="{{$dt[1]}}" name="color[]">
                                            <td>{{Helper::getboxpackingnamebyid($dt[0])}}</td>
                                            <td>{{Helper::getcolorsnamebyid($dt[1])}}</td>
                                            <td><input type="number" name="price[]" class="price-input" step="any" placeholder="Enter Price"/></td>
                <td><input type="text"  class="getprice-input" readonly /></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                        </table>
                                        </div>

                              </div>
                              <div class="mb-3">
                                <div class="d-grid">
                                   <button type="submit" class="btn btn-primary">Save Product</button>
                                </div>
                            </div>
                            </div>
                            </form>
						   </div>
						   <div class="col-lg-2">
						
						  </div>
					   </div><!--end row-->
					</div>
				  </div>
			  </div>
			</div>
		</div>
		<!--end page wrapper -->
 <script>
     document.addEventListener('DOMContentLoaded', function () {
    // Table se commission percentage nikalna (e.g., 25)
    const table = document.getElementById('example');
    const commissionPercent = parseFloat(table.getAttribute('data-commission')) || 0;

    // Price input fields par event listener lagana
    table.addEventListener('input', function (event) {
        // Sirf tabhi trigger ho jab user price input me kuch likhe
        if (event.target.classList.contains('price-input')) {
            const priceInput = event.target;
            const priceValue = parseFloat(priceInput.value);

            // Current row (tr) ko dhoondna jisme input badla hai
            const currentRow = priceInput.closest('tr');
            const getPriceInput = currentRow.querySelector('.getprice-input');

            if (!isNaN(priceValue) && priceValue > 0) {
                // Formula: Price - (Price * Commission / 100)
                const discountedPrice = priceValue - (priceValue * commissionPercent / 100);
                
                // Value ko 2 decimal places tak set karna (e.g., 75.00)
                getPriceInput.value = discountedPrice.toFixed(2);
            } else {
                // Agar input khali hai ya valid number nahi hai
                getPriceInput.value = '';
            }
        }
    });
});

 </script>
@endsection