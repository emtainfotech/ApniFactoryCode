import 'dart:convert';
import 'dart:developer';
import 'package:apni_factory/model/add_to_cart_model.dart';
import 'package:apni_factory/model/add_to_wishlist_model.dart';
import 'package:apni_factory/model/product_detail_model.dart';
import 'package:apni_factory/model/product_review_model.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/screens/cart_screen.dart';
import 'package:apni_factory/screens/wishlist_screen.dart';
import 'package:apni_factory/utils.dart';
import 'package:carousel_slider/carousel_controller.dart';
import 'package:flutter/material.dart';
import '../api_services/creator_api_services.dart';
import '../res/widgets/round_button.dart';

class ProductDetailProvider extends ChangeNotifier {
  bool loadingproductDetail = true;
  ProductDetailModel? productDetailModel;
  TextEditingController revierController = TextEditingController();
  int current = 0;
  final CarouselSliderController carouselSliderController =
      CarouselSliderController();

  List<String> imageList = [];
  // List? attributes;

  Future<void> productDetail(
      {required BuildContext context,
      required String productid,
      required String colorid}) async {
    // attributes = [];
    try {
      loadingproductDetail = true;
      notifyListeners();
      productDetailModel = await CreatorApiServices.producDetailsDetails(
          context,
          productid: productid,
          colorid: colorid);

      notifyListeners();
      if (productDetailModel != null && productDetailModel!.code == 100) {
        loadingproductDetail = false;

        // attributes = productDetailModel?.data?.attributes?.map((attr) {
        //       return {
        //         "attributeid": attr.attributeid,
        //         "qtyquantity": attr.quantity
        //       };
        //     }).toList() ??
        //     [];
        // log(attributes.toString());
        imageList =
            List<String>.from(productDetailModel?.data?.multipleimages ?? []);

        notifyListeners();
      } else {
        loadingproductDetail = true;
        notifyListeners();
      }
    } catch (e) {
      log("product detail error: ${e.toString()}");
    }
    notifyListeners();
  }

  bool loadingProductReview = false;
  Future<void> productReview(
      {required BuildContext context,
      required double height,
      required String productid,
      String? review}) async {
    try {
      if (review != null && review.isNotEmpty) {
        loadingProductReview = true;
        notifyListeners();
        ProductReviewModel? productReviewModel =
            await CreatorApiServices.producReviewDetails(context,
                productid: productid,
                rating: rating.toString(),
                review: review);

        notifyListeners();
        if (productReviewModel != null && productReviewModel.code == 100) {
          loadingProductReview = false;

          Utils.commonSnackBar(context,
              height: height, message: productReviewModel.msg.toString());


          revierController.clear();
          rating = 0.0;
          notifyListeners();
        } else {
          Utils.commonSnackBar(context,
              height: height,
              message: productReviewModel?.msg.toString() ?? "");
          loadingProductReview = true;
          notifyListeners();
        }
      } else {
        Utils.commonSnackBar(context,
            height: height, message: "Enter All Details");
      }
    } catch (e) {
      log("product review error: ${e.toString()}");
    }
    notifyListeners();
  }

  double rating = 0.0;
  void starRating(v) {
    rating = v;
    notifyListeners();
  }



  List<Map<String, String>> getAttributesList({required int id}) {
    return productDetailModel!.data!.attributes!.where((entry) => entry.attributeid == id).map((entry) {
      return {
        "id": entry.attributeid.toString(),
        "qty": "1",
      };
    }).toList();
   
  }

  bool loadingAddToCart = false;
  AddToCartModel? addToCartModel;
  Future<void> addToCart(
      {required BuildContext context,
      required BuildContext globalContext,
      required double height,
      required double width,
      required String productid,
      required List attributes,
      required int brandid}) async {
    try {
      loadingAddToCart = true;
      notifyListeners();
      log(attributes.toString());
      log(productid);
      log(brandid.toString());
      addToCartModel = await CreatorApiServices.addToCartDetails(context,
          productid: productid,
          attributes: attributes,
          brandid: brandid.toString());
log(attributes.toString());
      notifyListeners();
      // if (!context.mounted) return;
      if (addToCartModel != null && addToCartModel?.code == 100) {
        loadingAddToCart = false;
        Navigator.push(globalContext, MaterialPageRoute(
          builder: (context) {
            return CartScreen();
          },
        ));
        Utils.commonSnackBar(globalContext,
            height: height, message: addToCartModel?.msg.toString() ?? "");

        notifyListeners();
      } else if (addToCartModel != null && addToCartModel?.code == 500) {
        loadingAddToCart = false;

        Utils.commonSnackBar(globalContext,
            height: height, message: addToCartModel!.msg.toString());
      } else {
        loadingAddToCart = true;
        notifyListeners();
      }
    } catch (e) {
      log("add to cart error: ${e.toString()}");
    }
    notifyListeners();
  }

  bool loadingAddToWishlist = false;
  AddToWishlistModel? addToWishlistModel;

  Future<void> addToWishList({
    required BuildContext context,
    required double height,
    required double width,
    String? productid,
    String? color,
  }) async {
    try {
      if (productid != null &&
          productid.isNotEmpty &&
          color != null &&
          color.isNotEmpty) {
        loadingAddToWishlist = true;
        notifyListeners();
        addToWishlistModel = await CreatorApiServices.addToWishlistDetails(
            context,
            productid: productid,
            color: color);
        notifyListeners();

        if (addToWishlistModel != null && addToWishlistModel!.code == 100) {
          loadingAddToWishlist = false;

          notifyListeners();
          Utils.commonSnackBar(context,
              height: height, message: addToWishlistModel!.msg.toString());
          Utils.commonSnackBar(context,
              height: height, message: addToWishlistModel!.msg.toString());
          showModalBottomSheet(
            context: context,
            builder: (context) {
              return Container(
                  height: height / 6,
                  width: width,
                  decoration: BoxDecoration(
                      boxShadow: [
                        BoxShadow(
                            blurRadius: 32,
                            color: AppColors.blackColor.withOpacity(0.1))
                      ],
                      // border: Border.all(color: AppColors.primaryColor),
                      borderRadius: BorderRadius.circular(height / 66.6),
                      color: AppColors.whiteColor),
                  child: Padding(
                    padding: EdgeInsets.symmetric(
                      horizontal: width / 16.2,
                    ),
                    child: Column(
                      children: [
                        SizedBox(
                          height: height / 42,
                        ),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              addToWishlistModel?.msg.toString() ?? "",
                              style: AppTextStyle.poppinsRegular14(
                                  context: context,
                                  fontWeight: FontWeight.w500),
                            ),
                            // Text(
                            //   "Total : ₹ ",
                            //   style: AppTextStyle.poppinsRegular16(
                            //       context: context,
                            //       color: AppColors.secondaryColor,
                            //       fontWeight: FontWeight.w500),
                            // )
                          ],
                        ),
                        SizedBox(
                          height: height / 42,
                        ),
                        RoundButton(
                          title: "View Wishlist",
                          onPressed: () {
                            Navigator.push(context, MaterialPageRoute(
                              builder: (context) {
                                return WishlistScreen();
                              },
                            ));
                          },
                        )
                      ],
                    ),
                  ));
            },
          );
          notifyListeners();
        } else if (addToWishlistModel != null &&
            addToWishlistModel!.code == 500) {
          loadingAddToWishlist = false;

          Utils.commonSnackBar(context,
              height: height, message: addToWishlistModel!.msg.toString());
          showModalBottomSheet(
            context: context,
            builder: (context) {
              return Container(
                  height: height / 6,
                  width: width,
                  decoration: BoxDecoration(
                      boxShadow: [
                        BoxShadow(
                            blurRadius: 32,
                            color: AppColors.blackColor.withOpacity(0.1))
                      ],
                      // border: Border.all(color: AppColors.primaryColor),
                      borderRadius: BorderRadius.circular(height / 66.6),
                      color: AppColors.whiteColor),
                  child: Padding(
                    padding: EdgeInsets.symmetric(
                      horizontal: width / 16.2,
                    ),
                    child: Column(
                      children: [
                        SizedBox(
                          height: height / 42,
                        ),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              addToWishlistModel?.msg.toString() ?? "",
                              style: AppTextStyle.poppinsRegular14(
                                  context: context,
                                  fontWeight: FontWeight.w500),
                            ),
                            // Text(
                            //   "Total : ₹ ",
                            //   style: AppTextStyle.poppinsRegular16(
                            //       context: context,
                            //       color: AppColors.secondaryColor,
                            //       fontWeight: FontWeight.w500),
                            // )
                          ],
                        ),
                        SizedBox(
                          height: height / 42,
                        ),
                        RoundButton(
                          title: "View Wishlist",
                          onPressed: () {
                            Navigator.push(context, MaterialPageRoute(
                              builder: (context) {
                                return WishlistScreen();
                              },
                            ));
                          },
                        )
                      ],
                    ),
                  ));
            },
          );
        } else {
          loadingAddToWishlist = true;
          notifyListeners();
        }
      }
    } catch (e) {
      log("add to wishlist error: ${e.toString()}");
    }
    notifyListeners();
  }
}
