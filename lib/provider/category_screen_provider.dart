import 'dart:developer';

import 'package:apni_factory/api_services/creator_api_services.dart';
import 'package:apni_factory/model/category_screen_model.dart';
import 'package:apni_factory/model/color_screen_model.dart';
import 'package:apni_factory/model/search_model.dart';
import 'package:apni_factory/screens/brand_page_screen.dart';
import 'package:apni_factory/utils.dart';
import 'package:carousel_slider/carousel_controller.dart';
import 'package:flutter/material.dart';

class CategoryScreenProvider extends ChangeNotifier {
  bool loadingCategoryScreen = true;
  bool loadingColorScreen = true;
  ColorScreenModel? colorScreenModel;
  CategoryScreenModel? categoryScreenModel;
  int current = 0;
  int bannercurrent = 0;
  final CarouselSliderController carousalController =
      CarouselSliderController();

  List? slider;
  List? advertisement;
  Future<void> categoryScreen({
    required BuildContext context,
    required int mid,
  }) async {
    try {
      categoryScreenModel = null;
      notifyListeners();

      loadingCategoryScreen = true;
      categoryScreenModel = await CreatorApiServices.categoryScreenDetails(
        context,
        mid: mid,
      );
      notifyListeners();
      if (categoryScreenModel != null && categoryScreenModel!.code == 100) {
        loadingCategoryScreen = false;
        slider = categoryScreenModel?.data?.slider;
        advertisement = categoryScreenModel?.data?.advertisement;
        notifyListeners();
      } else {
        loadingCategoryScreen = true;
        notifyListeners();
      }
      // }
    } catch (e) {
      log("category error: ${e.toString()}");
    }
    notifyListeners();
  }



  SearchModel? searchModel;
  bool loadingSearch = false;

  Future<void> search(
    double height, {
    required BuildContext context,
    String? word,
  }) async {
    try {
      if (word != null && word.trim().isNotEmpty) {
        loadingSearch = true;
        notifyListeners();
       
        searchModel = await CreatorApiServices.searchDetails(
          context,
          word: word.trim(),
        );

        if (searchModel != null && searchModel!.code == 100) {
          loadingSearch = false;
         
          notifyListeners();
        } else {
          loadingSearch = false;
          Utils.commonSnackBar(context,
              height: height, message: "Something Went Wrong");
          notifyListeners();
        }
      } else {
        searchModel = null;
        // Empty input: clear search and show home content
        loadingSearch = false;

        notifyListeners();
      }
    } catch (e) {
      log("search error: ${e.toString()}");
    }
    notifyListeners();
  }


   void onSearchChanged(
    String value,
    double height,
    BuildContext context,
  ) {
    if (value.trim().isEmpty) {
      searchModel = null;
      loadingSearch = false;
      notifyListeners();
    } else {
      search(height, context: context, word: value);
    }
  }

  final TextEditingController categorySearchController = TextEditingController();
  void clearSearch() {
   categorySearchController.clear();
    searchModel = null;
    loadingSearch = false;
    notifyListeners();
  }

  // Future<void> brandScreen(
  //     {required BuildContext context,
  //     required int mid,
  //     required int cid,
  //     required int sid}) async {
  //   try {
  //     categoryScreenModel = null;
  //     notifyListeners();

  //     loadingCategoryScreen = true;
  //     categoryScreenModel = await CreatorApiServices.categoryScreenDetails(
  //       context,
  //       mid: mid,
  //     );
  //     notifyListeners();
  //     if (categoryScreenModel != null &&
  //         categoryScreenModel!.code == 100 &&
  //         categoryScreenModel?.data != null) {
  //       loadingCategoryScreen = false;
    



  //       notifyListeners();
  //     } else {
  //       loadingCategoryScreen = true;
  //       notifyListeners();
  //     }
  //     // }
  //   } ca
  //   notifyList
  // void resetAndReloadCategory(BuildContext context, {required cmpid, required cid}) async {
  //   categoryScre();
  //   await BrandPageScreen(cmpid: cmpid,cid:cid ,);
  // }
}
