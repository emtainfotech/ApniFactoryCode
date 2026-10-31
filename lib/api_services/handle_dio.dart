import 'dart:developer';
import 'dart:io';

import 'package:apni_factory/screens/network_screen.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

class HandleDio {
  static Future handleDioError(
      {required DioException error, required BuildContext context}) async {
    if (error.error is SocketException) {
      Navigator.pushAndRemoveUntil(context, MaterialPageRoute(builder: (context) {
        return NetworkErrorScreen();
      },), (route) => false);

    } else {
      log("Error : ${error.toString()}");
    }
    return null;
  }
}