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
                   <form method="GET" class="row" >
        @csrf
        <div class="form-group col-md-2">
            <label for="from_date">From Date:</label>
            <input type="date" class="form-control" id="from_date" name="from_date" value="{{ $request->from_date ?? '' }}">
        </div>
        <div class="form-group col-md-2">
            <label for="to_date">To Date:</label>
            <input type="date" class="form-control" id="to_date" name="to_date" value="{{ $request->to_date ?? '' }}">
        </div>
        <div class="form-group col-md-2">
            <label for="order_no">Order No:</label>
            <input type="text" class="form-control" id="order_no" name="order_no" value="{{ request('order_no') }}">
        </div>
        <div class="form-group col-md-2">
            <label for="txn_id">Transaction ID:</label>
            <input type="text" class="form-control" id="txn_id" name="txn_id" value="{{ request('txn_id') }}">
        </div>
        <div class="form-group col-md-2">
            <label for="status">Status:</label>
            <select class="form-control" id="status" name="status">
                <option value="">Select Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
            </select>
        </div>
        <div class="form-group col-md-2">
        <button type="submit" class="btn btn-primary" style="margin-top: 20px;">Search</button>
        </div>
    </form>
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
                                            <th>Date</th>
                                            <th>Orderno</th>
                                            <th>Status</th>
                                            <th>TxnId</th>
                                            <th>TxnDetail</th>
                                            <th>TxnResponse</th>
                                            <th>TxnMethod</th>
                                        </tr> 
                                          </thead>
                                    <tbody>
                                        @foreach($list as $key=>$lt)
                                         <tr>
                                             <td>{{$key+1}}</td>
                                             <td>{{date("d-m-Y",strtotime($lt->created_at))}}</td>
                                             <td>{{$lt->order_no}}</td>
                                             <td>{{$lt->status}}</td>
                                             <td>{{$lt->txnid}}</td>
                                             <td>{{json_decode($lt->txndetail,true)}}</td>
                                             <td>{{json_decode($lt->txnresponse,true)}}</td>
                                             <td>{{$lt->txnmethod}}</td>
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