@extends('layout')
@section('title', 'Home Page')
@section('content')
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">

				<!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<div class="breadcrumb-title pe-3">Order</div>
					<div class="ps-3">
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0 p-0">
								<li class="breadcrumb-item"><a href="javascript:;">Home</a>
								</li>
								<li class="breadcrumb-item active" aria-current="page">Order list</li>
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
            <input type="text" class="form-control" id="order_no" name="orderno" value="{{ request('orderno') }}">
        </div>
        <div class="form-group col-md-2">
            <label for="status">Status:</label>
            <select class="form-control" id="status" name="status">
                <option value="">Select Status</option>
                <option value="Order Received" {{ request('status') == 'Order Received' ? 'selected' : '' }}>Order Received</option>
                                            <option value="Order Processed" {{ request('status') == 'Order Processed' ? 'selected' : '' }}>Order Processed</option>
                                            <option value="In Transit" {{ request('status') == 'In Transit' ? 'selected' : '' }}>In Transit</option>
                                            <option value="Out for Delivery" {{ request('status') == 'Out for Delivery' ? 'selected' : '' }}>Out for Delivery</option>
                                            <option value="Delivered" {{ request('status') == 'Delivered' ? 'selected' : '' }}>Delivered</option>
                                            <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="Returned" {{ request('status') == 'Returned' ? 'selected' : '' }}>Returned</option>
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
              <div class="card">
				  <div class="card-body p-4">
					  <h5 class="card-title"> Order List</h5>
					  <hr/>
                     <div class="card">
					<div class="card-body">
						<div class="table-responsive">
							<table id="example2" class="table table-striped table-bordered">
								<thead>
									<tr>
										<th>S.no</th>
										<th>Detail</th>
										<th>Date</th>
										<th>Order Id</th>
										<!--<th>Customer</th>-->
										<!--<th>Coupon Code</th>-->
										<th>NetAmount</th>
										<th>GrandTotal</th>
										<th>status</th>
									</tr>
								</thead>
								<tbody>
								     @foreach($list as $key=>$lt)
                                         <tr>
                                             <td>{{$key+1}}</td>
                                             <td> <a class="btn btn-sm btn-icon btn-primary" href="{{ route('order.detail',$lt->orderno) }}"><i class="fa-regular fa-eye"></i><span class="sr-only">View</span></a>
                                             <a href="{{ url('invoice/order',$lt->orderno) }}">invoice</a></td>
                                             <td>{{date("d-m-Y",strtotime($lt->created_at))}}</td>
                                             <td>{{$lt->orderno}}</td>
                                             <!--<td>@php $nme = Helper::getcustomerdetail($lt->customer_id); @endphp {{ $nme->name }}</td>-->
                                             <!--<td>{{$lt->sellercouponamount}} + {{$lt->admincouponamount}}</td>-->
                                             <td>{{$lt->netamount}}</td>
                                             <td>{{$lt->grandtotal}}</td>
                                             <td>
                                             @php 
                                                    $status = Helper::getstatusoforder($lt->orderno); 
                                             @endphp {{ $status }}</td>
                                         </tr>
                                     @endforeach
								
								</tfoot>
							</table>
						</div>
					</div>
				</div>
				  </div>
			  </div>

			</div>
		</div>
		<!--end page wrapper -->
 
@endsection