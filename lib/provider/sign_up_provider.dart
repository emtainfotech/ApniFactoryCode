import 'dart:async';
import 'dart:developer';
import 'package:apni_factory/api_services/creator_api_services.dart';
import 'package:apni_factory/model/get_otp_model.dart';
import 'package:apni_factory/model/gst_error_verification_model.dart';
import 'package:apni_factory/model/gst_verification_model.dart';
import 'package:apni_factory/model/sign_up_model.dart';
import 'package:apni_factory/model/verify_otp_model.dart';
import 'package:apni_factory/screens/sign_in.dart';
import 'package:apni_factory/utils.dart';
import 'package:flutter/material.dart';

class SignUpProvider extends ChangeNotifier {
  TextEditingController emailController = TextEditingController();
  TextEditingController passwordController = TextEditingController();
  TextEditingController mobileNumberController = TextEditingController();
  TextEditingController confirmPasswordController = TextEditingController();
  TextEditingController nameController = TextEditingController();

  bool loadingSignUp = false;
  bool isPasswordVisible = true;
  bool isConfirmPasswordVisible = true;
  // final FirebaseAuth auth = FirebaseAuth.instance;
  // User? user;
  // GoogleSignIn googleSignIn = GoogleSignIn(
  //
  //
  // );

  // void authChange() {
  //   auth.authStateChanges().listen((event) {
  //     user = event;
  //   });
  //   notifyListeners();
  // }

  // Future<void> handleGoogleSignUp({
  //   required double height,
  //   required BuildContext context,
  // }) async {
  //   try {
  //     GoogleAuthProvider _googleAuthProvider = GoogleAuthProvider();
  //     await auth.signInWithProvider(_googleAuthProvider);
  //     if (user != null) {
  //       user!.getIdToken().then((value) {
  //         log(value.toString());
  //       });
  //       loadingSignUp = true;
  //       SignUpModel? signUpModel = await CreatorApiServices.signUpDetails(
  //           context,
  //           name: user?.displayName ?? "",
  //           email: user?.email ?? "",
  //           mobileNo: user?.phoneNumber ?? "",
  //           password: null,
  //           regby: "google",
  //           gstPan: null,
  //           whatsappNumber: null);
  //       log("message");
  //       notifyListeners();
  //       log(signUpModel.toString());
  //       if (signUpModel != null && signUpModel.code == 100) {
  //         log(signUpModel.toString());
  //         loadingSignUp = false;
  //         bool userName =
  //             await SharedPref.setUserName(signUpModel.data!.name.toString());
  //         bool userEmailId = await SharedPref.setUserEmailId(
  //             signUpModel.data!.email.toString());
  //         bool userLoginId =
  //             await SharedPref.setUserLoginId(signUpModel.data!.id.toString());
  //         if (userLoginId && userName && userEmailId) {
  //           Navigator.push(context, MaterialPageRoute(
  //             builder: (context) {
  //               return AllScreen();
  //             },
  //           ));
  //         }

  //         notifyListeners();
  //       } else if (signUpModel != null && signUpModel.code == 500) {
  //         loadingSignUp = false;
  //         Utils.commonSnackBar(context,
  //             height: height, message: signUpModel.msg.toString());
  //         notifyListeners();
  //       } else {
  //         loadingSignUp = false;
  //         Utils.commonSnackBar(context,
  //             height: height, message: "Something Went Wrong");
  //         notifyListeners();
  //       }

  //       log(user!.email.toString());
  //       log(user?.displayName ?? "");
  //       log(user?.phoneNumber ?? "");
  //       notifyListeners();
  //     }
  //     notifyListeners();
  //   } catch (e) {
  //     log("google sign up error: ${e.toString()}");
  //   }
  //   notifyListeners();
  // }


  void onMobileChanged() {
  // Reset everything when number changes
  isMobileOtpVerified = false;
  isMobileOtpReceived = false;
  mobileOtpController.clear();

  notifyListeners();
}

  void passwordVisibility() {
    isPasswordVisible = !isPasswordVisible;
    notifyListeners();
  }

  void confirmPasswordVisibility() {
    isConfirmPasswordVisible = !isConfirmPasswordVisible;
    notifyListeners();
  }

  Future<void> signUp(double height,
      {required BuildContext context,
      String? name,
      String? email,
      String? password,
      String? mobileNumber,
      String? confirmPassword,
      required String regby}) async {
    try {
      if (name != null &&
          name.isNotEmpty &&
          email != null &&
          email.isNotEmpty &&
          password != null &&
          password.isNotEmpty &&
          mobileNumber != null &&
          mobileNumber.isNotEmpty &&
          confirmPassword != null &&
          confirmPassword.isNotEmpty) {
        if (password.length >= 6) {
          if (email.isValidEmail()) {
            if (password == confirmPassword) {
              if (isEmailOtpvVerified == true) {
                if (isMobileOtpVerified == true) {
                  loadingSignUp = true;
                  notifyListeners();
                  SignUpModel? signUpModel =
                      await CreatorApiServices.signUpDetails(
                    context,
                    name: name,
                    email: email,
                    mobileNo: mobileNumber,
                    password: password,
                    regby: regby,
                    // gstPan: gstPan,
                  );
                  notifyListeners();
                  if (signUpModel != null && signUpModel.code == 100) {
                    loadingSignUp = false;
                    notifyListeners();
                    nameController.clear();
                    emailController.clear();
                    mobileNumberController.clear();
                    passwordController.clear();
                    confirmPasswordController.clear();

                    Navigator.push(context, MaterialPageRoute(
                      builder: (context) {
                        return SignInScreen();
                      },
                    ));

                    notifyListeners();
                  } else if (signUpModel != null && signUpModel.code == 500) {
                    loadingSignUp = false;
                    Utils.commonSnackBar(context,
                        height: height, message: signUpModel.msg.toString());
                    notifyListeners();
                  } else {
                    loadingSignUp = false;

                    notifyListeners();
                  }
                } else {
                  loadingSignUp = false;
                  Utils.commonSnackBar(context,
                      height: height, message: "Please verify Mobile Number");
                  notifyListeners();
                }
              } else {
                loadingSignUp = false;
                Utils.commonSnackBar(context,
                    height: height, message: "Please verify Email");
                notifyListeners();
              }
            } else {
              loadingSignUp = false;
              Utils.commonSnackBar(context,
                  height: height,
                  message: "Confirm Password should be same as password");
              notifyListeners();
            }
          } else {
            loadingSignUp = false;
            Utils.commonSnackBar(context,
                height: height, message: "Enter Correct Email");
            notifyListeners();
          }
        } else {
          loadingSignUp = false;
          Utils.commonSnackBar(context,
              height: height, message: "Password should be greater than 6");
          notifyListeners();
        }
      } else {
        Utils.commonSnackBar(context,
            height: height, message: "Enter all details");
        notifyListeners();
      }
    } catch (e) {
      log("signup error: ${e.toString()}");
    }
    notifyListeners();
  }

  TextEditingController emailOtpController = TextEditingController();
  bool isEmailOtpReceived = false;
  GetOtpModel? getOtpModel;
  bool loadingGetEmailOtp = false;
  Future<void> getEmailOtp(
    double height, {
    required BuildContext context,
    String? email,
  }) async {
    try {
      if (email != null && email.isNotEmpty) {
        loadingGetEmailOtp = true;
        notifyListeners();
        log(email.toString());
        GetOtpModel? getOtpModel = await CreatorApiServices.getOtpDetails(
            context,
            email: email,
            sendon: "email");
        notifyListeners();

        if (getOtpModel != null && getOtpModel.code == 100) {
          isEmailOtpReceived = true;
          // showOtpTimer();
          Utils.commonSnackBar(context,
              height: height, message: getOtpModel.msg.toString());
          loadingGetEmailOtp = false;

          notifyListeners();
        } else if (getOtpModel != null && getOtpModel.code == 500) {
          loadingGetEmailOtp = false;
          Utils.commonSnackBar(context,
              height: height, message: getOtpModel.msg.toString());
          notifyListeners();
        } else {
          loadingGetEmailOtp = false;
          Utils.commonSnackBar(context,
              height: height, message: "Something Went Wrong");
          notifyListeners();
        }
      } else {
        Utils.commonSnackBar(context, height: height, message: "Enter Email");
        notifyListeners();
      }
    } catch (e) {
      log("get otp error: ${e.toString()}");
    }
    notifyListeners();
  }

  VerifyOtpModel? verifyOtpModel;
  bool loadingVerifyEmailOtp = false;
  bool isEmailOtpvVerified = false;
  Future<void> verifyEmailOtp(double height,
      {required BuildContext context, String? email, String? otp}) async {
    try {
      if (email != null && email.isNotEmpty && otp != null && otp.isNotEmpty) {
        loadingVerifyEmailOtp = true;
        notifyListeners();
        verifyOtpModel = await CreatorApiServices.verifyOtpDetails(context,
            email: email, otp: otp);
        notifyListeners();

        if (verifyOtpModel != null && verifyOtpModel?.code == 100) {
          emailOtpController.clear();
          isEmailOtpReceived = false;
          isEmailOtpvVerified = true;
          notifyListeners();
          // timer!.cancel();
          Utils.commonSnackBar(context,
              height: height, message: verifyOtpModel!.msg.toString());

          loadingVerifyEmailOtp = false;

          notifyListeners();
        } else if (verifyOtpModel != null && verifyOtpModel?.code == 500) {
          loadingVerifyEmailOtp = false;
          Utils.commonSnackBar(context,
              height: height, message: verifyOtpModel!.msg.toString());
          notifyListeners();
        } else {
          loadingVerifyEmailOtp = false;
          Utils.commonSnackBar(context,
              height: height, message: "Something Went Wrong");
          notifyListeners();
        }
      } else {
        Utils.commonSnackBar(context,
            height: height, message: "Enter Email & Otp");
        notifyListeners();
      }
    } catch (e) {
      log("verify otp error: ${e.toString()}");
    }
    notifyListeners();
  }

  TextEditingController mobileOtpController = TextEditingController();
  bool isMobileOtpReceived = false;

  bool loadingGetMobileOtp = false;
  Future<void> getMobileOtp(double height,
      {required BuildContext context, String? mobile}) async {
    try {
      if (mobile != null && mobile.isNotEmpty) {
        loadingGetMobileOtp = true;
        notifyListeners();
        log(mobile.toString());
        GetOtpModel? getOtpModel = await CreatorApiServices.getOtpDetails(
            context,
            email: mobile,
            sendon: "mobile");
        notifyListeners();

        if (getOtpModel != null && getOtpModel.code == 100) {
          isMobileOtpReceived = true;
          // showOtpTimer();
          Utils.commonSnackBar(context,
              height: height, message: getOtpModel.msg.toString());
          loadingGetMobileOtp = false;

          notifyListeners();
        } else if (getOtpModel != null && getOtpModel.code == 500) {
          loadingGetMobileOtp = false;
          Utils.commonSnackBar(context,
              height: height, message: getOtpModel.msg.toString());
          notifyListeners();
        } else {
          loadingGetMobileOtp = false;
          Utils.commonSnackBar(context,
              height: height, message: "Something Went Wrong");
          notifyListeners();
        }
      } else {
        Utils.commonSnackBar(context,
            height: height, message: "Enter Mobile No");
        notifyListeners();
      }
    } catch (e) {
      log("get otp error: ${e.toString()}");
    }
    notifyListeners();
  }

  bool loadingVerifyMobileOtp = false;
  bool isMobileOtpVerified = false;
  Future<void> verifyMobileOtp(double height,
      {required BuildContext context, String? mobile, String? otp}) async {
    try {
      if (mobile != null &&
          mobile.isNotEmpty &&
          otp != null &&
          otp.isNotEmpty) {
        loadingVerifyMobileOtp = true;
        notifyListeners();
        verifyOtpModel = await CreatorApiServices.verifyOtpDetails(context,
            email: mobile, otp: otp);
        notifyListeners();

        if (verifyOtpModel != null && verifyOtpModel?.code == 100) {
          mobileOtpController.clear();
          isMobileOtpReceived = false;
          isMobileOtpVerified = true;
          notifyListeners();
          // timer!.cancel();
          Utils.commonSnackBar(context,
              height: height, message: verifyOtpModel!.msg.toString());

          loadingVerifyMobileOtp = false;

          notifyListeners();
        } else if (verifyOtpModel != null && verifyOtpModel?.code == 500) {
          loadingVerifyMobileOtp = false;
          Utils.commonSnackBar(context,
              height: height, message: verifyOtpModel!.msg.toString());
          notifyListeners();
        } else {
          loadingVerifyMobileOtp = false;
          Utils.commonSnackBar(context,
              height: height, message: "Something Went Wrong");
          notifyListeners();
        }
      } else {
        Utils.commonSnackBar(context,
            height: height, message: "Enter Mobile Number & Otp");
        notifyListeners();
      }
    } catch (e) {
      log("verify otp error: ${e.toString()}");
    }
    notifyListeners();
  }
}

extension EmailValidator on String {
  bool isValidEmail() {
    return RegExp(
            r'^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$')
        .hasMatch(this);
  }
}
