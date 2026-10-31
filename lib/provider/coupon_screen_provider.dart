import 'dart:developer';
import 'package:apni_factory/api_services/creator_api_services.dart';
import 'package:apni_factory/model/apply_coupon_model.dart';
import 'package:apni_factory/model/get_coupon_list_model.dart';
import 'package:apni_factory/provider/cart_screen_provider.dart';
import 'package:apni_factory/res/widgets/global_cart_container.dart';
import 'package:apni_factory/utils.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class CouponScreenProvider extends ChangeNotifier {
  bool loadingGetCouponList = true;
  GetCouponListModel? getCouponListModel;
  List<String>? percent;
  List<Data>? a;
  Future<void> getCouponList({
    required BuildContext context,
    required String couponof
  }) async {
    try {
      loadingGetCouponList = true;
      getCouponListModel =
          await CreatorApiServices.getCouponListDetails(context,couponof: couponof);
      notifyListeners();
      if (getCouponListModel != null && getCouponListModel!.code == 200) {
        // if (getCouponListModel!.data != null) {
        //   a = getCouponListModel!.data;
        //   for (var i = 0; i < getCouponListModel!.data!.length; i++) {
        //     percent = a![i].name!.split("%");

        //     log(a![i].price.toString());
        //   }

        //   // a![0].price;
        //   // for(var element in a!){
        //   //   log(a![0].price.toString());
        //   // }
        // }
        loadingGetCouponList = false;

        notifyListeners();
      } else {
        loadingGetCouponList = true;
        notifyListeners();
      }
    } catch (e) {
      log("get coupon list error: ${e.toString()}");
    }
    notifyListeners();
  }

    bool loadingApplyCoupon = false;
    Future<void> applyCoupon({
      required double height,
      required BuildContext context,
      required String couponId,
    }) async {
      try {
        loadingApplyCoupon = true;
        ApplyCouponModel? applyCouponModel =
            await CreatorApiServices.applyCouponDetails(context,
                couponid: couponId);
        notifyListeners();
      if (applyCouponModel != null && applyCouponModel.code == 200) {
        loadingApplyCoupon = false;

        await Provider.of<CartScreenProvider>(context, listen: false)
            .userCart(context: context);
        Navigator.pop(context);
        Utils.commonSnackBar(context,
            height: height, message: "Coupon Applied");
        // Utils.commonSnackBar(context,
        //     height: height, message: orderModel.msg.toString());

        notifyListeners();
      } else {
        loadingApplyCoupon = true;
        notifyListeners();
      }
    } catch (e) {
      log("apply coupon error: ${e.toString()}");
    }
    notifyListeners();
  }
}
