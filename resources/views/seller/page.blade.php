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
                           
						   <div class="col-lg-12">
                            @foreach($errors->all() as $error)
						             <div class="alert alert-danger">{{ $error ?? ''  }}</div>
                                @endforeach
                          
                            <form method="post" enctype='multipart/form-data'>
                                @csrf
                                <input type="hidden" name="slug" value="{{$title}}"/>
                           <div class="border border-3 p-4 rounded">
                              
                              <div class="mb-3">
                                <label for="Description" class="form-label"> Description</label>
                                <textarea class="form-control" id="Description" name="description" rows="25">
                                 @if(!empty($list->description))
                                    {{$list->description}}
                                @endif
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
						  
					   </div><!--end row-->
					</div>
				  </div>
			  </div>
                <!----------------list of Brands----------------------->
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
										<th>Page</th>
										<th>LastUpdateOn</th>
									</tr>
								</thead>
								<tbody>
                                        @if(!empty($logslist))
                                              @foreach($logslist as $key=>$lt)
                                         <tr>
                                             <td>{{$key+1}}</td>
                                             <td>{{$lt->pagename}}</td>
                                             <td>{{$lt->updateon}}</td>
                                             <!--<td>{{date("d-m-Y",strtotime($lt->created_at))}}</td>-->
                                          
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
