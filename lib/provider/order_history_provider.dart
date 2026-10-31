import 'dart:developer';

import 'package:apni_factory/api_services/creator_api_services.dart';
import 'package:apni_factory/model/order_histort_model.dart';
import 'package:flutter/material.dart';

class OrderHistoryProvider extends ChangeNotifier{
  bool loadingOrderHistory = true;
 OrderHistoryModel? orderHistoryModel;

  Future<void> orderHistory({
    required BuildContext context,
  }) async {
    try {
      loadingOrderHistory = true;
      orderHistoryModel=
          await CreatorApiServices.orderHistoryDetails(context);
      notifyListeners();
      if (orderHistoryModel != null && orderHistoryModel!.code == 100) {
       
        loadingOrderHistory = false;

        notifyListeners();
      } else {
        loadingOrderHistory = true;
        notifyListeners();
      }
    } catch (e) {
      log("order history error: ${e.toString()}");
    }
    notifyListeners();
  }
}