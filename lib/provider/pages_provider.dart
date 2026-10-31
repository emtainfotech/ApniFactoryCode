import 'dart:developer';

import 'package:apni_factory/model/about_us_model.dart';
import 'package:apni_factory/model/get_pages_model.dart';
import 'package:apni_factory/model/help_model.dart';
import 'package:apni_factory/model/privacy_policy_model.dart';
import 'package:apni_factory/model/return_policy_model.dart';
import 'package:apni_factory/model/terms_condition_model.dart';
import 'package:flutter/material.dart';

import '../api_services/creator_api_services.dart';

class PagesProvider extends ChangeNotifier {
  // bool loadingGetPages = true;
  // GetPagesModel? getPagesModel;
  // Future<void> getPage(
  //     {required BuildContext context, required String id}) async {
  //   try {
  //     loadingGetPages = true;
  //     getPagesModel = await CreatorApiServices.getPagesDetails(context, id: id);
  //     notifyListeners();
  //     if (getPagesModel != null && getPagesModel!.code == 100) {
  //       loadingGetPages = false;

  //       notifyListeners();
  //     } else {
  //       loadingGetPages = true;
  //       notifyListeners();
  //     }
  //   } catch (e) {
  //     log("get pages error: ${e.toString()}");
  //   }
  //   notifyListeners();
  // }

  
}
