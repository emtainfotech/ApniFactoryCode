@extends('layout')
@section('title',$title)
@section('content')
<style>
    table#example2 p {
    text-wrap: wrap;
}
.help-css p {
    width: 100%;
    padding: 21px;
    border-radius: 15px;
    background: rgb(136 51 255 / 12%);
    font-size: 15px;
    font-weight: 500;
}
.help-css i {
    width: 40px;
    height: 40px;
    border-radius: 50%;
  
    line-height: 40px;
    text-align: center;
    color:#2143bd;
    font-size: 20px;
    margin-right: 15px;
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
					  <h5 class="card-title"> Your {{$title}}</h5>
					  <hr/>
                       <div class="form-body mt-4">
					    <div class="row">
                           
						   <div class="col-lg-12">
                         <form class="row g-3">
									<div class=" offset-lg-3 col-md-6 offset-lg-3 help-css">
									     @foreach($list as $key=>$lt)
										<div class="display-flex">
										    <p>{{$lt->attribute}}  <i class="fa-solid fa-exchange">‌</i> {{$lt->value}}</p>
										</div>
										 @endforeach
									
									</div>
									
								
								
									
								
								</form>
						   </div>
						  
					   </div><!--end row-->
					</div>
				  </div>
			  </div>
                <!----------------list of Brands----------------------->
                 
		</div>
		<!--end page wrapper -->

@endsection
