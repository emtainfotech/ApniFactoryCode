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
     <title>Login - Apni Factory</title>

 	<div class="page-wrapper ms-0">
 	    <div class="container">
 	           <div class="row">
			<div class="page-content offset-lg-3 col-lg-6 offset-lg-3">
			    <div class="card">
			        <div class="card-body">
			         
								<div class=" p-4 rounded">
									<div class="text-center ">
									    <img src="{{ $_ENV['APP_URL']}}/public/img/Apni-Factory-3.png" class="logo-login" alt="logo icon">
										<!--<h3 class="mt-4">Sign in</h3>-->
										
										</p>
									</div>
							
									<div class="form-body">
									      <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                    <label class="form-check-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Login') }}
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
								
											<div class="text-center pt-5">
										
										<p>Don't have an account yet? <a href="{{url('register')}}">Sign up here</a>
										</p>
									</div>
									</div>
								</div>
							</div>
							</div>
</div>
</div>
</div>
</div>
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
	<script>
		$(document).ready(function () {
			$('#image-uploadify').imageuploadify();
		})
        tinymce.init({
		  selector: '#mytextarea'
		});
       
		
		// $(document).ready(function() {
		// 	var table = $('#example2').DataTable( {
		// 		lengthChange: false,
		// 		buttons: [ 'copy', 'excel', 'pdf', 'print']
		// 	} );
		 
		// 	table.buttons().container()
		// 		.appendTo( '#example2_wrapper .col-md-6:eq(0)' );
		// } );
	</script>
	<!--app JS-->
	<script src="{{ $_ENV['APP_URL']}}/public/js/app.js"></script>


</div>
@endsection
