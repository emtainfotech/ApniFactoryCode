import 'dart:convert';
import 'dart:developer';
import 'dart:io';
import 'package:apni_factory/api_services/handle_dio.dart';

import 'package:apni_factory/model/about_us_model.dart';
import 'package:apni_factory/model/add_address_model.dart';
import 'package:apni_factory/model/add_to_cart_model.dart';
import 'package:apni_factory/model/add_to_wishlist_model.dart';
import 'package:apni_factory/model/admin_app_pages_model.dart';
import 'package:apni_factory/model/apply_coupon_model.dart';
import 'package:apni_factory/model/brand_detail_model.dart';
import 'package:apni_factory/model/brand_screen_model.dart';
import 'package:apni_factory/model/category_screen_model.dart';
import 'package:apni_factory/model/change_password_model.dart';
import 'package:apni_factory/model/check_availability_model.dart';
import 'package:apni_factory/model/city_list_model.dart';
import 'package:apni_factory/model/company_screen_model.dart';
import 'package:apni_factory/model/delete_address_model.dart';
import 'package:apni_factory/model/delete_cart_attribute_model.dart';
import 'package:apni_factory/model/delete_cart_model.dart';
import 'package:apni_factory/model/delete_wishlist_model.dart';
import 'package:apni_factory/model/empty_cart_model.dart';
import 'package:apni_factory/model/gst_error_verification_model.dart';
import 'package:apni_factory/model/gst_verification_model.dart';
import 'package:apni_factory/model/help_model.dart';
import 'package:apni_factory/model/help_no_for_app_model.dart';
import 'package:apni_factory/model/notification_model.dart';
import 'package:apni_factory/model/order_histort_model.dart';
import 'package:apni_factory/model/order_model.dart';
import 'package:apni_factory/model/pincode_list_model.dart';
import 'package:apni_factory/model/privacy_policy_model.dart';
import 'package:apni_factory/model/product_review_model.dart';
import 'package:apni_factory/model/related_product_model.dart';
import 'package:apni_factory/model/return_policy_model.dart';
import 'package:apni_factory/model/search_model.dart';
import 'package:apni_factory/model/single_order_detail_model.dart';
import 'package:apni_factory/model/state_model.dart';
import 'package:apni_factory/model/terms_condition_model.dart';
import 'package:apni_factory/model/update_address_model.dart';
import 'package:apni_factory/model/get_address_list_model.dart';
import 'package:apni_factory/model/get_coupon_list_model.dart';
import 'package:apni_factory/model/get_faq_model.dart';
import 'package:apni_factory/model/get_feedback_model.dart';
import 'package:apni_factory/model/get_address_detail_model.dart';
import 'package:apni_factory/model/get_otp_model.dart';
import 'package:apni_factory/model/get_pages_model.dart';
import 'package:apni_factory/model/get_profile_model.dart';
import 'package:apni_factory/model/home_screen_model.dart';
import 'package:apni_factory/model/increase_cart_quantity_model.dart';
import 'package:apni_factory/model/post_feedback_model.dart';
import 'package:apni_factory/model/product_category_list_model.dart';
import 'package:apni_factory/model/product_detail_model.dart';
import 'package:apni_factory/model/product_list_by_category_model.dart';
import 'package:apni_factory/model/reset_password_model.dart';
import 'package:apni_factory/model/sign_up_model.dart';
import 'package:apni_factory/model/update_cart_address_model.dart';
import 'package:apni_factory/model/update_profile_model.dart';
import 'package:apni_factory/model/user_cart_model.dart';
import 'package:apni_factory/model/user_wishlist_model.dart';
import 'package:apni_factory/model/verify_otp_model.dart';
import 'package:apni_factory/shared_pref.dart';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../model/login_model.dart';
import 'api_end_point.dart';

class CreatorApiServices {
  static Future<LoginModel?> loginDetails(BuildContext context,
      {required String email, String? password, String? deviceid}) async {
    Dio dio = Dio();
    FormData formData;

    formData = FormData.fromMap(
        {"email": email, "password": password, "deviceid": deviceid});
    try {
      Response response = await dio.post(ApiEndPoint.loginApi, data: formData);

      log("login response is ${response.data.toString()}");

      if (response.statusCode == 200) {
        LoginModel loginModel = LoginModel.fromJson(response.data);

        return loginModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<SignUpModel?> signUpDetails(BuildContext context,
      {required String name,
      required String email,
      required mobileNo,
      required password,
      required String regby}) async {
    Dio dio = Dio();

    FormData formData = FormData.fromMap({
      "email": email,
      "name": name,
      "regby": regby,
      "password": password,
      "mobile": mobileNo,
    });

    // Map<String, dynamic>  formData;
    //   if (regby == "app") {
    //    formData = {
    //       "email": email,
    //       "password": password,
    //       "name": name,
    //       "regby": regby,
    //       "mobile": mobileNo,
    //       "whatsappno": whatsappNumber,

    //     };
    //   } else {
    //     formData = {
    //       "email": email,
    //       "password": null,
    //       "name": name,
    //       "regby": regby,
    //       "mobile": mobileNo
    //     };
    //   }
    try {
      Response response = await dio.post(
        ApiEndPoint.signUpApi,
        data: formData,
      );

      log("Sign Up response is ${response.data.toString()}");

      // var data = json.decode(response.data);

      if (response.statusCode == 200) {
        SignUpModel signUpModel = SignUpModel.fromJson(response.data);

        return signUpModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  // static Future<dynamic> gstVerificationDetails(
  //   BuildContext context, {
  //   required String gstNumber,
  // }) async {
  //   Dio dio = Dio();
  //   const String apiKey =
  //       'fz3sSQg528hXFIOGkHLEEUrhjnS2'; // Replace with your actual key
  //   final String url =
  //       'https://appyflow.in/api/verifyGST?gstNo=$gstNumber&key_secret=$apiKey';

  //   try {
  //     Response response = await dio.post(
  //       url,
  //     );
  //     log("GST Verification response: ${response.data}");

  //     // var data = json.decode(response.data);

  //     if (response.statusCode == 200) {
  //       final data = response.data;
  //       if (data['error'] == true) {
  //         // Parse error model
  //         GstErrorResponseModel errorModel =
  //             GstErrorResponseModel.fromJson(data);
  //         log(errorModel.message.toString());
  //         return errorModel;
  //       } else {
  //         // Parse success model
  //         GstResponseModel gstModel = GstResponseModel.fromJson(data);
  //         log(gstModel.taxpayerInfo?.gstin ?? "");
  //         return gstModel;
  //       }
  //     } else {
  //       return null;
  //     }
  //   } on DioException catch (error) {
  //     return await HandleDio.handleDioError(error: error, context: context);
  //   }
  // }

  static Future<ChangePasswordModel?> changePasswordDetails(
      BuildContext context,
      {required String currentPassword,
      required String newPassword}) async {
    Dio dio = Dio();
    FormData formData;
    formData = FormData.fromMap({
      "currentpassword": currentPassword,
      "newpassword": newPassword,
    });
    String userId = await SharedPref.getUserLoginId();
    try {
      Response response = await dio.post(ApiEndPoint.changePasswordApi + userId,
          data: formData);

      log("change password response is ${response.data.toString()}");
      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        ChangePasswordModel changePasswordModel =
            ChangePasswordModel.fromJson(data);

        return changePasswordModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<GetOtpModel?> getOtpDetails(BuildContext context,
      {required String email, required String sendon}) async {
    Dio dio = Dio();

    FormData formData =
        FormData.fromMap({"emailmobile": email, "sendon": sendon});
    try {
      Response response = await dio.post(ApiEndPoint.getOtpApi, data: formData);

      log("Get Otp response is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        GetOtpModel getOtpModel = GetOtpModel.fromJson(data);
        log(getOtpModel.toString());
        return getOtpModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<VerifyOtpModel?> verifyOtpDetails(BuildContext context,
      {required String email, required String otp}) async {
    Dio dio = Dio();

    FormData formData =
        FormData.fromMap({"emailmobile": email, "otp": int.parse(otp)});
    try {
      Response response =
          await dio.post(ApiEndPoint.verifyOtpApi, data: formData);

      log("Verify Otp response is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        VerifyOtpModel verifyOtpModel = VerifyOtpModel.fromJson(data);
        log(verifyOtpModel.toString());
        return verifyOtpModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<ResetPasswordModel?> resetPasswordDetails(BuildContext context,
      {required String id,
      required String password,
      required String emailmobile}) async {
    Dio dio = Dio();

    FormData formData = FormData.fromMap(
        {"id": id, "password": password, "emailmobile": emailmobile});
    try {
      Response response =
          await dio.post(ApiEndPoint.resetPasswordApi, data: formData);

      log("Reset Password response is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        ResetPasswordModel resetPasswordModel =
            ResetPasswordModel.fromJson(data);
        log(resetPasswordModel.toString());
        return resetPasswordModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<HomescreenModel?> homeScreenDetails(
    BuildContext context,
  ) async {
    Dio dio = Dio();
    String userId = await SharedPref.getUserLoginId();
    try {
      Response response = await dio.get(
        "${ApiEndPoint.homeScreenApi}?userid=${userId}",
      );

      log("HomeScreen response is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        HomescreenModel homeScreenModel = HomescreenModel.fromJson(data);
        log(homeScreenModel.toString());
        return homeScreenModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<SearchModel?> searchDetails(
    BuildContext context, {
    required String word,
  }) async {
    Dio dio = Dio();

    FormData formData = FormData.fromMap({
      "word": word,
    });

    try {
      Response response = await dio.post(ApiEndPoint.searchApi, data: formData);

      log("Search response is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        SearchModel searchModel = SearchModel.fromJson(data);

        log(searchModel.toString());
        return searchModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<GetProfileModel?> getProfileDetails(
    BuildContext context,
  ) async {
    Dio dio = Dio();
    String userLoginId = await SharedPref.getUserLoginId();

    try {
      Response response = await dio.get(
        ApiEndPoint.getProfileApi + "${int.parse(userLoginId)}",
      );

      log("Get Profile response is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        GetProfileModel getProfileModel = GetProfileModel.fromJson(data);
        log(getProfileModel.toString());
        return getProfileModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<UpdateProfileModel?> updateProfileDetails(
    BuildContext context, {
    required String name,
    required String email,
    required String mobile,
    // String? location,
    required String whatsappNo,
    required String gstorpan,
    File? image,
  }) async {
    Dio dio = Dio();
    String userLoginId = await SharedPref.getUserLoginId();
    String imageFileName = image?.path.split('/').last ?? "";
    FormData formData;
    if (image != null) {
      formData = FormData.fromMap({
        "email": email,
        "name": name,
        "mobile": mobile,
        "location": "Na",
        "image":
            await MultipartFile.fromFile(image.path, filename: imageFileName),
        "whatsappno": whatsappNo,
        "gstorpan": gstorpan
      });
    } else {
      formData = FormData.fromMap({
        "email": email,
        "name": name,
        "mobile": mobile,
        "location": "Na",
        "image": null,
        "whatsappno": whatsappNo,
        "gstorpan": gstorpan
      });
    }

    try {
      Response response = await dio.post(
          ApiEndPoint.updateProfileApi + "${int.parse(userLoginId)}",
          data: formData);

      log("Update Profile response is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        UpdateProfileModel updateProfileModel =
            UpdateProfileModel.fromJson(data);
        log(updateProfileModel.toString());
        return updateProfileModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<GetFeedbackListModel?> getFeedbackListDetails(
    BuildContext context,
  ) async {
    Dio dio = Dio();
    String userLoginId = await SharedPref.getUserLoginId();
    // FormData formData = FormData.fromMap({
    //   "userid": userLoginId,
    // });
    log(ApiEndPoint.getFeedbackListApi + userLoginId);
    try {
      Response response = await dio.get(
        ApiEndPoint.getFeedbackListApi + userLoginId,
      );

      log("Get Feedback response is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        GetFeedbackListModel getFeedbackListModel =
            GetFeedbackListModel.fromJson(data);
        log(getFeedbackListModel.toString());
        return getFeedbackListModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<PostFeedbackModel?> postFeedbackDetails(BuildContext context,
      {required String feedback}) async {
    Dio dio = Dio();
    String id = await SharedPref.getUserLoginId();
    String name = await SharedPref.getUserName();
    String email = await SharedPref.getUserEmailId();
    FormData formData = FormData.fromMap(
        {"userid": id, "name": name, "email": email, "feedback": feedback});
    try {
      Response response =
          await dio.post(ApiEndPoint.postFeedbackApi, data: formData);

      log("Post feedback response is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        PostFeedbackModel postFeedbackModel = PostFeedbackModel.fromJson(data);
        log(postFeedbackModel.toString());
        return postFeedbackModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<GetPagesModel?> getPagesDetails(BuildContext context,
      {required String slug}) async {
    Dio dio = Dio();

    try {
      Response response = await dio.get(
        ApiEndPoint.getPagesApi + "?name=${slug}",
      );

      log("Get Page response is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        GetPagesModel getPagesModel = GetPagesModel.fromJson(data);
        log(getPagesModel.toString());
        return getPagesModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<HelpNoForAppModel?> helpNoForAppDetails(
    BuildContext context,
  ) async {
    Dio dio = Dio();

    try {
      Response response = await dio.get(
        ApiEndPoint.helpNoForAppApi,
      );

      log("Help No For App response is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        HelpNoForAppModel helpNoForAppModel = HelpNoForAppModel.fromJson(data);
        log(helpNoForAppModel.toString());
        return helpNoForAppModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<CategoryScreenModel?> categoryScreenDetails(
    BuildContext context, {
    required int mid,
  }) async {
    log(mid.toString());
    Dio dio = Dio();
    // FormData formData = FormData.fromMap({
    //   "mid": mid,
    // });
    try {
      log(ApiEndPoint.categoryScreenApi + "mid=${mid}");
      Response response = await dio.get(
        ApiEndPoint.categoryScreenApi + "mid=${mid}",
        // data: formData
      );

      log("Category response is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        CategoryScreenModel categoryScreenModel =
            CategoryScreenModel.fromJson(data);
        log(categoryScreenModel.toString());
        return categoryScreenModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<CompanyScreenModel?> companyScreenDetails(
    BuildContext context, {
    required int cid,
  }) async {
    log(cid.toString());
    Dio dio = Dio();
    FormData formData = FormData.fromMap({
      "cid": cid,
    });

    try {
      Response response = await dio.get(
        ApiEndPoint.companyScreenApi + "cid=${cid}",
      );

      log("Company response is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        CompanyScreenModel comapnyScreenModel =
            CompanyScreenModel.fromJson(data);
        log(comapnyScreenModel.toString());
        return comapnyScreenModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<BrandScreenModel?> brandScreenDetails(
    BuildContext context, {
    required int cmpid,
    required int cid,
  }) async {
    log(cmpid.toString());
    log(cid.toString());
    Dio dio = Dio();
    FormData formData = FormData.fromMap({"cmpid": cmpid, "category_id": cid});
    try {
      Response response = await dio.get(
        ApiEndPoint.brandScreenApi + "cmpid=${cmpid}&cid=${cid}",
      );

      log("Brand response is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        BrandScreenModel brandScreenModel = BrandScreenModel.fromJson(data);
        log(brandScreenModel.toString());
        return brandScreenModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<ProductCategoryListScreenModel?>
      productCategoryListScreenDetails(BuildContext context,
          {required int bid, required int cid}) async {
    log(bid.toString());
    log(cid.toString());
    Dio dio = Dio();
    FormData formData = FormData.fromMap({"bid": bid, "cid": cid});
    try {
      log(ApiEndPoint.productCategoryListScreenApi + "bid=${bid}&cid=${cid}");
      Response response = await dio.get(
        ApiEndPoint.productCategoryListScreenApi + "bid=${bid}&cid=${cid}",
      );

      log("Product Category List response is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        ProductCategoryListScreenModel productCategoryListScreenModel =
            ProductCategoryListScreenModel.fromJson(data);
        log(productCategoryListScreenModel.toString());
        return productCategoryListScreenModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<ProductListByCategoryScreenModel?>
      productListByCategoryScreenDetails(
    BuildContext context, {
    required int sid,
    required int bid,
  }) async {
    Dio dio = Dio();
    FormData formData = FormData.fromMap({"bid": bid, "sid": sid});

    try {
      log("sid is ${sid}");
      log("bid is ${bid}");
      Response response = await dio.get(
        ApiEndPoint.productListBySubCategoryScreenApi + "sid=${sid}&bid=${bid}",
      );

      log("Product List By Subcategory response is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        ProductListByCategoryScreenModel productListBySubCategoryScreenModel =
            ProductListByCategoryScreenModel.fromJson(data);
        log(productListBySubCategoryScreenModel.toString());
        return productListBySubCategoryScreenModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<ProductDetailModel?> producDetailsDetails(
    BuildContext context, {
    required String productid,
    required String colorid,
  }) async {
    String userid = await SharedPref.getUserLoginId();
    Dio dio = Dio();
    FormData formData = FormData.fromMap(
        {"productid": productid, "colorid": colorid, "userid": userid});
    try {
      Response response =
          await dio.post(ApiEndPoint.productDetailApi, data: formData);

      log("Product Detail Screen response is ${response.data.toString()}");

      // var data = json.decode(response.data);

      if (response.statusCode == 200) {
        ProductDetailModel productDetailModel =
            ProductDetailModel.fromJson(response.data);
        log(productDetailModel.toString());
        return productDetailModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<ProductReviewModel?> producReviewDetails(
    BuildContext context, {
    required String productid,
    required String rating,
    required String review,
  }) async {
    String userid = await SharedPref.getUserLoginId();
    Dio dio = Dio();
    FormData formData = FormData.fromMap({
      "productid": productid,
      "userid": userid,
      "rating": rating,
      "review": review
    });
    try {
      Response response =
          await dio.post(ApiEndPoint.productReviewApi, data: formData);

      log("Product Review Screen response is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        ProductReviewModel productReviewModel =
            ProductReviewModel.fromJson(data);
        log(productReviewModel.toString());
        return productReviewModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<UserWishlistModel?> userWishlistDetails(
    BuildContext context,
  ) async {
    Dio dio = Dio();
    String userid = await SharedPref.getUserLoginId();

    FormData formData = FormData.fromMap({
      "userid": userid,
    });

    try {
      Response response =
          await dio.post(ApiEndPoint.userWishlistApi, data: formData);

      log("User Wishlist response is ${response.data.toString()}");

      // var data = json.decode(response.data);

      if (response.statusCode == 200) {
        UserWishlistModel userWishlistModel =
            UserWishlistModel.fromJson(response.data);

        log(userWishlistModel.toString());
        return userWishlistModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<DeleteWishlistModel?> deleteWishlistDetails(
      BuildContext context,
      {required String wishlistid}) async {
    Dio dio = Dio();
    String userid = await SharedPref.getUserLoginId();
    FormData formData = FormData.fromMap({
      "wishlistid": wishlistid,
      "userid": userid,
    });
    try {
      Response response =
          await dio.post(ApiEndPoint.deleteWishlistApi, data: formData);

      log("Delete Wishlist response is ${response.data.toString()}");

      // var data = json.decode(response.data);

      if (response.statusCode == 200) {
        DeleteWishlistModel deleteWishlistModel =
            DeleteWishlistModel.fromJson(response.data);
        log(deleteWishlistModel.toString());
        return deleteWishlistModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<AddToWishlistModel?> addToWishlistDetails(BuildContext context,
      {required String productid, required String color}) async {
    Dio dio = Dio();
    String userid = await SharedPref.getUserLoginId();
    FormData formData = FormData.fromMap(
        {"productid": productid, "userid": userid, "color": color});

    try {
      Response response =
          await dio.post(ApiEndPoint.addToWishlistApi, data: formData);

      log("Add To Wishlist response is ${response.data.toString()}");

      // var data = json.decode(response.data);

      if (response.statusCode == 200) {
        AddToWishlistModel addToWishlistModel =
            AddToWishlistModel.fromJson(response.data);
        log(addToWishlistModel.toString());
        return addToWishlistModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<UserCartModel?> userCartDetails(
    BuildContext context,
  ) async {
    Dio dio = Dio();
    String userid = await SharedPref.getUserLoginId();

    FormData formData = FormData.fromMap({
      "userid": userid,
    });
    log(userid.toString());
    try {
      Response response =
          await dio.post(ApiEndPoint.userCartApi, data: formData);

      log("User Cart response is ${response.data.toString()}");

      if (response.statusCode == 200) {
        UserCartModel userCartModel = UserCartModel.fromJson(response.data);

        log(userCartModel.toString());
        return userCartModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<DeleteCartModel?> deleteCartDetails(BuildContext context,
      {required String cartid}) async {
    Dio dio = Dio();

    FormData formData = FormData.fromMap({
      "cartid": cartid,
    });
    try {
      Response response =
          await dio.post(ApiEndPoint.deleteCartApi, data: formData);

      log("Delete Cart response is ${response.data.toString()}");

      // var data = json.decode(response.data);

      if (response.statusCode == 200) {
        DeleteCartModel deleteCartModel =
            DeleteCartModel.fromJson(response.data);
        log(deleteCartModel.toString());
        return deleteCartModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<DeleteCartAttributeModel?> deleteCartAttributeDetails(
      BuildContext context,
      {required String cartattributeid}) async {
    Dio dio = Dio();

    FormData formData = FormData.fromMap({
      "cartattributeid": cartattributeid,
    });
    try {
      Response response =
          await dio.post(ApiEndPoint.deleteCartAttributeApi, data: formData);

      log("Delete Cart Attribute response is ${response.data.toString()}");

      // var data = json.decode(response.data);

      if (response.statusCode == 200) {
        DeleteCartAttributeModel deleteCartAttributeModel =
            DeleteCartAttributeModel.fromJson(response.data);
        log(deleteCartAttributeModel.toString());
        return deleteCartAttributeModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<EmptyCartModel?> emptyCartDetails(
    BuildContext context,
  ) async {
    Dio dio = Dio();
    String customerid = await SharedPref.getUserLoginId();
    FormData formData = FormData.fromMap({
      "customer_id": customerid,
    });
    try {
      Response response =
          await dio.post(ApiEndPoint.emptyCartApi, data: formData);

      log("Empty Cart response is ${response.data.toString()}");

      // var data = json.decode(response.data);

      if (response.statusCode == 200) {
        EmptyCartModel emptyCartModel = EmptyCartModel.fromJson(response.data);
        log(emptyCartModel.toString());
        return emptyCartModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<AddToCartModel?> addToCartDetails(BuildContext context,
      {required String productid,
      required String brandid,
      required List attributes}) async {
    Dio dio = Dio();
    String userid = await SharedPref.getUserLoginId();
    final Map<String, dynamic> body = {
      "productid": productid,
      "userid": userid,
      "brandid": brandid,
      "attributes": attributes
    };

    // FormData formData = FormData.fromMap(
    //     {"productid": productid, "userid": userid, "brandid": qty});
    try {
      Response response = await dio.post(ApiEndPoint.addToCartApi, data: body);

      log("Add To Cart response is ${response.data.toString()}");

      // var data = json.decode(response.data);

      if (response.statusCode == 200) {
        AddToCartModel addToCartModel = AddToCartModel.fromJson(response.data);
        log(addToCartModel.toString());
        return addToCartModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<IncreaseCartQtyModel?> increaseCartQuantityDetails(
      BuildContext context,
      {required String cartattributeid,
      required String qty}) async {
    Dio dio = Dio();

    FormData formData =
        FormData.fromMap({"cartattributeid": cartattributeid, "qty": qty});
    try {
      Response response =
          await dio.post(ApiEndPoint.increaseCartQuantityApi, data: formData);

      log("Increase Cart Quantity response is ${response.data.toString()}");

      // var data = json.decode(response.data);

      if (response.statusCode == 200) {
        IncreaseCartQtyModel increaseCartQtyModel =
            IncreaseCartQtyModel.fromJson(response.data);
        log(increaseCartQtyModel.toString());
        return increaseCartQtyModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<GetFaqModel?> getFaqDetails(
    BuildContext context,
  ) async {
    Dio dio = Dio();

    try {
      Response response = await dio.get(
        ApiEndPoint.getFaqApi,
      );

      log("Get Faq response is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        GetFaqModel getFaqModel = GetFaqModel.fromJson(data);
        log(getFaqModel.toString());
        return getFaqModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<GetCouponListModel?> getCouponListDetails(BuildContext context,
      {required String couponof}) async {
    Dio dio = Dio();
    String userid = await SharedPref.getUserLoginId();
    FormData formData =
        FormData.fromMap({"userid": userid, "couponof": couponof});
    try {
      Response response =
          await dio.post(ApiEndPoint.getCouponListApi, data: formData);

      log("Get Coupon List response is ${response.data.toString()}");

      // var data = json.decode(response.data);

      if (response.statusCode == 200) {
        GetCouponListModel getCouponListModel =
            GetCouponListModel.fromJson(response.data);
        log(getCouponListModel.toString());
        return getCouponListModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<ApplyCouponModel?> applyCouponDetails(
    BuildContext context, {
    required String couponid,
  }) async {
    Dio dio = Dio();
    String userid = await SharedPref.getUserLoginId();
    FormData formData =
        FormData.fromMap({"userid": userid, "couponid": couponid});
    try {
      Response response =
          await dio.post(ApiEndPoint.applyCouponApi, data: formData);

      log("Apply Coupon response is ${response.data.toString()}");

      // var data = json.decode(response.data);

      if (response.statusCode == 200) {
        ApplyCouponModel applyCouponModel =
            ApplyCouponModel.fromJson(response.data);
        log(applyCouponModel.toString());
        return applyCouponModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<GetAddressListModel?> getAddressListDetails(
    BuildContext context,
  ) async {
    Dio dio = Dio();
    String customerId = await SharedPref.getUserLoginId();
    FormData formData = FormData.fromMap({
      "customer_id": customerId,
    });
    try {
      log(customerId);
      Response response =
          await dio.post(ApiEndPoint.getAddressListApi, data: formData);

      log("Get Address List response is ${response.data.toString()}");

      // var data = json.decode(response.data);

      if (response.statusCode == 200) {
        GetAddressListModel getAddressListModel =
            GetAddressListModel.fromJson(response.data);

        return getAddressListModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<AddAddressModel?> addAddressDetails(BuildContext context,
      {required String name,
      required String landmark1,
      required String landmark2,
      required String type,
      required String country,
      required String state,
      required String city,
      required String pincode,
      required String phoneno,
      required String identityname}) async {
    Dio dio = Dio();
    String customerId = await SharedPref.getUserLoginId();
    FormData formData = FormData.fromMap({
      "customer_id": customerId,
      "name": name,
      "landmark1": landmark1,
      "landmark2": landmark2,
      "country": country,
      "state": state,
      "city": city,
      "pincode": pincode,
      "phoneno": phoneno,
      "location": "NA",
      "identityname": identityname,
      "type": type
    });
    try {
      Response response =
          await dio.post(ApiEndPoint.addAddressApi, data: formData);

      log("Add Address response is ${response.data.toString()}");

      // var data = json.decode(response.data);

      if (response.statusCode == 200) {
        AddAddressModel addAddressModel =
            AddAddressModel.fromJson(response.data);

        return addAddressModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<UpdateAddressModel?> updateAddressDetails(BuildContext context,
      {required String name,
      required String landmark,
      required String address,
      required String country,
      required String state,
      required String city,
      required String pincode,
      required String phoneno,
      required String identityname,
      required String addressId,
      required String type}) async {
    Dio dio = Dio();
    String customerId = await SharedPref.getUserLoginId();
    FormData formData = FormData.fromMap({
      "customer_id": customerId,
      "name": name,
      "landmark1": address,
      "landmark2": landmark,
      "country": country,
      "state": state,
      "city": city,
      "pincode": pincode,
      "phoneno": phoneno,
      "location": "NA",
      "identityname": identityname,
      "type": type
    });
    try {
      Response response = await dio
          .post(ApiEndPoint.updateAddressApi + addressId, data: formData);

      log("Update Address response is ${response.data.toString()}");

      // var data = json.decode(response.data);

      if (response.statusCode == 200) {
        UpdateAddressModel updateAddressModel =
            UpdateAddressModel.fromJson(response.data);

        return updateAddressModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<GetAddressDetailModel?> getIdAddressDetails(
      BuildContext context,
      {required String addressId}) async {
    Dio dio = Dio();

    try {
      Response response =
          await dio.get(ApiEndPoint.getAddressDetailApi + addressId);

      log("Get Id Address response is ${response.data.toString()}");

      // var data = json.decode(response.data);

      if (response.statusCode == 200) {
        GetAddressDetailModel getAddressDetailModel =
            GetAddressDetailModel.fromJson(response.data);

        return getAddressDetailModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<DeleteAddressModel?> deleteAddressDetails(BuildContext context,
      {required String addressid}) async {
    Dio dio = Dio();

    try {
      Response response =
          await dio.delete(ApiEndPoint.deleteAddressApi + addressid);

      log("Delete Address response is ${response.data.toString()}");

      // var data = json.decode(response.data);

      if (response.statusCode == 200) {
        DeleteAddressModel deleteAddressModel =
            DeleteAddressModel.fromJson(response.data);

        return deleteAddressModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<UpdateCartAddressModel?> updateCartAddressDetail(
    BuildContext context, {
    required String addressid,
  }) async {
    Dio dio = Dio();
    String customerId = await SharedPref.getUserLoginId();
    FormData formData = FormData.fromMap({
      "userid": customerId,
      "addressid": addressid,
    });
    try {
      Response response =
          await dio.post(ApiEndPoint.updateCartAddressApi, data: formData);

      log("Update Cart Address response is ${response.data.toString()}");

      // var data = json.decode(response.data);

      if (response.statusCode == 200) {
        UpdateCartAddressModel updateCartAddressModel =
            UpdateCartAddressModel.fromJson(response.data);

        return updateCartAddressModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<StateModel?> stateModelDetails(
    BuildContext context,
  ) async {
    Dio dio = Dio();
    // String customerId = await SharedPref.getUserLoginId();
    // FormData formData = FormData.fromMap({
    //   "customer_id": customerId,
    // });
    try {
      Response response = await dio.get(
        "${ApiEndPoint.stateCityPincodeApi}?listof=state&name=",
      );

      log("Get State List is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        StateModel stateModel = StateModel.fromJson(data);

        return stateModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<CityListModel?> cityModelDetails(BuildContext context,
      {required String state}) async {
    Dio dio = Dio();

    try {
      Response response = await dio.get(
        "${ApiEndPoint.stateCityPincodeApi}?listof=city&name=${state}",
      );

      log("Get City List is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        CityListModel cityListModel = CityListModel.fromJson(data);

        return cityListModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<PincodeListModel?> pincodeModelDetails(BuildContext context,
      {required String city}) async {
    Dio dio = Dio();

    try {
      Response response = await dio.get(
        "${ApiEndPoint.stateCityPincodeApi}?listof=pincode&name=${city}",
      );

      log("Get Pincode List is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        PincodeListModel pincodeListModel = PincodeListModel.fromJson(data);

        return pincodeListModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<CheckAddressAvailabilityModel?> checkAddressAvailabilityDetails(
      {required BuildContext context, required String addressid}) async {
    Dio dio = Dio();
    String userId = await SharedPref.getUserLoginId();
    FormData formData = FormData.fromMap({
      "userid": userId,
      "addressid": addressid,
    });
    try {
      Response response =
          await dio.post("${ApiEndPoint.checkAvailabilityApi}", data: formData);

      log("Check Address Availability is ${response.data.toString()}");

      // var data = json.decode(response.data);

      if (response.statusCode == 200) {
        CheckAddressAvailabilityModel checkAddressAvailabilityModel =
            CheckAddressAvailabilityModel.fromJson(response.data);

        return checkAddressAvailabilityModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<OrderModel?> orderDetails(
    BuildContext context, {
    required String addressid,
  }) async {
    Dio dio = Dio();
    String userId = await SharedPref.getUserLoginId();
    FormData formData = FormData.fromMap({
      "userid": userId,
      "addressid": addressid,
    });
    try {
      Response response = await dio.post(ApiEndPoint.orderApi, data: formData);

      log("Order response is ${response.data.toString()}");

      // var data = json.decode(response.data);

      if (response.statusCode == 200) {
        OrderModel orderModel = OrderModel.fromJson(response.data);

        return orderModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<SingleOrderDetailModel?> singleOrderDetail(
    BuildContext context, {
    required String orderno,
  }) async {
    Dio dio = Dio();
log("message");
    FormData formData = FormData.fromMap({
      "orderno": orderno,
    });
    log(orderno);
    try {
      Response response =
          await dio.post(ApiEndPoint.singleOrderDetailApi, data: formData);

      log("Single Order Detail response is ${response.data.toString()}");

      // var data = json.decode(response.data);

      if (response.statusCode == 200) {
        SingleOrderDetailModel singleOrderDetailModel =
            SingleOrderDetailModel.fromJson(response.data);

        return singleOrderDetailModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<BrandDetailModel?> brandDetail(
    BuildContext context, {
    required String brandid,
  }) async {
    Dio dio = Dio();

    FormData formData = FormData.fromMap({
      "brandid": brandid,
    });
    try {
      Response response =
          await dio.post(ApiEndPoint.brandDetailApi, data: formData);

      log("Brand Detail response is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        BrandDetailModel brandDetailModel = BrandDetailModel.fromJson(data);

        return brandDetailModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<OrderHistoryModel?> orderHistoryDetails(
    BuildContext context,
  ) async {
    Dio dio = Dio();
    String userId = await SharedPref.getUserLoginId();
    FormData formData = FormData.fromMap({
      "userid": userId,
    });
    try {
      Response response =
          await dio.post(ApiEndPoint.orderHistoryApi, data: formData);

      log("Order History response is ${response.data.toString()}");

      // var data = json.decode(response.data);

      if (response.statusCode == 200) {
        OrderHistoryModel orderHistoryModel =
            OrderHistoryModel.fromJson(response.data);

        return orderHistoryModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<NotificationModel?> notificationDetails(
    BuildContext context,
  ) async {
    Dio dio = Dio();
    String userId = await SharedPref.getUserLoginId();

    try {
      Response response = await dio.get(
        ApiEndPoint.notificationApi + "userid=${userId}",
      );

      log("Notification response is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        NotificationModel notificationModel = NotificationModel.fromJson(data);
        log(notificationModel.toString());
        return notificationModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<RelatedProductModel?> relatedProductDetails(
    BuildContext context,
  ) async {
    Dio dio = Dio();
    String userId = await SharedPref.getUserLoginId();
    FormData formData = FormData.fromMap({
      "userid": userId,
    });
    try {
      Response response =
          await dio.post(ApiEndPoint.relatedProductApi, data: formData);

      log("Related Product response is ${response.data.toString()}");

      // var data = json.decode(response.data);

      if (response.statusCode == 200) {
        RelatedProductModel relatedProductModel =
            RelatedProductModel.fromJson(response.data);
        log(relatedProductModel.toString());
        return relatedProductModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<AdminAppPagesModel?> adminAppPagesDetails(
    BuildContext context,
  ) async {
    Dio dio = Dio();

    try {
      Response response = await dio.get(
        ApiEndPoint.adminAppPagesApi,
      );

      log("Admin app pages response is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        AdminAppPagesModel adminAppPagesModel =
            AdminAppPagesModel.fromJson(data);
        log(adminAppPagesModel.toString());
        return adminAppPagesModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }

  static Future<GstVerificationModel?> gstVerificationDetails(
    BuildContext context, {
    required String gstNumber,
  }) async {
    Dio dio = Dio();

    FormData formData = FormData.fromMap({
      "gst": gstNumber,
    });
    try {
      Response response =
          await dio.post(ApiEndPoint.gstVerificationApi, data: formData);

      log("Gst Verification response is ${response.data.toString()}");

      var data = json.decode(response.data);

      if (response.statusCode == 200) {
        GstVerificationModel gstVerificationModel =
            GstVerificationModel.fromJson(data);
        log(gstVerificationModel.toString());
        return gstVerificationModel;
      } else {
        return null;
      }
    } on DioException catch (error) {
      return await HandleDio.handleDioError(error: error, context: context);
    }
  }
}
