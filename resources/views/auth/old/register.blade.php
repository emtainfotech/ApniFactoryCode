@extends('layouts.app')

@section('content')
<div class="container">
	<link href="{{ $_ENV['APP_URL']}}/public/css/imageuploadify.min.css" rel="stylesheet" />
	<link href="{{ $_ENV['APP_URL']}}/public/css/simplebar.css" rel="stylesheet" />
	<link href="{{ $_ENV['APP_URL']}}/public/css/perfect-scrollbar.css" rel="stylesheet" />
	<link href="{{ $_ENV['APP_URL']}}/public/css/metisMenu.min.css" rel="stylesheet" />
    <link href="{{ $_ENV['APP_URL']}}/public/css/dataTables.bootstrap5.min.css" rel="stylesheet">
	<!-- loader-->
	<!-- Bootstrap CSS -->
	<link href="{{ $_ENV['APP_URL']}}/public/css/bootstrap.min.css" rel="stylesheet">

	<link href="{{ $_ENV['APP_URL']}}/public/css/bootstrap-extended.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
	<link href="{{ $_ENV['APP_URL']}}/public/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
     <title>Registration - Apni Factory</title>

 	<div class="page-wrapper ms-0">
 	    <div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>
                    	<div class="text-center ">
									    <img src="{{ $_ENV['APP_URL']}}/public/img/Apni-Factory-3.png" class="logo-login" alt="logo icon">
										<!--<h3 class="mt-4">Sign in</h3>-->
										
										</p>
									</div>
                <div class="card-body">
							<form method="POST" action="{{ route('register') }}">
                            @csrf
                            <!-- Main Category Dropdown -->
                            <div class="col-12 mb-3">
                                <label for="inputMainCategory" class="form-label">Main Category</label>
                                <select name="category_id" id="inputMainCategory" class="form-select @error('category_id') is-invalid @enderror">
                                    <option value="" disabled selected>Select Main Category</option>
                                    @foreach($mainCategories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="row">
                            <!-- GST Field -->
                            <div class="col-6 mb-3">
                                <label for="innputgst" class="form-label">GST Number</label>
                                <input type="text" class="form-control @error('gstno') is-invalid @enderror" id="innputgst" name="gstno" placeholder="15 Digit GST" value="{{ old('gstno') }}">
                                @error('gstno')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 mb-3">
                                <button class="btn btn-primary">Verify</button>
                            </div>
                            </div>
                            <!-- Name Field -->
                            <div class="row">
                            <div class="col-sm-6 mb-3">
                                <label for="inputFirstName" class="form-label"> Name</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="inputFirstName" placeholder="Jhon" value="{{ old('name') }}">
                                @error('name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Company Name Field -->
                            <div class="col-sm-6 mb-3">
                                <label for="inputLastName" class="form-label">Company Name</label>
                                <input type="text" name="cmpname" class="form-control @error('cmpname') is-invalid @enderror" id="inputLastName" placeholder="Deo" value="{{ old('cmpname') }}">
                                @error('cmpname')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                    </div>
                            <!-- Email Field -->
                            <div class="col-12 mb-3">
                                <label for="inputEmailAddress" class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="inputEmailAddress" placeholder="example@user.com" value="{{ old('email') }}">
                                @error('email')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Mobile Field -->
                            <div class="col-12 mb-3">
                                <label for="innputmobile" class="form-label">Mobile Number</label>
                                <input type="tel" name="mobile" class="form-control @error('mobile') is-invalid @enderror" id="innputmobile" placeholder="+91 98765 41230" value="{{ old('mobile') }}">
                                @error('mobile')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- CRN No Field -->
                            <div class="col-12 mb-3">
                                <label for="innputcrnno" class="form-label">CRN No</label>
                                <input type="text" name="crnno" class="form-control @error('crnno') is-invalid @enderror" id="innputcrnno" placeholder="1236547899" value="{{ old('crnno') }}">
                                @error('crnno')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            
                            <!-- Password Field -->
                            <div class="col-12 mb-3">
                                <label for="inputChoosePassword" class="form-label">Password</label>
                                <div class="input-group" id="show_hide_password">
                                    <input type="password" class="form-control border-end-0 @error('password') is-invalid @enderror" name="password" id="inputChoosePassword" value="" placeholder="Enter Password"> 
                                    <a href="javascript:void(0)" class="input-group-text bg-transparent"><i class="bx bx-hide"></i></a>
                                </div>
                                @error('password')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Confirm Password Field -->
                            <div class="col-12 mb-3">
                                <label for="password-confirm" class="form-label">{{ __('Confirm Password') }}</label>
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm Password">
                            </div>

                            <!-- City Field -->
                            <div class="col-12 mb-3">
                                <label for="inputSelectCountry" class="form-label">City</label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror" id="innputgst" name="city" placeholder="City" value="{{ old('city') }}">
                                @error('city')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Terms Checkbox -->
                            <div class="col-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" id="flexSwitchCheckChecked" name="terms" value="1">
                                    <label class="form-check-label" for="flexSwitchCheckChecked">I read and agree to Terms &amp; Conditions</label>
                                </div>
                                @error('terms')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Register') }}
                                    </button>
                                </div>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- Bootstrap JS -->
	<script src="{{ $_ENV['APP_URL']}}/public/js/bootstrap.bundle.min.js"></script>
	<!--plugins-->
	<script src="{{ $_ENV['APP_URL']}}/public/js/jquery.min.js"></script>
	<script src="{{ $_ENV['APP_URL']}}/public/js/simplebar.min.js"></script>
	<script src="{{ $_ENV['APP_URL']}}/public/js/metisMenu.min.js"></script>
	<script src="{{ $_ENV['APP_URL']}}/public/js/perfect-scrollbar.js"></script>
	<script src="{{ $_ENV['APP_URL']}}/public/js/imageuploadify.min.js"></script>
    <script src='https://cdn.tiny.cloud/1/vdqx2klew412up5bcbpwivg1th6nrh3murc6maz8bukgos4v/tinymce/5/tinymce.min.js' ></script>
    <script src="{{ $_ENV['APP_URL']}}/public/js/dataTables.bootstrap5.min.js"></script>
    <script src="{{ $_ENV['APP_URL']}}/public/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
			$('#example').DataTable();
		  } );

    </script>
	<!--app JS-->
	<script src="{{ $_ENV['APP_URL']}}/public/js/app.js"></script>