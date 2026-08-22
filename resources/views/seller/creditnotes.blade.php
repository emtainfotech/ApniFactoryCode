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
					  <h5 class="card-title text-center"> Current = <span class="text-bold text-info">{{$currentbalance}}</span></h5>
					  <hr/>
					</div>
					</div>
               @foreach($errors->all() as $error)
						             <div class="alert alert-danger">{{ $error ?? ''  }}</div>
                                @endforeach
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
                                            <th>View</th>
                                            <th>Date</th>
                                            <th>Orderno</th>
                                            <th>Value</th>
                                            <th>Commission</th>
                                            <th>Refundtobuyer</th>
                                            <th>Debit</th>
                                            <th>Credit</th>
                                            <th>Balance</th>
                                            <th>Msg</th>
                                        </tr>   
                                         </thead>
                                         <tbody>
                                        @foreach($list as $key=>$lt)
                                         <tr>
                                             <td>{{$key+1}}</td>
                                             <td> <a class="btn-sm btn-icon btn-primary" href="{{ route('creditnotes.detail',$lt->id) }}"><i class="fa-regular fa-eye"></i><span class="sr-only">View</span></a></td>
                                             <td>{{date("d-m-Y",strtotime($lt->created_at))}}</td>
                                             <td>{{$lt->orderno}}</td>
                                             <td>{{$lt->value}}</td>
                                             <td>{{$lt->commission}}</td>
                                             <td>{{$lt->refundtobuyer}}</td>
                                             <td>{{$lt->debit}}</td>
                                             <td>{{$lt->credit}}</td>
                                             <td>{{$lt->balance}}</td>
                                             <td>{{$lt->msg}}</td>
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