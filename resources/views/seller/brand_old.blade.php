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
					  <h5 class="card-title">Add Your {{$title}}</h5>
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
                                <label for="inputProductType" class="form-label">Main Category</label>
                                <select class="form-select"  name="maincategory" id="maincategoryforajax" required>
                                    <option></option>
                                    @foreach(Helper::getmaincat() as $mid)
                                    <option value="{{$mid->id}}" @if($mid->id==$maincatid) selected @endif>{{$mid->name}}</option>
                                    @endforeach
                                  </select>
                              </div>
                              <div class="mb-3">
                                <label for="inputProductType" class="form-label">Sub Category</label>
                                <select class="form-select" id="subcatforajax" name="category[]" multiple required>
                                  @foreach($categorylist as $caatid)
                                    <option value="{{$caatid->id}}">{{$caatid->name}}</option>
                                    @endforeach
                                  </select>
                              </div>
							<div class="mb-3">
							    <a href="category" style="float: right; color: blue;text-decoration: underline;">Add New Category Request</a>
                              </div>
							<div class="mb-3">
								<label for="inputProductTitle" class="form-label"> Name</label>
								<input type="text" class="form-control" name="name" id="inputProductTitle" placeholder="Enter {{$title}} ">
							  </div>
							<div class="mb-3">
								<label for="inputProductTitle" class="form-label"> Trademark Number</label>
								<input type="text" class="form-control" name="tno" id="inputProductTitle" placeholder="Enter {{$title}} Trademark Number">
							  </div>
							<div class="mb-3">
								<label for="inputProductTitle" class="form-label">Brand Logo</label>
								<input type="file" name="image" class="form-control" >
							  </div>
							<!--<div class="mb-3">-->
							<!--	<label for="inputProductTitle" class="form-label">File of Brand Trademark</label>-->
							<!--	<input type="file" name="tfile" class="form-control" >-->
							<!--  </div>-->
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
				  <div class="card-body p-4"> <h5 class="card-title"> {{$title}} Filter</h5>
					  <hr/>
                   <form method="GET" class="row" >
        @csrf
        <div class="form-group col-md-2">
            <label for="order_no">Brand Name:</label>
            <input type="text" class="form-control" id="order_no" name="brand" value="{{ request('brand') }}" placeholder="Enter Brand Name">
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
        <div class="form-group col-md-2">
        <button type="submit" class="btn btn-warning" style="margin-top: 20px;">Search</button>
        </div>
    </form>
    </div>
    </div>
                   <div class="card">
				  <div class="card-body p-4">
					  <h5 class="card-title"> {{$title}} List</h5>
					  <hr/>
                       <div class="form-body mt-4">
					    <div class="row">
                            <div class="table-responsive">  
                                   
                                    <table id="example2" class="table table-striped table-bordered" style="width:100%">
                                        <thead>
                                        <tr>
                                  <th>S.no</th>
                                            <th>Image</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                            <th>MainCategory</th>
                                            <th>Category</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>   
                                        </thead>
                                        <tbody>{{dd($list)}}
                                        @foreach($list as $key=>$lt)
                                        @php print_r($lt);@endphp
                                         <tr>
                                             <td>{{$key+1}}</td>
                                              <td><img src="{{ env('imagepath').'/'.$lt->image }}" width="100px"/></td>
                                             <td>{{$lt->name}}</td>
                                             <td>{{$lt->type}} / @if($lt->status==1) <b class="text-success">Active</b>@else <b class="text-danger">Deactive</b>@endif</td>
                                             <td>{{Helper::getmaincatname($lt->mid)}}</td>
                                             <td>{{$lt->categories}}</td>
                                             <td>{{date("d-m-Y",strtotime($lt->created_at))}}</td>
                                             <td>
                                               @if($lt->adminresponse!='')  
                                                <p class="text-info" style="display: contents;">{{$lt->adminresponse}}</p>
                                               @else
                                               <form action="{{ route('brand.delete', $lt->id) }}" method="POST"  onSubmit="return confirm('Are you sure to delete this record?')">
                                                      @csrf
                                                      @method("DELETE")
                                                      <button type="submit" class="btn btn"> <svg class="icon" data-bs-toggle="tooltip" data-bs-title="Delete" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                          <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                          <path d="M4 7l16 0"></path>
                                                          <path d="M10 11l0 6"></path>
                                                          <path d="M14 11l0 6"></path>
                                                          <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"></path>
                                                          <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"></path>
                                                        </svg>            
                                                            <span class="sr-only">Delete</span></button>
                                                    </form>
                                                @endif
                                                  
                                                    <button type="button" class="btn btn" data-bs-toggle="modal" data-bs-target="#exampleModal{{$key+1}}" data-bs-whatever="@getbootstrap"><i class="fas fa-edit"></i></button>

                                                   <div class="modal fade" id="exampleModal{{$key+1}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                      <div class="modal-dialog">
                                                        <div class="modal-content">
                                                          <div class="modal-header">
                                                            <h5 class="modal-title" id="exampleModalLabel">Change Status</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                          </div>
                                                            <form action="{{ route('brand.update', $lt->id) }}" method="post">
                                                                @csrf
                                                          <div class="modal-body">
                                                              <div class="mb-3">
                                                                  <input type="hidden" name="upid" value="{{$lt->id}}"/>
                                                                <label for="recipient-name" class="col-form-label">Status:</label>
                                                                <select name="changestatus" class="form-control">
                                                                    <option value="1">Active</option>
                                                                    <option value="0">Deactive</option>
                                                                </select>
                                                              </div>
                                                          </div>
                                                          <div class="modal-footer">
                                                            <button type="submit" class="btn btn-primary">Update</button>
                                                          </div>
                                                            </form>
                                                        </div>
                                                      </div>
                                                    </div>
                                                    </td>
                                         </tr>
                                        @endforeach
                                            
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