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
                                <label for="inputfaqhead" class="form-label">Faq's Heading</label>
                                <input type="text" class="form-control" id="inputfaqhead" name="question">
                              </div>
                              <div class="mb-3">
                                <label for="fqadescription" class="form-label">Faq's Description</label>
                                <textarea class="form-control" id="fqadescription" name="answer" rows="5">
                                 
                                  </textarea>
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
					  <h5 class="card-title"> {{$title}} List</h5>
					  <hr/>
                       <div class="form-body mt-4">
					    <div class="row">
                           
					   
					   <div class="card">
					<div class="card-body">
						<div class="table-responsive">
							<table id="example2" class="table table-striped table-bordered">
								<thead>
									<tr>
										<th>S.no</th>
										<th>Question</th>
										<th>Answer</th>
										<th>date</th>
										<th>status</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
								
                                              @foreach($list as $key=>$lt)
                                         <tr>
                                             <td>{{$key+1}}</td>
                                             <td>{{substr($lt->question, 0, 15)}}</td>
                                             <td>{{substr($lt->answer, 0, 15)}}</td>
                                             <td>{{date("d-m-Y",strtotime($lt->created_at))}}</td>
                                             <td>@if($lt->status==1) <b class="text-success">Active</b>@else <b class="text-danger">Deactive</b>@endif</td>
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
                                                 <form action="{{ route('faq.delete', $lt->id) }}" method="POST"  onSubmit="return confirm('Are you sure to delete this record?')">
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
						
							</table>
						</div>
					</div>
				</div>
					</div>
				  </div>
			  </div>
			</div>
		</div>
		<!--end page wrapper -->

@endsection
