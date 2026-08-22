@extends('layout')
@section('title',$title)
@section('content')
<style>
    table#example2 p {
    text-wrap: wrap;
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
                           
						   <div class="col-lg-12">
						       @foreach($errors->all() as $error)
						             <div class="alert alert-danger">{{ $error ?? ''  }}</div>
                                @endforeach
                            <form method="post" enctype='multipart/form-data'>
                                @csrf
									<div class="col-md-12">
										<label for="inputtopic" class="form-label">Your Topic</label>
										<input type="text" class="form-control" name="topic">
									</div>
									<div class="col-md-12">
										<label for="inputmessage" class="form-label">Message</label>
										<textarea rows="10" class="form-control" name="message"></textarea>
									</div>
								
								
									<div class="col-12">
										<button type="submit" class="btn btn-primary px-5">Submit</button>
									</div>
								</form>
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
                     <div class="card">
					<div class="card-body">
						<div class="table-responsive">
							<table id="example2" class="table table-striped table-bordered">
								<thead>
									<tr>
										<th>S.No.</th>
										<th>Topic</th>
										<th>Message</th>
										<th>Status</th>
										<th>date</th>
									
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
                                        @if(!empty($list))
                                              @foreach($list as $key=>$lt)
                                         <tr>
                                             <td>{{$key+1}}</td>
                                             <td>{{substr($lt->topic, 0, 15)}}</td>
                                             <td>{{substr($lt->msg, 0, 15)}}</td>
                                             <td>
                                                 @if($lt->status=='Reject')
                                                 <a class="btn btn-sm btn-icon btn-danger" href="" >{{$lt->status}} <span class="sr-only"></span></a></br>
                                                 @if(!empty($lt->adminmsg))<small class="text-info"><b>{{$lt->adminmsg}}</b></small>@endif
                                                 @else
                                                 <a class="btn btn-sm btn-icon btn-info" href="" >{{$lt->adminstatus}} <span class="sr-only"></span></a>
                                                 @endif
                                                 
                                             </td>
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
                                                 <form action="{{ route('ticketsupport.delete', $lt->id) }}" method="POST"  onSubmit="return confirm('Are you sure to delete this record?')">
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
							</table>
						</div>
					</div>
				</div>
				  </div>
			  </div>
		</div>
		<!--end page wrapper -->

@endsection
