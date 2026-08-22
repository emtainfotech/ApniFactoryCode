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
						       @foreach($errors->all() as $error)
						             <div class="alert alert-danger">{{ $error ?? ''  }}</div>
                                @endforeach
                                @if($otpsend==2)
                                 <form method="get" enctype='multipart/form-data' >
                                    @csrf
                                   	<div class="mb-3">
                                   	    <input type="hidden" name="otpsend" value='2'>
								<label for="AccountHolderName" class="form-label">OTP Send To Your Registered Mobile Number</label>
								<input type="number" class="form-control" name="otp" placeholder="Enter OTP">
							  </div>
							   <div class="d-grid">
                                   <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                                </form>
                                @elseif($otpsend==1)
                            <form method="post" enctype='multipart/form-data'>
                                @csrf
                                <input type="hidden" name="actionof1" value="@if(!empty($first) && $first->accountholder!='') {{$first->id}} @else insert @endif">
                                <input type="hidden" name="actionof2" value="@if(!empty($second) && $second->accountholder!='') {{$second->id}} @else insert @endif">
					    <div class="row">
                            <div class="col-lg-12">
                                 <div class="form-check">
									<input class="form-check-input" type="radio" name="isprimary" value="1" id="flexRadioDefault" @if(!empty($first)) @if($first->isprimary=='Y') checked="" @else @endif  @endif>
									<label class="form-check-label" for="flexRadioDefault">Primary Account Details</label>
								</div>
                           <div class="border border-3 p-2 rounded">
							<div class="mb-3">
								<label for="AccountHolderName" class="form-label">Account Holder Name</label>
								<input type="text" class="form-control" name="accountholder1" placeholder="Account Holder Name " value=" @if(!empty($first)) {{$first->accountholder}} @endif">
							  </div>
							  <div class="mb-3">
								<label for="AccountNumber" class="form-label">Account Number</label>
								<input type="text`w2" class="form-control" name="accountno1" placeholder="Account Number " value=" @if(!empty($first)) {{$first->accountno}} @endif">
							  </div>
							  <div class="mb-3">
								<label for="AccountNumber" class="form-label">Confirm Account Number</label>
								<input type="password" class="form-control" name="accountno1" placeholder="Account Number " value="@if(!empty($first)) {{$first->accountno}} @endif">
							  </div>
							  	<div class="mb-3">
								<label for="AccountBankName" class="form-label">Bank Name</label>
								<input type="text" class="form-control" name="bankname1" placeholder="Bank Name " value=" @if(!empty($first)) {{$first->bankname}} @endif">
							  </div>
							  	<div class="mb-3">
								<label for="BankBranchName" class="form-label">Bank Branch Name</label>
								<input type="text" class="form-control" name="branch1" placeholder="Bank Branch Name " value=" @if(!empty($first)) {{$first->branch}} @endif">
							  </div>
							  	<div class="mb-3">
								<label for="BankifscCode" class="form-label">IFSC Code</label>
								<input type="text" class="form-control" name="ifsc1" placeholder="IFSC Code " value=" @if(!empty($first)) {{$first->ifsc}} @endif">
							  </div>
                            </div>
                            </div>
						  <div class="offset-md-4 col-md-4 offset-md-4 mt-4">
						      <div class="mb-3">
                                <div class="d-grid">
                                   <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </div>
						  </div>
					   </div><!--end row-->
					   
					     </form>
						  <?php /*
						  <div class="col-lg-4">
                           
                                <div class="form-check">
									<input class="form-check-input" type="radio" name="isprimary" value="2"  id="flexRadioDefault1" @if(!empty($second)) @if($second->isprimary=='Y') checked="" @else @endif  @endif>
									<label class="form-check-label" for="flexRadioDefault1">Primary Account Details</label>
								</div>
                           <div class="border border-3 p-2 rounded">
                      
							<div class="mb-3">
								<label for="AccountHolderName" class="form-label">Account Holder Name</label>
								<input type="text" class="form-control" name="accountholder2" placeholder="Account Holder Name " value=" @if(!empty($second)) {{$second->accountholder}} @endif">
							  </div>
							  <div class="mb-3">
								<label for="AccountNumber" class="form-label">Account Number</label>
								<input type="text" class="form-control" name="accountno2" placeholder="Account Number " value=" @if(!empty($second)) {{$second->accountno}} @endif">
							  </div>
							  	<div class="mb-3">
								<label for="AccountBankName" class="form-label">Bank Name</label>
								<input type="text" class="form-control" name="bankname2" placeholder="Bank Name " value=" @if(!empty($second)) {{$second->bankname}} @endif">
							  </div>
							  	<div class="mb-3">
								<label for="BankBranchName" class="form-label">Bank Branch Name</label>
								<input type="text" class="form-control" name="branch2" placeholder="Bank Branch Name " value=" @if(!empty($second)) {{$second->branch}} @endif">
							  </div>
							  
							  	<div class="mb-3">
								<label for="BankifscCode" class="form-label">IFSC Code</label>
								<input type="text" class="form-control" name="ifsc2" placeholder="IFSC Code " value=" @if(!empty($second)) {{$second->ifsc}} @endif">
							  </div>
                              
						 </div>
						   </div>
						   
						   <div class="col-lg-4">
						 <div class="form-check">
									<input class="form-check-input" type="radio"  name="isprimary" value="3"  id="flexRadioDefault2"  @if(!empty($thrid)) @if($thrid->isprimary=='Y') checked="" @else @endif  @endif>
									<label class="form-check-label" for="flexRadioDefault2">Primary Account Details</label>
								</div>
                           <div class="border border-3 p-2 rounded">
                      
							<div class="mb-3">
								<label for="AccountHolderName" class="form-label">Account Holder Name</label>
								<input type="text" class="form-control" name="accountholder[]" placeholder="Account Holder Name " value=" @if(!empty($thrid)) {{$thrid->accountholder}} @endif">
							  </div>
							  <div class="mb-3">
								<label for="AccountNumber" class="form-label">Account Number</label>
								<input type="number" class="form-control" name="accountno[]" placeholder="Account Number " value=" @if(!empty($thrid)) {{$thrid->accountno}} @endif">
							  </div>
							  	<div class="mb-3">
								<label for="AccountBankName" class="form-label">Bank Name</label>
								<input type="text" class="form-control" name="bankname[]" placeholder="Bank Name " value=" @if(!empty($thrid)) {{$thrid->bankname}} @endif">
							  </div>
							  	<div class="mb-3">
								<label for="BankBranchName" class="form-label">Bank Branch Name</label>
								<input type="text" class="form-control" name="branch[]" placeholder="Bank Branch Name " value=" @if(!empty($thrid)) {{$thrid->branch}} @endif">
							  </div>
							  
							  	<div class="mb-3">
								<label for="BankifscCode" class="form-label">IFSC Code</label>
								<input type="text" class="form-control" name="ifsc[]" placeholder="IFSC Code " value=" @if(!empty($thrid)) {{$thrid->ifsc}} @endif">
							  </div>
                            </div>
						  </div>
                              */ ?>
					     @else
					        <a href="?otpsend=3" class="btn btn-info">Request To Send OTP</a>
					        @endif
					</div>
				  </div>
			  </div>
                <!----------------list of Brands----------------------->
                  
					   </div><!--end row-->
					</div>
				  </div>
			  </div>
			</div>
		</div>
		<!--end page wrapper -->
 
@endsection