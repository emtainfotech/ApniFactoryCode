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
     <meta name="csrf-token" content="{{ csrf_token() }}">

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
							<form method="POST" action="{{ route('register') }}" id="registerForm">
    @csrf
    <div class="row">
        <!-- Main Category -->
        <div class="col-12 mb-3">
            <label for="inputMainCategory" class="form-label">
                Main Category
            </label>

            <select name="category_id"
                    id="inputMainCategory"
                    class="form-select @error('category_id') is-invalid @enderror">

                <option value="" disabled selected>
                    Select Main Category
                </option>

                @foreach($mainCategories as $category)
                    <option value="{{ $category->id }}"
                        {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach

            </select>

            @error('category_id')
                <div class="text-danger small">
                    {{ $message }}
                </div>
            @enderror
        </div>



        <!-- GST -->
        <div class="col-md-6 mb-3">
            <label for="inputgst" class="form-label">
                GST Number
            </label>

            <div class="input-group">

                <input type="text"
                       class="form-control @error('gstno') is-invalid @enderror"
                       id="inputgst"
                       name="gstno"
                       placeholder="15 Digit GST"
                       value="{{ old('gstno') }}">

                <button type="button"
                        class="btn btn-primary"
                        id="verifyGSTBtn">
                    Verify
                </button>

            </div>

            <small id="gstMessage"></small>

            @error('gstno')
                <div class="text-danger small">
                    {{ $message }}
                </div>
            @enderror
        </div>



        <!-- Name -->
        <div class="col-md-6 mb-3">
            <label for="inputFirstName" class="form-label">
                Name
            </label>

            <input type="text"
                   name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   id="inputFirstName"
                   placeholder="John"
                   value="{{ old('name') }}">

            @error('name')
                <div class="text-danger small">
                    {{ $message }}
                </div>
            @enderror
        </div>



        <!-- Company Name -->
        <div class="col-md-6 mb-3">
            <label for="inputCompanyName" class="form-label">
                Company Name
            </label>

            <input type="text"
                   name="cmpname"
                   class="form-control @error('cmpname') is-invalid @enderror"
                   id="inputCompanyName"
                   placeholder="Company Name"
                   value="{{ old('cmpname') }}">

            @error('cmpname')
                <div class="text-danger small">
                    {{ $message }}
                </div>
            @enderror
        </div>



        <!-- Email -->
        <div class="col-md-6 mb-3">

            <label for="inputEmailAddress" class="form-label">
                Email Address
            </label>

            <div class="input-group">

                <input type="email"
                       name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       id="inputEmailAddress"
                       placeholder="example@user.com"
                       value="{{ old('email') }}">

                <button type="button"
                        class="btn btn-warning"
                        id="sendEmailOtpBtn">
                    Verify
                </button>

            </div>

            <!-- Email OTP -->
            <div class="input-group mt-2 d-none" id="emailOtpBox">

                <input type="text"
                       id="emailOtp"
                       class="form-control"
                       placeholder="Enter Email OTP">

                <button type="button"
                        class="btn btn-success"
                        id="verifyEmailOtpBtn">
                    Submit OTP
                </button>

            </div>

            <small id="emailMessage"></small>

            @error('email')
                <div class="text-danger small">
                    {{ $message }}
                </div>
            @enderror
        </div>



        <!-- Mobile -->
        <div class="col-md-6 mb-3">

            <label for="inputmobile" class="form-label">
                Mobile Number
            </label>

            <div class="input-group">

                <input type="tel"
                       name="mobile"
                       class="form-control @error('mobile') is-invalid @enderror"
                       id="inputmobile"
                       placeholder="+919876541230"
                       value="{{ old('mobile') }}">

                <button type="button"
                        class="btn btn-warning"
                        id="sendMobileOtpBtn">
                    Verify
                </button>

            </div>

            <!-- Mobile OTP -->
            <div class="input-group mt-2 d-none" id="mobileOtpBox">

                <input type="text"
                       id="mobileOtp"
                       class="form-control"
                       placeholder="Enter Mobile OTP">

                <button type="button"
                        class="btn btn-success"
                        id="verifyMobileOtpBtn">
                    Submit OTP
                </button>

            </div>

            <small id="mobileMessage"></small>

            @error('mobile')
                <div class="text-danger small">
                    {{ $message }}
                </div>
            @enderror
        </div>



        <!-- CRN -->
        <div class="col-md-6 mb-3">
            <label for="inputcrnno" class="form-label">
                CRN No
            </label>

            <input type="text"
                   name="crnno"
                   class="form-control @error('crnno') is-invalid @enderror"
                   id="inputcrnno"
                   placeholder="1236547899"
                   value="{{ old('crnno') }}">

            @error('crnno')
                <div class="text-danger small">
                    {{ $message }}
                </div>
            @enderror
        </div>



        <!-- City -->
        <div class="col-md-6 mb-3">
            <label for="inputcity" class="form-label">
                City
            </label>

            <input type="text"
                   class="form-control @error('city') is-invalid @enderror"
                   id="inputcity"
                   name="city"
                   placeholder="City"
                   value="{{ old('city') }}">

            @error('city')
                <div class="text-danger small">
                    {{ $message }}
                </div>
            @enderror
        </div>



        <!-- Password -->
        <div class="col-md-6 mb-3">

            <label for="inputChoosePassword" class="form-label">
                Password
            </label>

            <div class="input-group" id="show_hide_password">

                <input type="password"
                       class="form-control border-end-0 @error('password') is-invalid @enderror"
                       name="password"
                       id="inputChoosePassword"
                       placeholder="Enter Password">

                <a href="javascript:void(0)"
                   class="input-group-text bg-transparent">

                    <i class="bx bx-hide"></i>

                </a>

            </div>

            @error('password')
                <div class="text-danger small">
                    {{ $message }}
                </div>
            @enderror
        </div>



        <!-- Confirm Password -->
        <div class="col-md-6 mb-3">

            <label for="password-confirm" class="form-label">
                Confirm Password
            </label>

            <input id="password-confirm"
                   type="password"
                   class="form-control @error('password_confirmation') is-invalid @enderror"
                   name="password_confirmation"
                   required
                   autocomplete="new-password"
                   placeholder="Confirm Password">

            @error('password_confirmation')
                <div class="text-danger small">
                    {{ $message }}
                </div>
            @enderror
        </div>



        <!-- Terms -->
        <div class="col-12 mb-3">

            <div class="form-check form-switch">

                <input class="form-check-input @error('terms') is-invalid @enderror"
                       type="checkbox"
                       id="flexSwitchCheckChecked"
                       name="terms"
                       value="1">

                <label class="form-check-label"
                       for="flexSwitchCheckChecked">

                    I read and agree to <a href="/TermsAndCondition">Terms & Conditions</a>

                </label>

            </div>

            @error('terms')
                <div class="text-danger small">
                    {{ $message }}
                </div>
            @enderror
        </div>



        <!-- Submit -->
        <div class="row mb-0">

            <div class="col-md-6 offset-md-4">

                <button type="submit"
                        class="btn btn-primary">

                    {{ __('Register') }}

                </button>

            </div>

        </div>

    </div>
</form>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});


// ================= GST VERIFY =================

$("#verifyGSTBtn").click(function () {

    let gst = $("#inputgst").val();

    if(gst == ''){
        alert("Enter GST Number");
        return;
    }

    $.ajax({
        url: "/verify-gst",
        type: "POST",
         headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
        data: {
            gst: gst
        },

        success: function (response) {
            if(response.status == true){
                $("#gstMessage")
                    .html("GST Verified")
                    .css("color","green");
                $("#verifyGSTBtn")
                    .removeClass('btn-primary')
                    .addClass('btn-success')
                    .text('Verified');
                // AUTO FILL
                $("#inputCompanyName").val(response.data.company_name);
                $("#inputFirstName").val(response.data.legal_name);
                $("#inputcity").val(response.data.city);
            }else{
                $("#gstMessage")
                    .html(response.message)
                    .css("color","red");
            }
        }
    });

});



// ================= EMAIL OTP SEND =================

$("#sendEmailOtpBtn").click(function () {

    let emailipt = $("#inputEmailAddress").val();

    $.ajax({
        url: "/api/sendotp",
        type: "POST",
        data: {
            emailmobile: emailipt,
            sendon:'email'
        },

        success: function (response) {
            $("#emailOtpBox").removeClass('d-none');
            $("#emailMessage")
                .html("OTP Sent")
                .css("color","green");
        }
    });

});



// ================= EMAIL OTP VERIFY =================

$("#verifyEmailOtpBtn").click(function () {
    $.ajax({
        url: "/api/verifyotp",
        type: "POST",
        data: {
            otp: $("#emailOtp").val(),
            emailmobile:$("#inputEmailAddress").val()
        },
        dataType: 'json',
        success: function (response) {
            if(response.status){
                $("#sendEmailOtpBtn")
                    .removeClass('btn-warning')
                    .addClass('btn-success')
                    .text('Verified');
                $("#emailOtpBox").addClass('d-none');
                $("#emailMessage")
                    .html("Email Verified")
                    .css("color","green");
            }else{
                 $("#emailMessage")
                    .html("Something Went Wroung")
                    .css("color","red");
            }
        }
    });
});
// ================= MOBILE OTP SEND =================

$("#sendMobileOtpBtn").click(function () {
    $.ajax({
        url: "/api/sendotp",
        type: "POST",
        data: {
            emailmobile: $("#inputmobile").val(),
            sendon:'mobile'
        },
        dataType: 'json',
        success: function (response) {
            if(response.status){
            $("#mobileOtpBox").removeClass('d-none');

            $("#mobileMessage")
                .html("OTP Sent")
                .css("color","green");
        }
    }
    });
});




// ================= MOBILE OTP VERIFY =================

$("#verifyMobileOtpBtn").click(function () {

    $.ajax({
        url: "/api/verifyotp",
        type: "POST",
        data: {
            otp: $("#mobileOtp").val(),
            emailmobile: $("#inputmobile").val(),
        },
        dataType: 'json',

        success: function (response) {

            if(response.status){

                $("#sendMobileOtpBtn")
                    .removeClass('btn-warning')
                    .addClass('btn-success')
                    .text('Verified');

                $("#mobileOtpBox").addClass('d-none');

                $("#mobileMessage")
                    .html("Mobile Verified")
                    .css("color","green");
            }else{
                 $("#mobileMessage")
                    .html("Something Went Wroung")
                    .css("color","red");
            }
        }
    });

});

</script>
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