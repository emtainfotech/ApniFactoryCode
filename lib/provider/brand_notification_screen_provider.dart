import 'dart:developer';

import 'package:apni_factory/api_services/creator_api_services.dart';
import 'package:apni_factory/model/brand_detail_model.dart';
import 'package:flutter/material.dart';

class BrandNotificationScreenProvider extends ChangeNotifier {
  bool loadingBrandDetail = true;
  BrandDetailModel? brandDetailModel;

  Future<void> brandDetail(
      {required BuildContext context, required String brandid}) async {
    try {
      loadingBrandDetail = true;
      brandDetailModel =
          await CreatorApiServices.brandDetail(context, brandid: brandid);
      notifyListeners();
      if (brandDetailModel != null && brandDetailModel!.code == 100) {
        loadingBrandDetail = false;

        notifyListeners();
      } else {
        loadingBrandDetail = true;
        notifyListeners();
      }
    } catch (e) {
      log("brand detail error: ${e.toString()}");
    }
    notifyListeners();
  }
}
