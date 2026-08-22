<!doctype html>
<html lang="en">
<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	<link rel="icon" href="#" type="image/png" />
	<!--plugins-->
	<link href="https://laserboook.com/apnifactory/public/css/imageuploadify.min.css" rel="stylesheet" />
	<link href="https://laserboook.com/apnifactory/public/css/simplebar.css" rel="stylesheet" />
	<link href="https://laserboook.com/apnifactory/public/css/perfect-scrollbar.css" rel="stylesheet" />
	<link href="https://laserboook.com/apnifactory/public/css/metisMenu.min.css" rel="stylesheet" />
    <link href="https://laserboook.com/apnifactory/public/css/dataTables.bootstrap5.min.css" rel="stylesheet">
	<!-- loader-->
	<!-- Bootstrap CSS -->
	<link href="https://laserboook.com/apnifactory/public/css/bootstrap.min.css" rel="stylesheet">

	<link href="https://laserboook.com/apnifactory/public/css/bootstrap-extended.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
	<link href="https://laserboook.com/apnifactory/public/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
     <title>Login - Apni Factory</title>
</head>

<body>
 	<div class="page-wrapper ms-0">
 	    <div class="container">
 	           <div class="row">
			<div class="page-content offset-lg-3 col-lg-6 offset-lg-3">
			    <div class="card">
			        <div class="card-body">
			         
								<div class=" p-4 rounded">
									<div class="text-center ">
									    <img src="https://laserboook.com/apnifactory/public/img/Apni-Factory-3.png" class="logo-login" alt="logo icon">
										<h3 class="mt-4">Sign in 1231</h3>
										
										</p>
									</div>
							
									<div class="form-body">
										<form class="row g-3" action="{{url('seller/dashboard')}}" >
											<div class="col-12">
												<label for="inputEmailAddress" class="form-label">Email Address</label>
												<input type="email" class="form-control" id="inputEmailAddress" placeholder="Email Address">
											</div>
											<div class="col-12">
												<label for="inputChoosePassword" class="form-label">Enter Password</label>
												<div class="input-group" id="show_hide_password">
													<input type="password" class="form-control border-end-0" id="inputChoosePassword" value="" placeholder="Enter Password"> <a href="javascript:;" class="input-group-text bg-transparent"><i class="bx bx-hide"></i></a>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-check form-switch">
													<input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked" checked="">
													<label class="form-check-label" for="flexSwitchCheckChecked">Remember Me</label>
												</div>
											</div>
											<div class="col-md-6 text-end">	<a href="authentication-forgot-password.html">Forgot Password ?</a>
											</div>
											<div class="col-12">
												<div class="d-grid">
													<button type="submit" class="btn btn-primary"><i class="bx bxs-lock-open"></i>Sign in</button>
												</div>
											</div>
										</form>
											<div class="text-center pt-5">
										
										<p>Don't have an account yet? <a href="signup">Sign up here</a>
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
	<script src="https://laserboook.com/apnifactory/public/js/bootstrap.bundle.min.js"></script>
	<!--plugins-->
	<script src="https://laserboook.com/apnifactory/public/js/jquery.min.js"></script>
	<script src="https://laserboook.com/apnifactory/public/js/simplebar.min.js"></script>
	<script src="https://laserboook.com/apnifactory/public/js/metisMenu.min.js"></script>
	<script src="https://laserboook.com/apnifactory/public/js/perfect-scrollbar.js"></script>
	<script src="https://laserboook.com/apnifactory/public/js/imageuploadify.min.js"></script>
    <script src='https://cdn.tiny.cloud/1/vdqx2klew412up5bcbpwivg1th6nrh3murc6maz8bukgos4v/tinymce/5/tinymce.min.js' ></script>
    <script src="https://laserboook.com/apnifactory/public/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://laserboook.com/apnifactory/public/js/jquery.dataTables.min.js"></script>
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
	<script src="https://laserboook.com/apnifactory/public/js/app.js"></script>
</body>

</html>
