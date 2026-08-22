@extends('layout')
@section('title', 'Home Page')
@section('content')
<link src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css"/>
  <style>
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
    
 
</style>
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content"><div class="container invoice">
  <div class="invoice-header">
    <div class="row">
      <div class="col-md-8">
        <h1>Apni Factory – @if($creditnote->credit==0) Debit Note @else Credit Note @endif<small></small></h1>
        </hr>
        <h4 class="text-muted">Credit Note No: CN/{{date("Y",strtotime($order->created_at))}}/{{$order->orderno}}/{{$creditnote->id}}</h4>
        <h5> Date: {{date("d/m/Y",strtotime($order->created_at))}}</h5>
      </div>
    </div>
  </div>
  <div class="invoice-body">
    <div class="row">
      <div class="col-md-5">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Issued To :</h3>
          </div>
          <div class="panel-body">
            <dl class="dl-horizontal">
                <ul>
                <li><strong>Company :  {{$seller->name}}</strong></li>
                  <li>GST :  {{$seller->gst}}</li>
                  <li>Phone :  {{$seller->mobile}}</li>
                  <li>Email :  {{$seller->email}}</li>
                  <li>Address :  {{$seller->city}}, {{$seller->state}}</li>
                  <li>Pincode :  {{$seller->pincode}}</li>
                  @if($creditnote->credit==0) 
                  <li>Reason : {{$track->text}}</li>
                  <li>Status : {{$laststatus->status}}({{$laststatus->msg}})</li>
                  @else 
                  <li>L.R. No :  {{$track->lrno}}</li>
                  <li>E-way BillNo :  {{$track->invoiceno}}</li>
                  @endif
                </ul>
          </div>
        </div>
      </div>
      <div class="col-md-2"></div>
      <div class="col-md-5">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Issued By</h3>
          </div>
          <div class="panel-body">
            <dl class="dl-horizontal">
                <ul>
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
    <div class="panel panel-default table-responsive">
      <div class="panel-heading">
        <h3 class="panel-title">Services / Products</h3>
      </div>
      <table class="table">
        <thead>
          <tr>
            <th>Item / Details</th>
            <th class="text-center colfix">Qty(box + pcs)</th>
            <th class="text-center colfix">Rate</th>
            <th class="text-center colfix">CMP Dis.</th>
            <th class="text-center colfix">Value</br><small>Price After Discount</small></th>
            <th class="text-center colfix">Finalboxprice</th>
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
              <br><b>{{$od->hsn}}</b><br>{{$attri->color}}
              </td> 
            <td class="text-right">{{$attri->qty}} ( {{$attri->boxpacking}} )
            </td>
            <td class="text-right">{{$attri->prprice}}</td>
            <td class="text-right">{{$attri->coupon}}</td>
            <td class="text-right">{{$attri->amntaftrcoupn}}</td>
            <td class="text-right">{{$attri->unitprice}}</td>
            <td class="text-right">{{$attri->totalprice}}</td>
            
             </tr>
             @endforeach
            @endforeach
        </tbody>
      </table>
    </div>
    <div class="panel panel-default">
      <table class="table">
          <tr>
              <td>Net Taxable Amount</td>
              <td>{{$order->grandtotal}}</td>
          </tr>
          <tr>
              <td>Refund To Buyer</td>
              <td>{{$creditnote->refundtobuyer}}</td>
          </tr>
          <tr>
              <td>Apnifactory Commission @ {{$seller->comission}}%</td>
              <td>{{$credit = ($order->grandtotal*$seller->comission)/100}}</td>
          </tr>
          <tr>
              <td>Total Deduction</td>
              <td>{{$credit}}</td>
          </tr>
          <tr>
              <td>Net Payout to Seller</td>
              <td>{{$creditnote->credit}}</td>
          </tr>
        <tbody>
      </table>
    </div>
  </div>
  <div class="invoice-footer">
    
  </div>
</div>
		</div>
		</div>
		<!--end page wrapper -->
 
@endsection