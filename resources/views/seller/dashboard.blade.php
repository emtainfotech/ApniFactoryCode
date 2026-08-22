@extends('layout')
@section('title', 'Home Page')
@section('content')
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">

				<!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<div class="breadcrumb-title pe-3">Product</div>
					<div class="ps-3">
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0 p-0">
								<li class="breadcrumb-item"><a href="javascript:;">Home</a>
								</li>
								<li class="breadcrumb-item active" aria-current="page">Dashboard</li>
							</ol>
						</nav>
					</div>
					<div class="ms-auto">
						 @foreach($errors->all() as $error)
						             <div class="alert alert-danger">{{ $error ?? ''  }}</div>
                                @endforeach
                          
					</div>
				</div>
				<!--end breadcrumb-->

                   
{{-- Custom CSS for this page (Add in your CSS file or in <style> tags if needed) --}}
<style>
    .dashboard-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px 0;
        border-bottom-left-radius: 50% 20%;
        border-bottom-right-radius: 50% 20%;
    }
    .thought-card {
        background: #fff;
        border-left: 5px solid #667eea;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .stat-card {
        transition: transform 0.3s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .welcome-icon {
        font-size: 4rem;
        opacity: 0.2;
        position: absolute;
        right: 20px;
        top: 20px;
    }
</style>

<div class="dashboard-wrapper">
    <!-- Header Welcome Section -->
    <div class="dashboard-header position-relative overflow-hidden">
        <div class="container position-relative">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-2" style="
    color: #dc3545;
    font-size: calc(1.475rem + 1.7vw);
">
                        Welcome, {{ Auth::user()->name }}! 🎉
                    </h1>
                    <p class="lead mb-0">
                        Successfully registered to <strong>Apni Factory</strong>. We're glad to have you on board.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end d-none d-lg-block">
                    <i class="fas fa-school welcome-icon text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        
        <!-- Thought of the Day -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card thought-card rounded-3 p-4">
                    <div class="d-flex align-items-start">
                        <div class="bg-light rounded-circle p-3 me-3">
                            <i class="fas fa-quote-left text-primary fs-4"></i>
                        </div>
                        <div>
                            <h4 class="text-primary fw-bold mb-2">Thought of the Day</h4>
                            <p class="fs-5 fst-italic text-dark">
                                "Success is not the key to happiness. Happiness is the key to success. 
                                If you love what you are doing, you will be successful."
                            </p>
                            <p class="text-muted mb-0 text-end">- Albert Schweitzer</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats / Actions -->
        <div class="row g-4 mb-5">
            <!-- Profile Completion -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body text-center py-4">
                        <div class=" rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-id-card text-primary fs-4"></i>
                        </div>
                        <h5 class="card-title">Complete Profile</h5>
                        <p class="card-text text-muted small">Add more details to your company profile.</p>
                        <a href="{{url('seller/profile')}}" class="btn btn-outline-primary btn-sm">Update Now</a>
                    </div>
                </div>
            </div>
 <!-- View Orders -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body text-center py-4">
                        <div class=" rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-user text-warning fs-4"></i>
                        </div>
                        <h5 class="card-title">Update Banner</h5>
                        <p class="card-text text-muted small">Update Your Copmpany Banner.</p>
                        <a href="{{url('seller/profile')}}" class="btn btn-outline-warning btn-sm">Change Now</a>
                    </div>
                </div>
            </div>
            <!-- Add Products -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body text-center py-4">
                        <div class=" rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-box text-success fs-4"></i>
                        </div>
                        <h5 class="card-title">Add Products</h5>
                        <p class="card-text text-muted small">Start listing your products for sale.</p>
                        <a href="{{url('seller/product/add')}}" class="btn btn-outline-success btn-sm">Add Product</a>
                    </div>
                </div>
            </div>

            <!-- View Orders -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm stat-card h-100">
                    <div class="card-body text-center py-4">
                        <div class=" rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-shopping-cart text-warning fs-4"></i>
                        </div>
                        <h5 class="card-title">Orders</h5>
                        <p class="card-text text-muted small">Check your recent orders and requests.</p>
                        <a href="{{url('seller/product/order')}}" class="btn btn-outline-warning btn-sm">View Orders</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Getting Started Guide -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-rocket me-2 text-primary"></i>Getting Started with Apni Factory</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center mb-3">
                                <div class="display-6 fw-bold text-primary">01</div>
                                <h6>Verify Email</h6>
                                <p class="small text-muted mb-0">Check your inbox for verification link.</p>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <div class="display-6 fw-bold text-primary">02</div>
                                <h6>Add Products</h6>
                                <p class="small text-muted mb-0">Upload your inventory items.</p>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <div class="display-6 fw-bold text-primary">03</div>
                                <h6>Set Prices</h6>
                                <p class="small text-muted mb-0">Configure pricing for your buyers.</p>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <div class="display-6 fw-bold text-primary">04</div>
                                <h6>Start Selling</h6>
                                <p class="small text-muted mb-0">Go live and get orders.</p>
                            </div>
                        </div>
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