{{dd($list)}}
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
					  <h5 class="card-title"> {{$title}}</h5>
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
                                    <option value="{{$mid->id}}">{{$mid->name}}</option>
                                    @endforeach
                                </select>
                              </div>
							<div class="mb-3">
								<label for="inputProductTitle" class="form-label"> Name</label>
								<input type="text" name="name" class="form-control" id="inputProductTitle" placeholder="Enter {{$title}} ">
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
					  <h5 class="card-title"> Already Requested {{$title}} List</h5>
					  <hr/>
                       <div class="form-body mt-4">
					    <div class="row">
					        <?php /*
                            <div class="table-responsive">
                                    <table id="example" class="table table-striped table-bordered" style="width:100%">
                                        <tbody>
                                        <tr>
                                            <th>S.no</th>
                                            <th>Image</th>
                                            <th>Category</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                             @foreach($list as $key=>$lt)
                                         <tr>
                                             <td>{{$key+1}}</td>
                                             <td><img src="{{ env('imagepath').'/'.$lt->image }}" width="100px"/></td>
                                             <td>{{Helper::getmaincatname($lt->maincategory_id)}}</td>
                                             <td>{{$lt->name}}</td>
                                             <td>@if($lt->status==1) <b class="text-success">Active</b>@else <b class="text-danger">Deactive</b>@endif</td>
                                             <td>{{date("d-m-Y",strtotime($lt->created_at))}}</td>
                                             <td>
                                                 @if($lt->adminstatus=='reject')
                                                 <a class="btn btn-sm btn-icon btn-danger" href="" >{{$lt->adminstatus}} <span class="sr-only"></span></a>
                                                 @if(!empty($lt->adminmsg))<small class="text-danger"><b>{{$lt->adminmsg}}</b></small>@endif
                                                 @else
                                                 <a class="btn btn-sm btn-icon btn-info" href="" >{{$lt->adminstatus}} <span class="sr-only"></span></a>
                                                 @endif
                                                 
                                             </td>
                                         </tr>
                                        @endforeach
                                    </tbody>
                                        </table>
                                        
					   </div><!--end row-->
					  */ ?>
					</div>
				  </div>
			  </div>
			</div>
		</div>
		<!--end page wrapper -->
 
@endsection