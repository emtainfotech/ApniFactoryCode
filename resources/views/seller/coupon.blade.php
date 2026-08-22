@extends('layout')
@section('title',$title)
@section('content')
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
					  <h5 class="card-title">Add {{$title}}</h5>
					  <hr/>
                       <div class="form-body mt-4">
					    <div class="row">
                            <div class="col-lg-2"></div>
						   <div class="col-lg-8">
						       @foreach($errors->all() as $error)
						             <div class="alert alert-danger">{{ $error ?? ''  }}</div>
                                @endforeach
                            <form method="post" enctype='multipart/form-data'>
                                @csrf
                           <div class="border border-3 p-4 rounded">
                              <div class="mb-3">
                                <label for="inputProductType" class="form-label">Coupon On</label>
                                <select class="form-select" id="inputProductType" name="couponon" onchange="getComboA(this)" required>
                                    <option></option>
                                    <option value="1">Brand</option>
                                    <option value="2">Product</option>
                                  </select>
                              </div>
                              <div class="mb-3">
                                <label for="inputProductType" class="form-label">Select From List</label>
                                <select class="form-select" id="brandpro" name="couponof[]" multiple>
                                    <option></option>
                                  </select>
                              </div>
							<div class="mb-3">
								<label for="inputProductTitle" class="form-label"> Title</label>
								<input type="text" name="title" class="form-control" id="inputProductTitle" placeholder="Enter {{$title}} " required>
							  </div>
							<div class="mb-3">
								<label for="inputProductTitle" class="form-label"> Code</label>
								<input type="text" name="code" class="form-control" id="inputProductTitle" placeholder="Enter {{$title}} code " required>
							  </div>
                              <div class="mb-3">
                                <label for="inputProductType" class="form-label">Coupon Type</label>
                                <select class="form-select" id="inputProductType" name="type" required>
                                    <option></option>
                                    <option value="amount">Amount</option>
                                    <option value="percentage">Percentage</option>
                                  </select>
                              </div>
							<div class="mb-3">
								<label for="inputProductTitle" class="form-label">Figure</label>
								<input type="number" class="form-control" name="amount" placeholder="Enter Amount " required>
							  </div>
							<div class="mb-3">
								<label for="inputProductTitle" class="form-label"> Start Date</label>
								<input type="date" name="startdate" class="form-control" id="inputProductTitle" placeholder="Enter {{$title}} " required>
							  </div>
							<div class="mb-3">
								<label for="inputProductTitle" class="form-label"> End Date</label>
								<input type="date" name="enddate" class="form-control" id="inputProductTitle" placeholder="Enter {{$title}} " required>
							  </div>
							<div class="mb-3">
								<label for="inputProductTitle" class="form-label">Image</label>
								<input type="file" name="image" class="form-control" >
							  </div>
                              <div class="mb-3">
                                <div class="d-grid">
                                   <button type="submit" class="btn btn-primary">Save</button>
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
                <!----------------list of Brands----------------------->
                   <div class="card">
				  <div class="card-body p-4">
					  <h5 class="card-title"> Your {{$title}} List</h5>
					  <hr/>
					 
                       <div class="form-body mt-4">
					    <div class="row">
                            <div class="table-responsive">
                                    <table id="example2" class="table table-striped table-bordered" style="width:100%">
                                        <thead>
                                        <tr>      
                                            <th>S.no</th>
                                            <th>Image</th>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>type</th>
                                            <th>Amount</th>
                                            <th>Enddate</th>
                                            <th>status</th>
                                            <th>Couponon</th>
                                            <th>ApplyOn</th>
                                            <th>CreateDate</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(!empty($list))
                                              @foreach($list as $key=>$lt)
                                         <tr>
                                             <td>{{$key+1}}</td>
                                              <td><img src="{{ env('imagepath').'/'.$lt->image }}" width="100px"/></td>
                                             <td>{{substr($lt->code, 0, 15)}}</td>
                                             <td>{{substr($lt->name, 0, 15)}}</td>
                                             <td>{{$lt->type}}</td>
                                             <td>{{$lt->price}}</td>
                                             <td>{{$lt->expiry}}</td>
                                             <td>@if($lt->status==1) <b class="text-success">Active</b>@else <b class="text-danger">Deactive</b>@endif</td>
                                             <td>{{$lt->couponon}}</td>
                                             <td>
                                                 @foreach(json_decode($lt->couponapplyon) as $applyon)
                                                 @if($lt->couponon=='Brand')
                                                    {{Helper::getbrandname($applyon)}}</br>
                                                 @else
                                                    {{substr(Helper::getproductname($applyon), 0, 15)}}</br>
                                                 @endif
                                                 @endforeach</td>
                                             <td>{{date("d-m-Y",strtotime($lt->created_at))}}</td>
                                             <td>
<!--                                                  <a class="btn btn-sm btn-icon btn-primary" href="{{ route('product.edit', $lt->id) }}">-->
<!--    <svg class="icon" data-bs-toggle="tooltip" data-bs-title="Edit" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">-->
<!--  <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>-->
<!--  <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"></path>-->
<!--  <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"></path>-->
<!--  <path d="M16 5l3 3"></path>-->
<!--</svg>            -->
<!--    <span class="sr-only">Edit</span>-->
<!--</a>-->
                                                 <form action="{{ route('coupon.delete', $lt->id) }}" method="POST"  onSubmit="return confirm('Are you sure to delete this record?')">
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
                                        @endif
                                    </tbody>
                                        </table>
                                        
					   </div><!--end row-->
					</div>
				  </div>
			  </div>
			</div>
		</div>
		<!--end page wrapper -->
 
@endsection