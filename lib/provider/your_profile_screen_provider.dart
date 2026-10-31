import 'dart:developer';
import 'dart:io';
import 'package:apni_factory/api_services/creator_api_services.dart';
import 'package:apni_factory/model/get_profile_model.dart';
import 'package:apni_factory/model/gst_verification_model.dart';
import 'package:apni_factory/model/update_profile_model.dart';
import 'package:apni_factory/utils.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

class YourProfileScreenProvider extends ChangeNotifier {
  bool loadingGetProfile = true;
  GetProfileModel? getProfileModel;
  TextEditingController emailController = TextEditingController();
  TextEditingController nameController = TextEditingController();
  TextEditingController mobileController = TextEditingController();
  TextEditingController locationController = TextEditingController();
  TextEditingController whatsappnoController = TextEditingController();
  TextEditingController gstorpanController = TextEditingController();

  bool isYourProfileEnable = false;
  XFile? image;
  bool isPasswordVisible = true;

  void pickCameraImage() async {
    final XFile? selectedImages =
        await ImagePicker().pickImage(source: ImageSource.camera);
    if (selectedImages != null) {
      image = selectedImages;
    }
    notifyListeners();
  }

  void pickGalleryImage() async {
    final XFile? selectedImages =
        await ImagePicker().pickImage(source: ImageSource.gallery);
    if (selectedImages != null) {
      image = selectedImages;
    }
    notifyListeners();
  }

  void enableTextField() {
    isYourProfileEnable = !isYourProfileEnable;
    notifyListeners();
  }

  void passwordVisibility() {
    isPasswordVisible = !isPasswordVisible;
    notifyListeners();
  }

  Future<void> getProfile({
    required BuildContext context,
  }) async {
    try {
      loadingGetProfile = true;
      getProfileModel = await CreatorApiServices.getProfileDetails(context);
      notifyListeners();
      if (getProfileModel != null && getProfileModel!.code == 100) {
        emailController.text = getProfileModel?.data?.email ?? "";
        nameController.text = getProfileModel?.data?.name ?? "";
        mobileController.text = getProfileModel?.data?.mobile ?? "";
        locationController.text = getProfileModel?.data?.location ?? "";
        whatsappnoController.text = getProfileModel?.data?.whatsappno ?? "";
        gstorpanController.text = getProfileModel?.data?.gstorpan ?? "";
        loadingGetProfile = false;

        notifyListeners();
      } else {
        loadingGetProfile = true;
        notifyListeners();
      }
    } catch (e) {
      log("get profile error: ${e.toString()}");
    }
    notifyListeners();
  }

  bool isValidEmail(String email) {
    final emailRegex = RegExp(
      r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$',
    );
    return emailRegex.hasMatch(email);
  }

  bool isValidMobile(String mobile) {
    final mobileRegex = RegExp(r'^[6-9]\d{9}$'); // Indian mobile
    return mobileRegex.hasMatch(mobile);
  }

  bool isValidPAN(String pan) {
    final panRegex = RegExp(r'^[A-Z]{5}[0-9]{4}[A-Z]{1}$');
    return panRegex.hasMatch(pan);
  }

  bool gstverified = false;
  bool isValidGST(String gst) {
    final gstRegex =
        RegExp(r'^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$');
    return gstRegex.hasMatch(gst);
  }

  bool loadingGstVerification = false;

  Future<void> gstVerification(double height,
      {required BuildContext context, String? gstorpan}) async {
    try {
      if (gstorpan == null || gstorpan.isEmpty) {
        Utils.commonSnackBar(context,
            height: height, message: "Enter Gst Number");
        return;
      }

      // ✅ GST / PAN validation
      if (gstorpan != null && gstorpan.isNotEmpty) {
        if (!(isValidGST(gstorpan) || isValidPAN(gstorpan))) {
          Utils.commonSnackBar(
            context,
            height: height,
            message: "Enter valid GST or PAN",
          );
          return;
        }
      }

      loadingGstVerification = true;
      notifyListeners();

      GstVerificationModel? gstVerificationModel =
          await CreatorApiServices.gstVerificationDetails(context,
              gstNumber: gstorpan);

      if (gstVerificationModel != null && gstVerificationModel.code == 100) {
        gstverified = true;
        Utils.commonSnackBar(context,
            height: height, message: gstVerificationModel.msg.toString());
      } else if (gstVerificationModel != null &&
          gstVerificationModel.code == 500) {
        Utils.commonSnackBar(context,
            height: height, message: gstVerificationModel.msg.toString());
      } else {
        Utils.commonSnackBar(context,
            height: height, message: "Something Went Wrong");
      }

      loadingGstVerification = false;
      notifyListeners();
    } catch (e) {
      log("gst verificatiopn error: ${e.toString()}");
    }
  }

  bool loadingUpdateProfile = false;

  Future<void> updateProfile(double height,
      {required BuildContext context,
      String? email,
      String? name,
      String? mobile,
      String? whatsappNo,
      String? gstorpan}) async {
    try {
      if (email == null ||
          email.isEmpty ||
          name == null ||
          name.isEmpty ||
          mobile == null ||
          mobile.isEmpty ||
          whatsappNo == null ||
          whatsappNo.isEmpty) {
        Utils.commonSnackBar(context,
            height: height, message: "Enter all details");
        return;
      }

      // ✅ Email validation
      if (!isValidEmail(email)) {
        Utils.commonSnackBar(context,
            height: height, message: "Enter valid email");
        return;
      }

      // ✅ Mobile validation
      if (!isValidMobile(mobile)) {
        Utils.commonSnackBar(context,
            height: height, message: "Enter valid mobile number");
        return;
      }

      // ✅ WhatsApp validation (same as mobile)
      if (!isValidMobile(whatsappNo)) {
        Utils.commonSnackBar(context,
            height: height, message: "Enter valid WhatsApp number");
        return;
      }

      // ✅ GST / PAN validation
      if (gstorpan != null && gstorpan.isNotEmpty) {
        if (!(isValidGST(gstorpan) || isValidPAN(gstorpan))) {
          Utils.commonSnackBar(
            context,
            height: height,
            message: "Enter valid GST or PAN",
          );
          return;
        }
      }

      loadingUpdateProfile = true;
      notifyListeners();

      UpdateProfileModel? updateProfileModel = image != null
          ? await CreatorApiServices.updateProfileDetails(context,
              name: name,
              email: email,
              mobile: mobile,
              image: File(image!.path),
              gstorpan: gstorpan ?? "",
              whatsappNo: whatsappNo)
          : await CreatorApiServices.updateProfileDetails(context,
              name: name,
              email: email,
              mobile: mobile,
              image: null,
              gstorpan: gstorpan ?? "",
              whatsappNo: whatsappNo);

      if (updateProfileModel != null && updateProfileModel.code == 100) {
        isYourProfileEnable = false;
        getProfile(context: context);
      } else if (updateProfileModel != null && updateProfileModel.code == 500) {
        Utils.commonSnackBar(context,
            height: height, message: updateProfileModel.msg.toString());
      } else {
        Utils.commonSnackBar(context,
            height: height, message: "Something Went Wrong");
      }

      loadingUpdateProfile = false;
      notifyListeners();
    } catch (e) {
      log("update profile error: ${e.toString()}");
    }
  }

  // Future<void> updateProfile(double height,
  //     {required BuildContext context,
  //     String? email,
  //     String? name,
  //     String? mobile,

  //     String? whatsappNo,
  //     String? gstorpan}) async {
  //   try {
  //     if (email != null &&
  //         email.isNotEmpty &&
  //         name != null &&
  //         name.isNotEmpty &&
  //         mobile != null &&
  //         mobile.isNotEmpty &&
  //         whatsappNo != null &&
  //         whatsappNo.isNotEmpty &&
  //         gstorpan != null &&
  //         gstorpan.isNotEmpty) {
  //       loadingUpdateProfile = true;

  //       UpdateProfileModel? updateProfileModel = image != null
  //           ? await CreatorApiServices.updateProfileDetails(context,
  //               name: name,
  //               email: email,
  //               mobile: mobile,

  //               image: File(image!.path),
  //               gstorpan: gstorpan,
  //               whatsappNo: whatsappNo)
  //           : await CreatorApiServices.updateProfileDetails(context,
  //               name: name,
  //               email: email,
  //               mobile: mobile,

  //               image: null,
  //               gstorpan: gstorpan,
  //               whatsappNo: whatsappNo);
  //       notifyListeners();

  //       if (updateProfileModel != null && updateProfileModel.code == 100) {
  //         isYourProfileEnable = false;
  //         getProfile(context: context);
  //         loadingUpdateProfile = false;

  //         notifyListeners();
  //       } else if (updateProfileModel != null &&
  //           updateProfileModel.code == 500) {
  //         loadingUpdateProfile = false;
  //         Utils.commonSnackBar(context,
  //             height: height, message: updateProfileModel.msg.toString());
  //         notifyListeners();
  //       } else {
  //         loadingUpdateProfile = false;
  //         Utils.commonSnackBar(context,
  //             height: height, message: "Something Went Wrong");
  //         notifyListeners();
  //       }
  //     } else {
  //       Utils.commonSnackBar(context,
  //           height: height, message: "Enter all details");
  //       notifyListeners();
  //     }
  //   } catch (e) {
  //     log("update profile error: ${e.toString()}");
  //   }
  //   notifyListeners();
  // }
}
