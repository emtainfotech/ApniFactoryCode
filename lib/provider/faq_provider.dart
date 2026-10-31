import 'dart:developer';
import 'package:apni_factory/api_services/creator_api_services.dart';
import 'package:apni_factory/model/get_faq_model.dart';
import 'package:flutter/material.dart';
 
class FaqProvider extends ChangeNotifier {
  bool loadingGetFaq = true;
  GetFaqModel? getFaqModel;
  Future<void> getFaq({
    required BuildContext context,
  }) async {
    try {
      loadingGetFaq = true;
      getFaqModel = await CreatorApiServices.getFaqDetails(
        context,
      );
      notifyListeners();
      if (getFaqModel != null && getFaqModel!.code == 100) {
        loadingGetFaq = false;

        notifyListeners();
      } else {
        loadingGetFaq = true;
        notifyListeners();
      }
    } catch (e) {
      log("get faq error: ${e.toString()}");
    }
    notifyListeners();
  }
}
