import 'dart:developer';

import 'package:apni_factory/api_services/creator_api_services.dart';
import 'package:apni_factory/model/product_category_list_model.dart';
import 'package:apni_factory/model/product_list_by_category_model.dart';
import 'package:flutter/material.dart';

class ProductCategoryListScreenProvider extends ChangeNotifier {
  bool loadingProductCategoryListScreen = true;
  List? slider;
  ProductCategoryListScreenModel? productCategoryListScreenModel;
  String? selectedCategory;
  int? selectedBid;
  int? selectedSid;

  List? productDetails;
  List? subCategoryDetails;

  Future<void> productCategoryListScreen({
    required BuildContext context,
    required int bid,
    required int cid,
  }) async {
    try {
      productCategoryListScreenModel = null;
      notifyListeners();

      loadingProductCategoryListScreen = true;

      productCategoryListScreenModel =
          await CreatorApiServices.productCategoryListScreenDetails(context,
              bid: bid, cid: cid);

      notifyListeners();
      if (productCategoryListScreenModel != null &&
          productCategoryListScreenModel!.code == 100) {
                   loadingProductCategoryListScreen = false;
        selectedCategory = productCategoryListScreenModel
            ?.data?.product?[0].categoryname
            .toString();

        notifyListeners();
        subCategoryDetails = productCategoryListScreenModel?.data?.subcategory;
        selectedBid =
            productCategoryListScreenModel?.data?.subcategory?[0].bid ?? 0;
        selectedSid =
            productCategoryListScreenModel?.data?.subcategory?[0].cid ?? 0;

        productDetails = productCategoryListScreenModel?.data?.product;

        notifyListeners();
 
        // slider =companyScreenModel?.data?.;
        notifyListeners();
      } else {
        loadingProductCategoryListScreen = true;
        notifyListeners();
      }
      // }
    } catch (e) {
      log("product category list error: ${e.toString()}");
    }
    notifyListeners();
  }

  ProductListByCategoryScreenModel? productListBySubCategoryScreenModel;
  bool loadingProductListBySubcategory = false;
  Future<void> productListBySubCategoryScreen({
    required BuildContext context,
    required int bid,
    required int sid,
  }) async {
    try {
      loadingProductListBySubcategory = true;

      productListBySubCategoryScreenModel =
          await CreatorApiServices.productListByCategoryScreenDetails(context,
              sid: sid, bid: bid);
      notifyListeners();
      if (productListBySubCategoryScreenModel != null &&
          productListBySubCategoryScreenModel!.code == 100) {
        loadingProductListBySubcategory = false;
        notifyListeners();
        productDetails = productListBySubCategoryScreenModel?.data?.product;
        subCategoryDetails =
            productListBySubCategoryScreenModel?.data?.subcategory;

        // selectedColorId=productListBySubCategoryScreenModel?.data?.product?[index].colorid;
        // selectedPid=productListBySubCategoryScreenModel?.data?.product?[index].pid;
        // selectedCategory =
        //     productCategoryListScreenModel?.data?.subcategory?[0].toString();

        // slider =companyScreenModel?.data?.;
        notifyListeners();
      } else {
        loadingProductListBySubcategory = true;
        notifyListeners();
      }
      // }
    } catch (e) {
      log("product list by sub category error: ${e.toString()}");
    }
    notifyListeners();
  }

  // final List<String> categories = ['Electronics', 'Clothing', 'Groceries'];

  int selectedCategoryIndex = 0;

  // Initially selected category
  void selectedSubCategory({
    required int index,
    required BuildContext context,
  }) {
    selectedCategoryIndex = index;

    selectedCategory =
        productCategoryListScreenModel?.data?.subcategory?[index].toString();
    selectedBid =
        productCategoryListScreenModel?.data?.subcategory?[index].bid ?? 0;
    selectedSid = productCategoryListScreenModel?.data?.subcategory?[index].cid;

    productListBySubCategoryScreen(
      context: context,
      bid: selectedBid ?? 0,
      sid: selectedSid ?? 0,
    );

    notifyListeners();
  }

  void resetState() {
    selectedCategoryIndex = 0;
    selectedCategory = null;
    productDetails = null;
    subCategoryDetails = null;
    productCategoryListScreenModel = null;
    loadingProductCategoryListScreen = true;
    loadingProductListBySubcategory = false;
    notifyListeners();
  }
}
