@extends('layout')
@section('title', 'Home Page')
@section('content')
<link src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css"/>

		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content"><div class="container invoice">
  <div class="invoice-header">
    <div class="row">
      <div class="col-md-8">
        <h1>Order detail <small></small></h1>
        <h4 class="text-muted">NO: {{$order->orderno}} | Date: {{ date("d/m/Y")}}</h4>
      </div>
      <div class="col-md-4">
        <div class="media">
          <div class="media-left">
            <img class="media-object logo" src="{{ asset('public/img/Apni-Factory-3.png')}}" />
          </div>
          <ul class="media-body list-unstyled">
              @foreach($profile as $key=>$lt)
                 @if($lt->attribute=='Name')
				    <li><strong>{{$lt->attribute}} :  {{$lt->value}}</strong></li>
                @else
				    <li>{{$lt->attribute}} :  {{$lt->value}}</li>
				@endif
			   @endforeach
          </ul>
        </div>
      </div>
    </div>
  </div>
  <div class="invoice-body">
@php $totl=0;
        $address = json_decode($order->address);  
@endphp
    <div class="row">
      <div class="col-md-5">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Seller Details</h3>
          </div>
          <div class="panel-body">
            <dl class="dl-horizontal">
              <dt>Company </dt>
              <dd><strong>{{$seller->name}}</strong></dd>
              <dt>Industry</dt>
              <dd>{{$seller->gst}}</dd>
              <!--<dt>Phone</dt>-->
              <!--<dd>{{$seller->mobile}}</dd>-->
              <dt>Email</dt>
              <dd>{{$seller->email}}</dd>
              <dt>Pincode </dt>
              <dd class="mono">{{$seller->pincode}}</dd>
              <dt>Address</dt>
              <dd style="width: 46%;">{{$seller->city}}, {{$seller->state}}</dd>
          </div>
        </div>
      </div>
      <div class="col-md-2"></div>
      <div class="col-md-5">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Buyer Details</h3>
          </div>
          <div class="panel-body">
            <dl class="dl-horizontal">
              <dt>Name </dt>
              <dd><strong>{{$buyer->name}}</strong></dd>
              <dt>Pincode </dt>
              <dd>{{$address->pincode}}</dd>
              <dt>Address</dt>
              <dd style="width: 46%;">{{$address->name}} ,{{$address->landmark1}}, {{$address->landmark2}}</dd>
              <dt>Location</dt><dd style="width: 46%;">{{$address->city}}, {{$address->state}}, {{$address->country}}</dd>
              <!--<dt>Contact No</dt>-->
              <!--<dd>{{$address->phoneno}}</dd>-->
          </div>
        </div>
      </div>
    </div>
    <div class="panel panel-default table-responsive">
      <div class="panel-heading">
        <h3 class="panel-title">Services / Products</h3>
      </div>
      <table class="table table-bordered table-condensed">
        <thead>
          <tr>
            <th>Item / Details</th>
            <th class="text-center colfix">HSN Code</th>
            <th class="text-center colfix">Color</th>
            <th class="text-center colfix">Qty(box + pcs)</th>
            <th class="text-center colfix">Rate</th>
            <th class="text-center colfix">CMP Dis.</th>
            <th class="text-center colfix">Value</br><small>Price After Discount</small></th>
            <th class="text-center colfix">Finalboxprice</th>
            <th class="text-center colfix">GST</th>
            <th class="text-center colfix">Sub Total</th>
          </tr>
        </thead>
        <tbody>    
            @foreach($orderdetail as $od)  
            @php 
                $att = json_decode($od->attribute); 
            @endphp
            <tr>
              </tr>
              @foreach($att as $attri)
              <tr>
              <td class="text-center">
               {{$od->productname}}
              <br>
              <small class="text-muted">{{$od->brdcmpcat}} </small>
              </td> 
              <td>   <b>{{$od->hsn}}</b> </td>
                  <td>{{$attri->color}}</td>
            <td class="text-right">{{$attri->qty}} ( {{$attri->boxpacking}} )
            </td>
            <td class="text-right">{{$attri->prprice}}</td>
            <td class="text-right">{{$attri->coupon}}</td>
            <td class="text-right">{{$attri->amntaftrcoupn}}</td>
            <td class="text-right">{{$attri->unitprice}}</td>
            <td class="text-right">{{$attri->tax}}%</td>
            <td class="text-right">{{$attri->totalprice}}</td>
            @php $totl = $totl+$attri->totalprice; @endphp
             </tr>
             @endforeach
            @endforeach
        </tbody>
      </table>
    </div>
    <div class="panel panel-default">
      <table class="table lesstrheight">
          <tr>
              <td>Total Amount</td>
              <td>{{$totl}}</td>
          </tr>
          <tr>
              <td>Total Savings (Company Discount)</td>
              <td>{{$order->sellercouponamount}}</td>
          </tr>
          <tr>
              <td>ApniFactory Discount</td>
              <td>{{$order->admincouponamount}}</td>
          </tr>
          <tr>
              <td>Net Amount After Discount</td>
              <td>{{$order->netamount}}</td>
          </tr>
          @php $taxdetail = json_decode($order->taxdetail,true); @endphp
          @foreach($taxdetail as $taxdt)
          <tr>
              <td>Add: {{$taxdt['name']}}</td>
              <td> {{$taxdt['value']}}</td>
          </tr>
          @endforeach
          <tr>
              <td>Gross Total Payable</td>
              <td>{{$order->grandtotal}}</td>
          </tr>
          <tr>
              <td>Credits -</td>
              <td>{{$seller->comission}}%</td>
          </tr>
          <tr>
              <td>Credit Amounts - </td>
              <td>{{$credit = ($order->grandtotal*(100-$seller->comission))/100}}</td>
          </tr>
        <tbody>
      </table>
    </div>
 <div class="row mb-3">
						   <div class="col-lg-12">
						       @foreach($errors->all() as $error)
						             <div class="alert alert-danger">{{ $error ?? ''  }}</div>
                                @endforeach
                                @php
                            $transname = '';
                                        $text = '';
                                        $transcontact = '';
                                        $lrno = '';
                                        $ewaybillno = $billty = $invoice = '';
                                    if(!empty($track)){
                                        $transname = $track->transname;
                                        $text = $track->text;
                                        $transcontact = $track->transcontact;
                                        $lrno = $track->lrno;
                                        $ewaybillno = $track->invoiceno;
                                        $billty = $track->billty;
                                        $invoice = $track->invoice;
                                    }
                                @endphp
             <div class="border border-3 p-4 rounded panel-default">
                            	<div class="mb-3 panel-heading"><h3 class="panel-title">Tracking Details</h3></div>
                            	
                                @php $st=''; @endphp
                                 @php if(!empty($track) && $track->status==1){ $st='Accept'; }else{ $st='Reject';} @endphp
                                 
                        <form method="post" action="{{route('order.update',$order->id)}}">
                            <input type="hidden" value="actionorderstatus" name="action" >
                                @csrf
                                @if($st=='Accept') 
                            <input type="submit" name="orderaction" value="Order Accepted" class="btn btn-success"/>
                                 @else
                            <input type="submit" name="orderaction" value="Order Accepted" class="btn btn-success"/>
                            <input type="submit" name="orderaction" value="Order Rejected" class="btn btn-danger"/>
                                @endif
                        </form>   
                        
                                
    <form id="orderForm" method="post" enctype='multipart/form-data' action="{{route('order.update',$order->id)}}">
                                <input type="hidden" value="{{$credit}}" name="credit" >
                                <input type="hidden" value="trackstatus" name="action" >
                                <input type="hidden" value="{{$track->status ?? ''}}" name="status" >
                                @csrf
        <!-- Accept Order Fields -->
        <div @if($st=='Accept') @else id="acceptFields" class="hidden" @endif>
            	<div class="row">  
            	            <div class="mb-3 col-md-6">
								<label for="inputProductTitle" class="form-label"> Transport Name</label>
								<input type="text" class="accept-input form-control" name="transname" value="{{$transname}}" placeholder="Enter Transport Name " >
							  </div>
							<div class="mb-3 col-md-6">
								<label for="inputProductTitle" class="form-label">Transport Contact No.</label>
								<input type="number" class="accept-input form-control" name="transcontact" value="{{$transcontact}}" placeholder="Enter Transport Contact No" >
				            </div>
				</div>
				<div class="row">  
            	           	<div class="mb-3 col-md-6">
								<label for="inputProductTitle" class="form-label">L.R No.</label>
								<input type="text" class="accept-input form-control" name="lrno"  value="{{$lrno}}" placeholder="Enter L.R No. " >
							  </div>
							<div class="mb-3 col-md-6">
								<label for="inputProductTitle" class="form-label">Invoice No.</label>
								<input type="text" class="accept-input form-control" name="invoiceno"  value="{{$ewaybillno}}" placeholder="Enter InvoiceNo ">
							  </div>
				</div>
            	<div class="row">  
            	<div class="mb-3 col-md-6">
                <label>Billty File:</label>
                <input type="file" name="builty_file" accept=".pdf,.jpg,.png" class="accept-input form-control">
                 @if($st=='Accept' && $track->billty!='') <a href="{{asset('storage/app/public/'.$billty)}}" target="_blank">Download Billty </a> @endif
                </div>
                <div class="mb-3 col-md-6">
                    <label>Invoice File:</label>
                <input type="file" name="invoice_file" accept=".pdf,.jpg,.png" class="accept-input form-control">
                 @if($st=='Accept' && $track->invoice!='') <a href="{{asset('storage/app/public/'.$invoice)}}"  target="_blank">Download Invoice </a> @endif
                </div>
                </div>
        </div>
        <!-- Reject Order Fields -->
        <div id="rejectFields" class="hidden" >
            <div class="row">
                <div class="mb-3 col-md-12">
								<label for="inputProductTitle" class="form-label">Reason for Rejection:</label>
								<input type="text" class="form-control" name="rnote" value="{{$text}}" placeholder="Enter {{$title}} " id="rejection_reason">
							 </div>
            </div>
        </div>
        
                <div class="row">
							<div class="mb-3 col-md-12">
								<label for="inputProductTitle" class="form-label">Note</label>
								<input type="text" class="form-control" name="anote" value="{{$text}}" placeholder="Enter Any Message To Buyer ">
							  </div>
                </div>
         @if(empty($track) or $track->invoice=='')
                              <div class="mb-3">
                                <div class="d-grid">
                                   <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
          @endif
    </form>
    </div>
    </div>
						   </div>
					   </div><!--end row-->
					   <div class="row">
						   <div class="col-lg-12">
						   <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Order Status</h3>
          </div>
          <div class="panel-body">
           	<div class="table-responsive">
							<table id="example" class="table table-striped table-bordered">
								<thead>
									<tr>
										<th>S.no</th>
										<th>Status</th>
										<th>Message</th>
										<th>Date</th>
									</tr>
								</thead>
								<tbody>
								     @foreach($status as $key=>$lt)
                                         <tr>
                                             <td>{{$key+1}}</td>
                                             <td>{{$lt->status}}</td>
                                             <td>{{$lt->msg}}</td>
                                             <td>{{date("d-m-Y",strtotime($lt->created_at))}}</td>
                                         </tr>
                                     @endforeach
								<tr>
								    <td></td>
								    
                            <form method="post" enctype='multipart/form-data' action="{{route('order.update',$order->id)}}">
                                <input type="hidden" value="{{$order->id}}" name="orderid" >
                                <input type="hidden" value="{{$order->orderno}}" name="orderno" >
                                <input type="hidden" value="{{$order->user_id}}" name="userid" >
                                <input type="hidden" value="{{$buyer->mobile}}" name="buyermobile" >
                                <input type="hidden" value="stausupdate" name="action" >
                                @csrf
								    <td>
								        <select class="form-select" id="inputProductType" name="status">
                                            <option value=""></option>
                                            <option value="Order Received">Order Received</option>
                                            <option value="Order Processed">Order Processed</option>
                                            <option value="In Transit">In Transit</option>
                                            <option value="Out for Delivery">Out for Delivery</option>
                                            <option value="Delivered">Delivered</option>
                                            <option value="Pending">Pending</option>
                                            <option value="Returned">Returned</option>
                                          </select>
                                    </td>
								    <td>	<input type="text" class="form-control" name="msg"  placeholder="Enter Msg "></td>
								    <td>  <button type="submit" class="btn btn-info">Add Status</button></td>
								</form>
								</tr>
								</tbody>
							</table>
						</div>
            
          </div>
        </div>
						  </div>
					   </div>
  </div>
  <div class="invoice-footer">
  </div>
</div>
		</div>
		</div>
		<!--end page wrapper -->
	 <script> 
// 	 function toggleFieldsre() {
//           rejectDiv.style.display = 'block';
//                 // Set only the rejection reason to be required
//                 rejectionTextarea.required = true;
// 	 }
        function toggleFields() {
            const status = document.getElementById('orderStatus').value;
            const acceptDiv = document.getElementById('acceptFields');
            const rejectDiv = document.getElementById('rejectFields');
            
            // Get all individual input elements
            const acceptInputs = document.querySelectorAll('.accept-input');
            const rejectionTextarea = document.getElementById('rejection_reason');

            // 1. Reset everything first
            acceptDiv.style.display = 'none';
            rejectDiv.style.display = 'none';
            
            acceptInputs.forEach(input => input.required = false);
            rejectionTextarea.required = false;

            // 2. Apply logic based on selection
            if (status === 'accept') {
                acceptDiv.style.display = 'block';
                // Set all accept fields to be required
                // acceptInputs.forEach(input => input.required = true);
            } else if (status === 'reject') {
                rejectDiv.style.display = 'block';
                // Set only the rejection reason to be required
                rejectionTextarea.required = true;
            }
        } 
    </script>
   <style>
        .hidden { display: none; margin-top: 10px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; }
  body{
  background:#EEE;
  /* font-size:0.9em !important; */
}
.invoice{
  width:970px !important;
  margin:50px auto;
  .invoice-header{
    padding:25px 25px 15px;
    h1{
      margin:0
    }
    .media{
      .media-body{
        font-size:.9em;
        margin:0;
      }
    }
  }
  .invoice-body{
    border-radius:10px;
    padding:25px;
    background:#FFF;
  }
  .invoice-footer{
    padding:15px;
    font-size:0.9em;
    text-align:center;
    color:#999;
  }
}
.logo{
  max-height:70px;
  border-radius:10px;
}
/*.dl-horizontal{*/
/*  margin:0;*/
  dt{
          display: inline-block !important;
        float: left;
    width: 80px;
    overflow: hidden;
    clear: left;
    text-align: right;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  dd{
    margin-left:90px;    display: inline-block !important;
  }
/*}*/
.rowamount{
  padding-top:15px !important;
}
.rowtotal{
  font-size:1.3em;
}
.colfix{
  width:12%;
}
.mono{
  font-family:monospace;
}

.panel {
    margin-bottom: 20px;
    background-color: #fff;
    border: 1px solid transparent;
    border-radius: 4px;
    -webkit-box-shadow: 0 1px 1px rgba(0,0,0,.05);
    box-shadow: 0 1px 1px rgba(0,0,0,.05)
}

.panel-body {
    padding: 15px
}

.panel-heading {
    padding: 10px 15px;
    border-bottom: 1px solid transparent;
    border-top-left-radius: 3px;
    border-top-right-radius: 3px
}

.panel-heading>.dropdown .dropdown-toggle {
    color: inherit
}

.panel-title {
    margin-top: 0;
    margin-bottom: 0;
    font-size: 16px;
    color: inherit
}

.panel-title>.small,.panel-title>.small>a,.panel-title>a,.panel-title>small,.panel-title>small>a {
    color: inherit
}

.panel-footer {
    padding: 10px 15px;
    background-color: #f5f5f5;
    border-top: 1px solid #ddd;
    border-bottom-right-radius: 3px;
    border-bottom-left-radius: 3px
}

.panel>.list-group,.panel>.panel-collapse>.list-group {
    margin-bottom: 0
}

.panel>.list-group .list-group-item,.panel>.panel-collapse>.list-group .list-group-item {
    border-width: 1px 0;
    border-radius: 0
}

.panel>.list-group:first-child .list-group-item:first-child,.panel>.panel-collapse>.list-group:first-child .list-group-item:first-child {
    border-top: 0;
    border-top-left-radius: 3px;
    border-top-right-radius: 3px
}

.panel>.list-group:last-child .list-group-item:last-child,.panel>.panel-collapse>.list-group:last-child .list-group-item:last-child {
    border-bottom: 0;
    border-bottom-right-radius: 3px;
    border-bottom-left-radius: 3px
}

.panel>.panel-heading+.panel-collapse>.list-group .list-group-item:first-child {
    border-top-left-radius: 0;
    border-top-right-radius: 0
}

.panel-heading+.list-group .list-group-item:first-child {
    border-top-width: 0
}

.list-group+.panel-footer {
    border-top-width: 0
}

.panel>.panel-collapse>.table,.panel>.table,.panel>.table-responsive>.table {
    margin-bottom: 0
}

.panel>.panel-collapse>.table caption,.panel>.table caption,.panel>.table-responsive>.table caption {
    padding-right: 15px;
    padding-left: 15px
}

.panel>.table-responsive:first-child>.table:first-child,.panel>.table:first-child {
    border-top-left-radius: 3px;
    border-top-right-radius: 3px
}

.panel>.table-responsive {
    margin-bottom: 0;
    border: 0
}

.panel-group {
    margin-bottom: 20px
}

.panel-group .panel {
    margin-bottom: 0;
    border-radius: 4px
}

.panel-group .panel+.panel {
    margin-top: 5px
}

.panel-group .panel-heading {
    border-bottom: 0
}

.panel-group .panel-heading+.panel-collapse>.list-group,.panel-group .panel-heading+.panel-collapse>.panel-body {
    border-top: 1px solid #ddd
}

.panel-group .panel-footer {
    border-top: 0
}

.panel-group .panel-footer+.panel-collapse .panel-body {
    border-bottom: 1px solid #ddd
}

.panel-default {
    border-color: #ddd
}

.panel-default>.panel-heading {
    color: #333;
    background-color: #f5f5f5;
    border-color: #ddd
}

.panel-default>.panel-heading+.panel-collapse>.panel-body {
    border-top-color: #ddd
}

.panel-default>.panel-heading .badge {
    color: #f5f5f5;
    background-color: #333
}

.panel-default>.panel-footer+.panel-collapse>.panel-body {
    border-bottom-color: #ddd
}

.panel-primary {
    border-color: #337ab7
}

.panel-primary>.panel-heading {
    color: #fff;
    background-color: #337ab7;
    border-color: #337ab7
}

.panel-primary>.panel-heading+.panel-collapse>.panel-body {
    border-top-color: #337ab7
}

.panel-primary>.panel-heading .badge {
    color: #337ab7;
    background-color: #fff
}

.panel-primary>.panel-footer+.panel-collapse>.panel-body {
    border-bottom-color: #337ab7
}

.panel-success {
    border-color: #d6e9c6
}

.panel-success>.panel-heading {
    color: #3c763d;
    background-color: #dff0d8;
    border-color: #d6e9c6
}

.panel-success>.panel-heading+.panel-collapse>.panel-body {
    border-top-color: #d6e9c6
}

.panel-success>.panel-heading .badge {
    color: #dff0d8;
    background-color: #3c763d
}

.panel-success>.panel-footer+.panel-collapse>.panel-body {
    border-bottom-color: #d6e9c6
}

.panel-info {
    border-color: #bce8f1
}

.panel-info>.panel-heading {
    color: #31708f;
    background-color: #d9edf7;
    border-color: #bce8f1
}

.panel-info>.panel-heading+.panel-collapse>.panel-body {
    border-top-color: #bce8f1
}

.panel-info>.panel-heading .badge {
    color: #d9edf7;
    background-color: #31708f
}

.panel-info>.panel-footer+.panel-collapse>.panel-body {
    border-bottom-color: #bce8f1
}

.panel-warning {
    border-color: #faebcc
}

.panel-warning>.panel-heading {
    color: #8a6d3b;
    background-color: #fcf8e3;
    border-color: #faebcc
}

.panel-warning>.panel-heading+.panel-collapse>.panel-body {
    border-top-color: #faebcc
}

.panel-warning>.panel-heading .badge {
    color: #fcf8e3;
    background-color: #8a6d3b
}

.panel-warning>.panel-footer+.panel-collapse>.panel-body {
    border-bottom-color: #faebcc
}

.panel-danger {
    border-color: #ebccd1
}

.panel-danger>.panel-heading {
    color: #a94442;
    background-color: #f2dede;
    border-color: #ebccd1
}

.panel-danger>.panel-heading+.panel-collapse>.panel-body {
    border-top-color: #ebccd1
}

.panel-danger>.panel-heading .badge {
    color: #f2dede;
    background-color: #a94442
}

.panel-danger>.panel-footer+.panel-collapse>.panel-body {
    border-bottom-color: #ebccd1
}
.invoice table td, .invoice table th{
    padding: 15px;
    background: #fff;
    border: 1px solid #eee !important;}
.lesstrheight tr{line-height: 0.1;}  
 
</style>
@endsection