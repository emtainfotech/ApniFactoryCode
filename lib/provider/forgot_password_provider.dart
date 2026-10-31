import 'dart:async';
import 'dart:developer';
import 'package:apni_factory/model/get_otp_model.dart';
import 'package:apni_factory/model/reset_password_model.dart';
import 'package:apni_factory/model/verify_otp_model.dart';
import 'package:apni_factory/screens/reset_password_screen.dart';
import 'package:apni_factory/screens/sign_in.dart';
import 'package:apni_factory/utils.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import '../api_services/creator_api_services.dart';

class ForgotPasswordProvider extends ChangeNotifier {
  TextEditingController emailController = TextEditingController();
  TextEditingController passwordController = TextEditingController();
  TextEditingController otpController = TextEditingController();
  bool isOtpReceived = false;

  bool loadingGetOtp = false;
  Timer? timer;
  int otpTimer = 30;
  bool showResend = false;

  void showOtpTimer() {
    timer?.cancel();
    otpTimer = 30;
    showResend = false;

    timer = Timer.periodic(const Duration(seconds: 1), (t) {
      if (!hasListeners) {
        t.cancel();
        return;
      }

      otpTimer--;
      if (otpTimer == 0) {
        showResend = true;
        t.cancel();
      }
      notifyListeners();
    });
  }
  // void showOtpTimer() {
  //   timer = Timer.periodic(Duration(seconds: 1), (Timer t) {
  //     otpTimer--;
  //     notifyListeners();
  //     if (otpTimer == 0) {
  //       timer!.cancel();
  //       showResend = true;
  //       notifyListeners();
  //     }
  //   });
  //   notifyListeners();
  // }

  void disposeTimer() {
    timer?.cancel();
    timer = null;
  }

  // void disposeTimer(){
  //   if(timer!=null){
  //      timer!.cancel();
  //   }

  //   notifyListeners();
  // }

  Future<void> getOtp(
    double height, {
    required BuildContext context,
    String? email,
  }) async {
    try {
      if (email != null && email.isNotEmpty) {
        loadingGetOtp = true;
        log(email.toString());
        GetOtpModel? getOtpModel = await CreatorApiServices.getOtpDetails(
            context,
            email: email,
            sendon: "email");
        notifyListeners();

        if (getOtpModel != null && getOtpModel.code == 100) {
          isOtpReceived = true;
          showOtpTimer();
          Utils.commonSnackBar(context,
              height: height, message: getOtpModel.msg.toString());
          loadingGetOtp = false;

          notifyListeners();
        } else if (getOtpModel != null && getOtpModel.code == 500) {
          loadingGetOtp = false;
          Utils.commonSnackBar(context,
              height: height, message: getOtpModel.msg.toString());
          notifyListeners();
        } else {
          loadingGetOtp = false;
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
  bool loadingVerifyOtp = false;

  Future<void> verifyOtp(double height,
      {required BuildContext context, String? email, String? otp}) async {
    try {
      if (email != null && email.isNotEmpty && otp != null && otp.isNotEmpty) {
        loadingVerifyOtp = true;

        verifyOtpModel = await CreatorApiServices.verifyOtpDetails(context,
            email: email, otp: otp);
        notifyListeners();

        if (verifyOtpModel != null && verifyOtpModel?.code == 100) {
          otpController.clear();
          isOtpReceived = false;
          notifyListeners();
          timer?.cancel();
          Utils.commonSnackBar(context,
              height: height, message: verifyOtpModel!.msg.toString());
          loadingVerifyOtp = false;
          if (hasListeners) notifyListeners();

          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => ResetPasswordScreen()),
          );
        } else if (verifyOtpModel != null && verifyOtpModel?.code == 500) {
          loadingVerifyOtp = false;
          Utils.commonSnackBar(context,
              height: height, message: verifyOtpModel!.msg.toString());
          notifyListeners();
        } else {
          loadingVerifyOtp = false;
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

  bool loadingResetPassword = false;
  Future<void> resetPassword(double height,
      {required BuildContext context,
      String? id,
      String? password,
      String? emailmobile}) async {
    try {
      if (id != null &&
          id.isNotEmpty &&
          password != null &&
          password.isNotEmpty &&
          emailmobile != null &&
          emailmobile.isNotEmpty &&
          password.length >= 6) {
        loadingResetPassword = true;

        ResetPasswordModel? resetPasswordModel =
            await CreatorApiServices.resetPasswordDetails(context,
                id: id, password: password, emailmobile: emailmobile);
        notifyListeners();

        if (resetPasswordModel != null && resetPasswordModel.code == 100) {
          emailController.clear();
          passwordController.clear();
          Utils.commonSnackBar(context,
              height: height, message: resetPasswordModel.msg.toString());
          Navigator.push(context, MaterialPageRoute(
            builder: (context) {
              return SignInScreen();
            },
          ));
          loadingResetPassword = false;

          notifyListeners();
        } else if (resetPasswordModel != null &&
            resetPasswordModel.code == 500) {
          loadingResetPassword = false;
          Utils.commonSnackBar(context,
              height: height, message: resetPasswordModel.msg.toString());
          notifyListeners();
        } else {
          loadingResetPassword = false;
          Utils.commonSnackBar(context,
              height: height, message: "Something Went Wrong");
          notifyListeners();
        }
      } else {
        Utils.commonSnackBar(context,
            height: height, message: "Enter Password more than 6 letter");
        notifyListeners();
      }
    } catch (e) {
      log("reset password error: ${e.toString()}");
    }
    notifyListeners();
  }
}
