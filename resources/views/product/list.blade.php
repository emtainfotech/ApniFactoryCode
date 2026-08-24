@extends('layout')
@section('title', 'Home Page')
@section('content')

		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">

				<!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<div class="breadcrumb-title pe-3">Product</div>
					<div class="ps-3">
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0 p-0">
								<li class="breadcrumb-item"><a href="javascript:;">Home</a>
								</li>
								<li class="breadcrumb-item active" aria-current="page">Add New Product</li>
							</ol>
						</nav>
					</div>
					<div class="ms-auto">
						
					</div>
				</div>
				<!--end breadcrumb-->
 <div class="card">
				  <div class="card-body p-4"> <h5 class="card-title"> {{$title}} Filter</h5>
					  <hr/>
                   <form method="GET" class="row" >
        @csrf
        <div class="form-group col-md-3">
            <label for="order_no">{{$title}} Name:</label>
            <input type="text" class="form-control" id="order_no" name="product" value="{{ request('product') }}" placeholder="Enter {{$title}} Name">
        </div>
        <div class="form-group col-md-3">
            <label for="status">Category:</label>
            <select class="form-control" id="category" name="srchcategory">
                <option value="">Select category</option>
                  @foreach($categorylist as $ctid)
                                    <option value="{{$ctid->id}}">{{$ctid->name}}</option>
                                    @endforeach
            </select>
        </div>
        <div class="form-group col-md-3">
            <label for="status">Brand:</label>
            <select class="form-control" id="Brand" name="Brand">
                <option value="">Select Brand</option>
                  @foreach($Brandlist as $brid)
                                    <option value="{{$brid->id}}">{{$brid->name}}</option>
                                    @endforeach
            </select>
        </div>
        <div class="form-group col-md-2">
        <button type="submit" class="btn btn-warning" style="margin-top: 20px;">Search</button>
        </div>
    </form>
    </div>
    </div>
              <div class="card">
				  <div class="card-body p-4">
					  <h5 class="card-title"> Product List</h5>
					  <hr/>
                       <div class="form-body mt-4">
					    <div class="row">
						   <div class="col-lg-12">
                           <div class="border border-3 p-4 rounded">
						
                              <div class="mb-3">
                                   @foreach($errors->all() as $error)
						             <div class="alert alert-danger">{{ $error ?? ''  }}</div>
                                @endforeach
                                <div class="table-responsive">
                                    <table id="example2" class="table table-striped table-bordered" style="width:100%">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th>Product Image</th>
                                            <th>Brand</th>
                                            <th>Product name</th>
                                            <th>Category</th>
                                            <th>Status</th>
                                            <th>date </th>
                                            <th>Action </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                         @foreach($list as $key=>$lt)
                                         <tr>
                                             <td>{{$key+1}}</td>
                                              <td><img src="{{ env('imagepath').'/'.$lt->image }}" width="100px"/></td>
                                             <td>{{Helper::getbrandname($lt->brand_id)}}</td>
                                             <td>{{substr($lt->name, 0, 15)}}</td>
                                             <td>{{Helper::getcatname($lt->category_id)}}</td>
                                             <td>@if($lt->status==1) <b class="text-success">Active</b>@else <b class="text-danger">Deactive</b>@endif</td>
                                             <td>{{date("d-m-Y",strtotime($lt->created_at))}}</td>
                                             <td>
                                                   <a class="btn btn-sm btn-icon btn-primary" href="{{ route('product.edit', $lt->id) }}" title="Edit Product">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                        <span class="sr-only">Edit</span>
                                                    </a>
                                                    <a class="btn btn-sm btn-icon btn-info text-white" href="{{ route('seller.paint-pricing.index', ['product_id' => $lt->id]) }}" title="Smart Paint Pricing">
                                                        <i class="fa-solid fa-paint-roller"></i>
                                                    </a>
                                                 <form action="{{ route('product.delete', $lt->id) }}" method="POST"  onSubmit="return confirm('Are you sure to delete this record?')" style="display: inline-block;">
                                                      @csrf
                                                      @method("DELETE")
                                                      <button type="submit" class="btn btn-danger"> <svg class="icon" data-bs-toggle="tooltip" data-bs-title="Delete" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                          <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                          <path d="M4 7l16 0"></path>
                                                          <path d="M10 11l0 6"></path>
                                                          <path d="M14 11l0 6"></path>
                                                          <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"></path>
                                                          <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"></path>
                                                        </svg>            
                                                            <span class="sr-only">Delete</span></button>
                                                    </form>
                                                    </td>
                                         </tr>
                                        @endforeach
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
 
@endsection