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
					  <h5 class="card-title">Send Request To Add {{$title}}</h5>
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
								<label for="inputProductTitle" class="form-label"> Name</label>
								<input type="text" class="form-control" name="name" placeholder="Enter {{$title}} ">
							  </div>
							<div class="mb-3">
								<label for="inputProductTitle" class="form-label"> Content</label>
								<textarea rows="5" class="form-control" name="content"></textarea>
							  </div>
							<div class="mb-3">
								<label for="inputProductTitle" class="form-label"> File</label>
								<input type="file" class="form-control" id="inputProductTitle" name="image">
							  </div>
                              <div class="mb-3">
                                <div class="d-grid">
                                   <button type="submit" class="btn btn-primary">Submit </button>
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
					  <h5 class="card-title"> Already Requested {{$title}} List</h5>
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
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(!empty($list))
                                       
                                             @foreach($list as $key=>$lt)
                                         <tr>
                                             <td>{{$key+1}}</td>
                                              <td><img src="{{ env('imagepath').'/'.$lt->file }}" width="100px"/></td>
                                             <td>{{$lt->name}}</td>
                                             <td>{{date("d-m-Y",strtotime($lt->created_at))}}</td>
                                             <td>
                                                @if(!empty($lt->adminmsg))<small class="text-danger"><b>{{$lt->adminmsg}}</b></small>@endif</br>
                                               @if($lt->status==1) <b class="text-success">Active</b>@else <b class="text-danger">Deactive</b>@endif</td>
                                             
                                                 
                                             </td>
                                                 <td>
                                                 <form action="{{ route('advertisement.delete', $lt->id) }}" method="POST"  onSubmit="return confirm('Are you sure to delete this record?')">
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