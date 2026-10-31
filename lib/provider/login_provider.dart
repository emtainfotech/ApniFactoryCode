import 'dart:developer';

import 'package:apni_factory/api_services/creator_api_services.dart';
import 'package:apni_factory/api_services/notification_services.dart';
import 'package:apni_factory/model/login_model.dart';
import 'package:apni_factory/screens/all_screen.dart';

import 'package:apni_factory/shared_pref.dart';
import 'package:apni_factory/utils.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:firebase_messaging/firebase_messaging.dart';

import 'package:flutter/material.dart';

class LoginProvider extends ChangeNotifier {
  TextEditingController emailController = TextEditingController();
  TextEditingController passwordController = TextEditingController();
  bool loadingLogin = false;
  bool isPasswordVisible = true;
  final FirebaseAuth auth = FirebaseAuth.instance;
  User? user;
  void passwordVisibility() {
    isPasswordVisible = !isPasswordVisible;
    notifyListeners();
  }

  void authChange() {
    auth.authStateChanges().listen((event) {
      user = event;
    });
    notifyListeners();
  }

  Future<void> handleGoogleSignIn({
    required double height,
    required BuildContext context,
  }) async {
    try {
      GoogleAuthProvider _googleAuthProvider = GoogleAuthProvider();
      await auth.signInWithProvider(_googleAuthProvider);
      if (user != null) {
        user!.getIdToken().then((value) {
          log(value.toString());
        });
        loadingLogin = true;
        LoginModel? loginModel = await CreatorApiServices.loginDetails(context,
            email: user?.email ?? "", password: null);
        notifyListeners();
        if (loginModel != null && loginModel.code == 100) {
          emailController.clear();

          loadingLogin = false;
          bool userName =
              await SharedPref.setUserName(loginModel.data!.name.toString());
          bool userEmailId = await SharedPref.setUserEmailId(
              loginModel.data!.email.toString());

          bool userLoginId =
              await SharedPref.setUserLoginId(loginModel.data!.id.toString());
          if (userLoginId && userName && userEmailId) {
            Navigator.push(context, MaterialPageRoute(
              builder: (context) {
                return AllScreen();
              },
            ));
          }

          notifyListeners();
        } else if (loginModel != null && loginModel.code == 500) {
          loadingLogin = false;
          emailController.clear();

          Utils.commonSnackBar(context,
              height: height, message: loginModel.msg.toString());
          notifyListeners();
        } else {
          loadingLogin = false;
          Utils.commonSnackBar(context,
              height: height, message: "Something Went Wrong");
          notifyListeners();
        }
      }
    } catch (e) {
      log("google sign in error: ${e.toString()}");
    }
    notifyListeners();
  }

  FirebaseMessaging messaging = FirebaseMessaging.instance;
  Future<String> getDeviceToken() async {
    String? token = await messaging.getToken();

    return token!;
  }

  Future<void> login(
    double height, {
    required BuildContext context,
    String? email,
    String? password,
  }) async {
    try {
      if (email != null &&
          email.isNotEmpty &&
          password != null &&
          password.isNotEmpty) {
        loadingLogin = true;
        String? token = await NotificationServices().getDeviceToken();
        log(token.toString());
        LoginModel? loginModel = await CreatorApiServices.loginDetails(context,
            email: email, password: password, deviceid: token);
        notifyListeners();
        if (loginModel != null && loginModel.code == 100) {
          bool userName =
              await SharedPref.setUserName(loginModel.data!.name.toString());
          bool userEmailId = await SharedPref.setUserEmailId(
              loginModel.data!.email.toString());

          bool userLoginId =
              await SharedPref.setUserLoginId(loginModel.data!.id.toString());
          await SharedPref.setIsLoggedIn(true);
          emailController.clear();
          passwordController.clear();
          loadingLogin = false;

          if (userLoginId && userName && userEmailId) {
            Navigator.pushAndRemoveUntil(context, MaterialPageRoute(
              builder: (context) {
                return AllScreen();
              },
            ), (route) => false);
            // Navigator.push(context, MaterialPageRoute(
            //   builder: (context) {
            //     return AllScreen();
            //   },
            // ));
          }

          notifyListeners();
        } else if (loginModel != null && loginModel.code == 500) {
          emailController.clear();
          passwordController.clear();
          loadingLogin = false;
          Utils.commonSnackBar(context,
              height: height, message: loginModel.msg.toString());
          notifyListeners();
        } else {
          loadingLogin = false;
          Utils.commonSnackBar(context,
              height: height, message: "Something Went Wrong");
          notifyListeners();
        }
      } else {
        Utils.commonSnackBar(context,
            height: height, message: "Enter all details");
        notifyListeners();
      }
    } catch (e) {
      log("login error: ${e.toString()}");
    }
    notifyListeners();
  }
}
