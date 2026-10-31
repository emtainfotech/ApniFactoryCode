import 'dart:developer';
import 'package:apni_factory/api_services/creator_api_services.dart';
import 'package:apni_factory/model/brand_screen_model.dart';
import 'package:apni_factory/model/search_model.dart';
import 'package:apni_factory/utils.dart';
import 'package:flutter/material.dart';

class BrandScreenProvider extends ChangeNotifier{
  bool loadingBrandScreen = true;
  List? slider;
  BrandScreenModel? brandScreenModel;
  Future<void> brandScreen({
    required BuildContext context,
    required int cmpid,
    required int cid
  }) async {
    try {
      brandScreenModel = null;
      notifyListeners();

      loadingBrandScreen = true;
      brandScreenModel =
          await CreatorApiServices.brandScreenDetails(context, cmpid: cmpid,cid: cid);
      notifyListeners();
      if (brandScreenModel != null && brandScreenModel!.code == 100) {
        loadingBrandScreen = false;

        // slider =companyScreenModel?.data?.;
        notifyListeners();
      } else {
        loadingBrandScreen = true;
        notifyListeners();
      }
      // }
    } catch (e) {
      log("brand error: ${e.toString()}");
    }
    notifyListeners();
  }

final TextEditingController searchController = TextEditingController();

  SearchModel? searchModel;
  bool loadingSearch = false;

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


void clearSearch() {
    searchController.clear();
    searchModel = null;
    loadingSearch = false;
    notifyListeners();
  }

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
} 