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
					  <h5 class="card-title">{{$title}}</h5>
					  <hr/>
                       <div class="form-body mt-4">
					    <div class="row">
                            <div class="col-lg-2"></div>
						   <div class="col-lg-8">
						         @foreach($errors->all() as $error)
						             <div class="alert alert-danger">{{ $error ?? ''  }}</div>
                                @endforeach
                           <div class="border border-3 p-4 rounded">
							<div class="mb-3">
								<label for="inputProductTitle" class="form-label"> Company</label>
								<input type="text" class="form-control" id="inputProductTitle" readonly value="{{$detail->name}}">
							  </div>
							<div class="mb-3">
								<label for="inputProductTitle" class="form-label"> Name</label>
								<input type="text" class="form-control" id="inputProductTitle" readonly value="{{Auth::user()->name}}">
							  </div>
							<div class="mb-3">
								<label for="inputProductTitle" class="form-label"> Email</label>
								<input type="email" class="form-control" id="inputProductTitle" readonly value="{{$detail->email}}">
							  </div>
							<div class="mb-3">
								<label for="inputProductTitle" class="form-label"> Mobile</label>
								<input type="number" class="form-control" id="inputProductTitle" readonly value="{{$detail->mobile}}">
							  </div>
							<div class="mb-3">
								<label for="inputProductTitle" class="form-label"> Whatsapp No</label>
								<input type="number" class="form-control" id="inputProductTitle" readonly value="{{$detail->mobile}}">
							  </div>
							<div class="mb-3">
								<label for="inputProductTitle" class="form-label"> Pancard</label>
								<input type="text" class="form-control" id="inputProductTitle" readonly value="">
							  </div>
							<div class="mb-3">
								<label for="inputProductTitle" class="form-label"> CRN No</label>
								<input type="text" class="form-control" id="inputProductTitle" readonly value="{{$detail->crn}}">
							  </div>
							<div class="mb-3">
								<label for="inputProductTitle" class="form-label"> GST no</label>
								<input type="text" class="form-control" id="inputProductTitle" readonly value="{{$detail->gst}}">
							  </div>
                            </div>
						   </div>
						   <div class="col-lg-2">
						
						  </div>
					   </div><!--end row-->
					</div>
				  </div>
			  </div>
                 
              <div class="card">
				  <div class="card-body p-4">
					  <h5 class="card-title">Change Password</h5>
					  <hr/>
                       <div class="form-body mt-4">
					    <div class="row">
                            <div class="col-lg-2"></div>
						   <div class="col-lg-8">
                            <form method="post" action="{{url('seller/profile',$detail->id)}}">
                                @csrf
                                <input type="hidden" class="form-control" name="action" value="passwordupdate">
                           <div class="border border-3 p-4 rounded">
							<div class="mb-3">
								<label for="inputProductTitle" class="form-label"> Old Password</label>
								<input type="password" class="form-control" name="currentpassword"  value="Old Password">
							  </div>
							<div class="mb-3">
								<label for="inputProductTitle" class="form-label"> New Password</label>
								<input type="password" class="form-control" name="newpassword"  value="New">
							  </div>
                              <div class="mb-3">
                                <div class="d-grid">
                                   <button type="submit" class="btn btn-primary">Update Password</button>
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
              <div class="card">
				  <div class="card-body p-4">
					  <h5 class="card-title">Min Order Value</h5>
					  <hr/>
                       <div class="form-body mt-4">
					    <div class="row">
                            <div class="col-lg-2"></div>
						   <div class="col-lg-8">
                            <form method="post" action="{{url('seller/profile',$detail->id)}}">
                                @csrf
                                <input type="hidden" class="form-control" name="action" value="minordervalue">
                           <div class="border border-3 p-4 rounded">
							<div class="mb-3">
								<label for="inputProductTitle" class="form-label">Min Order Value</label>
								<input type="text" class="form-control" name="minvalue"  value="{{$detail->minordervalue}}">
							  </div>
                              <div class="mb-3">
                                <div class="d-grid">
                                   <button type="submit" class="btn btn-secondary" name="minordervalue">Update Min Order Value</button>
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
              <div class="card">
				  <div class="card-body p-4">
					  <h5 class="card-title">Company Banner / Company Logo</h5>
					  <hr/>
                       <div class="form-body mt-4">
					        <div class="">
					        
					    <div class="row justify-content-between border border-3 p-4 roundedborder border-3 p-4 rounded">
                            <div class="col-md-5">
                                <form method="post" enctype="multipart/form-data" action="{{url('seller/profile',$detail->id)}}">
                                @csrf
                                <input type="hidden" class="form-control" name="action" value="update_banner">
                                <div class="mb-3">
								<label for="inputProductTitle" class="form-label">Banner Image</label>
								@if($detail->photo)
                                    <img src="{{ asset('storage/app/public/' . $detail->photo) }}" width="100%" class="mb-2 rounded">
                                @endif
								<input type="file" class="form-control" name="banner_image" accept="image/*" >
							  </div>
								<div class="mb-3">
                                <div class="d-grid">
                                   <button type="submit" class="btn btn-success" >Update Banner</button>
                                </div>
                                </div>
							  </form>
                            </div>
						   <div class="col-md-5">
                                <form method="post" enctype="multipart/form-data" action="{{url('seller/profile',$detail->id)}}">
                                @csrf
                                <input type="hidden" name="action" value="update_logo">
                            <div class="mb-3">
								<label for="inputProductTitle" class="form-label">Company Logo</label>
								@if($detail->logo)
                    <img src="{{ asset('storage/app/public/' . $detail->logo) }}" width="150px" class="mb-2 d-block rounded">
                @endif
								
							<input type="file" class="form-control" name="company_logo" accept="image/*" required>
						   </div>
								<div class="mb-3">
                                <div class="d-grid">
                                   <button type="submit" class="btn btn-warning">Update Logo</button>
                                </div>
                            </div>
							  </form>
					   </div><!--end row-->
					   </div>
					</div>
				  </div>
			  </div>
			</div>
		</div>
		<!--end page wrapper -->
 
@endsection