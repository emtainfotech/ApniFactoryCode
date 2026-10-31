import 'dart:developer';

import 'package:apni_factory/api_services/creator_api_services.dart';
import 'package:apni_factory/model/help_no_for_app_model.dart';
import 'package:flutter/material.dart';

class TicketProvider extends ChangeNotifier{
   bool loadingHelpNoForApp = true;
HelpNoForAppModel? helpNoForAppModel;

  Future<void>helpNoForAppDetails({
    required BuildContext context,
  }) async {
    try {
     loadingHelpNoForApp= true;
      notifyListeners();
      
     helpNoForAppModel= await CreatorApiServices.helpNoForAppDetails(context);
      notifyListeners();

      if (helpNoForAppModel != null && helpNoForAppModel!.code == 100) {
      loadingHelpNoForApp = false;

        notifyListeners();
      } else {
      loadingHelpNoForApp = true;
        notifyListeners();
      }
    } catch (e) {
      log("help no for app error: ${e.toString()}");
    }
    notifyListeners();
  }

}