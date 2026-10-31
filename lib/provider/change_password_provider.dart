import 'dart:developer';

import 'package:apni_factory/api_services/creator_api_services.dart';
import 'package:apni_factory/model/change_password_model.dart';
import 'package:apni_factory/utils.dart';
import 'package:flutter/cupertino.dart';

class ChangePasswordProvider extends ChangeNotifier {
  TextEditingController currentPasswordController = TextEditingController();
  TextEditingController newPasswordController = TextEditingController();
  bool loadingChangePassword = false;
  Future<void> changePassword(double height,
      {required BuildContext context,
      String? currentPassword,
      String? newPassword}) async {
    try {
      if (currentPassword != null &&
          currentPassword.isNotEmpty &&
          newPassword != null &&
          newPassword.isNotEmpty) {
        loadingChangePassword = true;
        ChangePasswordModel? changePasswordModel =
            await CreatorApiServices.changePasswordDetails(context,
                currentPassword: currentPassword, newPassword: newPassword);
        notifyListeners();
        if (changePasswordModel != null && changePasswordModel.code == 100) {
          currentPasswordController.clear();
          newPasswordController.clear();
          loadingChangePassword = false;
          Utils.commonSnackBar(context,
              height: height, message: changePasswordModel.msg.toString());
          notifyListeners();
        } else if (changePasswordModel != null &&
            changePasswordModel.code == 500) {
          currentPasswordController.clear();
          newPasswordController.clear();
          loadingChangePassword = false;
          Utils.commonSnackBar(context,
              height: height, message: changePasswordModel.msg.toString());
          notifyListeners();
        } else {
          loadingChangePassword = false;
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
      log("change password error: ${e.toString()}");
    }
    notifyListeners();
  }
}
