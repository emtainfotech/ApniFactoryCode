import 'dart:convert';
import 'dart:developer';

import 'package:apni_factory/api_services/creator_api_services.dart';
import 'package:apni_factory/model/single_order_detail_model.dart';
import 'package:flutter/material.dart';

class SingleOrderDetailProvider extends ChangeNotifier {
  bool loadingSingleOrderDetail = true;
  SingleOrderDetailModel? singleOrderDetailModel;
  String? fullAddress;

  Future<void> singleOrderDetail(
      {required BuildContext context, required String orderno}) async {
    try {
      loadingSingleOrderDetail = true;
      singleOrderDetailModel =
          await CreatorApiServices.singleOrderDetail(context, orderno: orderno);
      notifyListeners();
      if (singleOrderDetailModel != null &&
          singleOrderDetailModel!.code == 100) {
        loadingSingleOrderDetail = false;
        String formatAddress(dynamic address) {
          if (address == null) return "";

          try {
            final Map<String, dynamic> data =
                address is String ? jsonDecode(address) : address;

            return [
              data['name'],
              data['identityname'],
              data['location'],
              data['landmark1'],
              data['landmark2'],
              data['city'],
              data['state'],
              data['pincode'],
              data['country'],
            ].where((e) => e != null && e.toString().isNotEmpty).join(", ");
          } catch (e) {
            log("❌ Address parsing error: $e");
            return "";
          }
        }

        fullAddress = formatAddress(singleOrderDetailModel!.data!.address);
        log(fullAddress.toString());

        notifyListeners();
      } else {
        loadingSingleOrderDetail = true;
        notifyListeners();
      }
    } catch (e) {
      log("single order detail error: ${e.toString()}");
    }
    notifyListeners();
  }
}
