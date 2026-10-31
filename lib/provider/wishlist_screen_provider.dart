import 'dart:developer';
import 'package:apni_factory/api_services/creator_api_services.dart';
import 'package:apni_factory/model/delete_wishlist_model.dart';
import 'package:apni_factory/model/user_wishlist_model.dart';
import 'package:apni_factory/utils.dart';
import 'package:flutter/material.dart';

class WishlistProvider extends ChangeNotifier {
  bool loadingUserWishlist = true;
  UserWishlistModel? userWishlistModel;

  Future<void> userWishList({
    required BuildContext context,
  }) async {
    try {
      loadingUserWishlist = true;
      notifyListeners();
      
      userWishlistModel = await CreatorApiServices.userWishlistDetails(context);
      notifyListeners();

      if (userWishlistModel != null && userWishlistModel!.code == 100) {
        loadingUserWishlist = false;

        notifyListeners();
      } else {
        loadingUserWishlist = true;
        notifyListeners();
      }
    } catch (e) {
      log("user wishlist error: ${e.toString()}");
    }
    notifyListeners();
  }





  bool loadingDeleteWishlist = false;

  Future<void> deleteWishList(
      {required BuildContext context,
      required BuildContext dialogContext,
      required double height,
      required String wishlistid}) async {
    try {
      loadingDeleteWishlist = true;
      notifyListeners();

      DeleteWishlistModel? deleteWishlistModel =
          await CreatorApiServices.deleteWishlistDetails(context,
              wishlistid: wishlistid);
      notifyListeners();

      if (deleteWishlistModel != null && deleteWishlistModel.code == 100) {
        if (context.mounted) {
          Utils.commonSnackBar(context,
              height: height, message: deleteWishlistModel.msg.toString());
        }

        await userWishList(context: context); // Refresh list before dialog pop

        if (dialogContext.mounted) {
          Navigator.pop(dialogContext); // Close dialog last
        }

        loadingDeleteWishlist = false;
        notifyListeners();
     
      } else {
        loadingUserWishlist = true;
        notifyListeners();
      }
    } catch (e) {
      log("delete wishlist error: ${e.toString()}");
    }
    notifyListeners();
  }
}
