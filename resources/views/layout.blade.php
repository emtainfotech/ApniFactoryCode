<!doctype html>
<html lang="en">
<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	<link rel="icon" href="#" type="image/png" />
	<!--plugins-->
	<link href="{{ asset('public/css/imageuploadify.min.css')}}" rel="stylesheet" />
	<!--<link href="{{ asset('public/css/simplebar.css')}}" rel="stylesheet" />-->
	<link href="{{ asset('public/css/perfect-scrollbar.css')}}" rel="stylesheet" />
	<link href="{{ asset('public/css/metisMenu.min.css')}}" rel="stylesheet" />

	<link href="{{ asset('public/css/customsidebar.css')}}" rel="stylesheet" />
	<!-- loader-->
	<!-- Bootstrap CSS -->
	<link href="{{ asset('public/css/bootstrap.min.css')}}" rel="stylesheet">

	<link href="{{ asset('public/css/bootstrap-extended.css')}}" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
	<link href="{{ asset('public/css/app.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
     <link href="{{ asset('public/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <script src="{{ asset('public/js/customlist.js') }}" ></script>
        <meta name="csrf-token" content="{{ csrf_token() }}" />
     <title>@yield('title', 'Apni Factory')</title>
</head>

<body>
	<!--wrapper-->
	<div class="wrapper"> 
		<!--end page wrapper -->
		@include('sidebar')
		
		 @yield('content')
		<!--start overlay-->
		<div class="overlay toggle-icon"></div>
		<!--end overlay-->
		<!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class="fa-solid fa-angle-up"></i></a>
		<!--End Back To Top Button-->
		<footer class="page-footer">
			<p class="mb-0">Copyright © 2021. All right reserved.</p>
		</footer>
	</div>
	<!--end wrapper-->

	<!-- Bootstrap JS -->
     	<script src="{{ asset('public/js/bootstrap.bundle.min.js')}}"></script>
	<!--plugins-->
		<script src="{{ asset('public/js/jquery.min.js')}}"></script>
     	     <!--<script src="{{ asset('public/js/jquery.dataTables.min.js')}}"></script>-->
<!--  <script src="{{ asset('public/js/dataTables.bootstrap5.min.js')}}"></script>-->
   <!--<script src="{{ asset('public/js/databasetablebutton.js')}}"></script>-->
   
   
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
 <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.js"></script>
 <script src="https://cdn.datatables.net/buttons/3.1.2/js/dataTables.buttons.js"></script>
 <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.bootstrap5.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
 <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.html5.min.js"></script>
 <script src="https://cdn.datatables.net/buttons/3.1.2/js/buttons.print.min.js"></script>

	<script src="{{ asset('public/js/simplebar.min.js')}}"></script>
	<script src="{{ asset('public/js/metisMenu.min.js')}}"></script>
	<script src="{{ asset('public/js/perfect-scrollbar.js')}}"></script>

  <script src="{{ asset('public/js/nicedit.js')}}"></script>
	<script src="{{ asset('public/js/imageuploadify.min.js')}}"></script>
    	<script>
$(document).ready(function() {
   new DataTable('#example2', {
    layout: {
        topStart: {
            buttons: ['excel', 'pdf', 'colvis']
        }
    }
});
} );
</script>


	<script>

		 bkLib.onDomLoaded(nicEditors.allTextAreas);
		 

	</script>

	<!--app JS-->
	<script src="{{ asset('public/js/app.js')}}"></script>
</body>

</html>