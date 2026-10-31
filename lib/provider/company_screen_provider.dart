import 'dart:developer';
import 'package:apni_factory/api_services/creator_api_services.dart';
import 'package:apni_factory/model/company_screen_model.dart';
import 'package:apni_factory/model/search_model.dart';
import 'package:apni_factory/utils.dart';
import 'package:carousel_slider/carousel_controller.dart';
import 'package:flutter/material.dart';

class CompanyScreenProvider extends ChangeNotifier {
  bool loadingCompanyScreen = true;
  List? slider;
  CompanyScreenModel? companyScreenModel;

  List? advertisementImage;
  int current = 0;
  int advertisementCurrent = 0;
  final CarouselSliderController carousalController =
      CarouselSliderController();
  Future<void> companyScreen({
    required BuildContext context,
    required int cid,
  }) async {
    try {
      companyScreenModel = null;
      notifyListeners();

      loadingCompanyScreen = true;
      companyScreenModel =
          await CreatorApiServices.companyScreenDetails(context, cid: cid);
      notifyListeners();
      if (companyScreenModel != null && companyScreenModel!.code == 100) {
        slider = companyScreenModel?.data?.slider;
        advertisementImage = companyScreenModel?.data?.advertisement;
        loadingCompanyScreen = false;

        // slider =companyScreenModel?.data?.;
      } else {
        loadingCompanyScreen = true;
        notifyListeners();
      }        notifyListeners();

      // }
    } catch (e) {
      log("company error: ${e.toString()}");
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

  final TextEditingController companySearchController = TextEditingController();
  void clearSearch() {
    companySearchController.clear();
    searchModel = null;
    loadingSearch = false;
    notifyListeners();
  }
}
