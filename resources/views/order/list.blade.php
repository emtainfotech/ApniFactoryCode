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
							<table id="example2" class="table table-striped table-bordered align-middle">
								<thead class="table-dark">
									<tr>
										<th>#</th>
										<th>Action</th>
										<th>Date</th>
										<th>Order No.</th>
										<th>Net Amount</th>
										<th>Grand Total</th>
										<th>Status</th>
										<th>Response Deadline (3-Day Limit)</th>
									</tr>
								</thead>
								<tbody>
								     @foreach($list as $key=>$lt)
                                         @php 
                                            $status = Helper::getstatusoforder($lt->orderno); 
                                            $stLower = strtolower($status ?? '');
                                            $isPending = in_array($stLower, ['pending', 'wait for confirmation', 'order received', '']);
                                            $createdAt = \Carbon\Carbon::parse($lt->created_at);
                                            $expiryDeadline = $createdAt->copy()->addHours(72);
                                            $isExpired = now()->greaterThan($expiryDeadline);
                                            $remainingSeconds = max(0, now()->diffInSeconds($expiryDeadline, false));
                                         @endphp
                                         <tr>
                                             <td>{{$key+1}}</td>
                                             <td>
                                                 <a class="btn btn-sm btn-primary me-1" href="{{ route('order.detail',$lt->orderno) }}" title="View & Process Order">
                                                     <i class="fa-regular fa-eye"></i> View
                                                 </a>
                                                 @if(!$isPending && $stLower != 'rejected' && $stLower != 'cancelled')
                                                 <a class="btn btn-sm btn-outline-secondary" href="{{ url('invoice/order',$lt->orderno) }}" target="_blank" title="Download Tax Invoice">
                                                     <i class="fa-solid fa-file-invoice"></i> Invoice
                                                 </a>
                                                 @endif
                                             </td>
                                             <td>{{ $createdAt->format('d-m-Y H:i') }}</td>
                                             <td><strong>#{{ $lt->orderno }}</strong></td>
                                             <td>₹{{ number_format($lt->netamount, 2) }}</td>
                                             <td><strong>₹{{ number_format($lt->grandtotal, 2) }}</strong></td>
                                             <td>
                                                 @if($isPending)
                                                     <span class="badge bg-warning text-dark px-2 py-1"><i class="fa-regular fa-clock me-1"></i> Pending Seller</span>
                                                 @elseif(in_array($stLower, ['accepted', 'completed', 'delivered', 'success']))
                                                     <span class="badge bg-success px-2 py-1"><i class="fa-solid fa-check-circle me-1"></i> {{ ucfirst($status) }}</span>
                                                 @elseif(in_array($stLower, ['processing', 'order processed']))
                                                     <span class="badge bg-info text-dark px-2 py-1"><i class="fa-solid fa-gear me-1"></i> Processing</span>
                                                 @elseif(in_array($stLower, ['in transit', 'out for delivery', 'shipped']))
                                                     <span class="badge bg-primary px-2 py-1"><i class="fa-solid fa-truck me-1"></i> In Transit</span>
                                                 @elseif(in_array($stLower, ['rejected', 'failed']))
                                                     <span class="badge bg-danger px-2 py-1"><i class="fa-solid fa-times-circle me-1"></i> Rejected</span>
                                                 @else
                                                     <span class="badge bg-secondary px-2 py-1">{{ ucfirst($status) }}</span>
                                                 @endif
                                             </td>
                                             <td>
                                                 @if($isPending)
                                                     @if($isExpired)
                                                         <span class="badge bg-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> Expired (Auto-Cancel)</span>
                                                     @else
                                                         <div class="countdown-timer" data-seconds="{{ $remainingSeconds }}">
                                                             <span class="badge bg-light text-danger border border-danger font-monospace px-2 py-1">
                                                                 ⏱️ <span class="timer-display">Calculating...</span>
                                                             </span>
                                                         </div>
                                                     @endif
                                                 @else
                                                     <span class="text-muted small"><i class="fa-solid fa-circle-check text-success me-1"></i> Responded</span>
                                                 @endif
                                             </td>
                                         </tr>
                                     @endforeach
								</tbody>
							</table>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateTimers() {
        document.querySelectorAll('.countdown-timer').forEach(function(el) {
            let seconds = parseInt(el.getAttribute('data-seconds'), 10);
            if (isNaN(seconds) || seconds <= 0) {
                el.innerHTML = '<span class="badge bg-danger">⏱️ Expired</span>';
                return;
            }
            seconds -= 1;
            el.setAttribute('data-seconds', seconds);

            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;

            const formatted = `${hours}h ${minutes}m ${secs}s`;
            const displaySpan = el.querySelector('.timer-display');
            if (displaySpan) {
                displaySpan.textContent = formatted;
            }
        });
    }
    updateTimers();
    setInterval(updateTimers, 1000);
});
</script>
						</div>
					</div>
				</div>
				  </div>
			  </div>

			</div>
		</div>
		<!--end page wrapper -->
 
@endsection