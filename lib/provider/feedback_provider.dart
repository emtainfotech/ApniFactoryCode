import 'dart:developer';
import 'package:apni_factory/api_services/creator_api_services.dart';
import 'package:apni_factory/model/get_feedback_model.dart';
import 'package:apni_factory/model/post_feedback_model.dart';
import 'package:flutter/material.dart';
import '../utils.dart';

class FeedbackProvider extends ChangeNotifier {
  bool loadingGetFeedbackList = true;
  GetFeedbackListModel? getFeedbackListModel;
  TextEditingController feedbackController = TextEditingController();
  Future<void> getFeedbackList({
    required BuildContext context,
  }) async {
    try {
      loadingGetFeedbackList = true;

      getFeedbackListModel = await CreatorApiServices.getFeedbackListDetails(context);
      notifyListeners();
      if (getFeedbackListModel != null && getFeedbackListModel!.code == 100) {

        loadingGetFeedbackList = false;

        notifyListeners();
      } else {
        loadingGetFeedbackList = true;
        notifyListeners();
      }
    } catch (e) {
      log("get feedback error: ${e.toString()}");
    }
    notifyListeners();
  }

  bool loadingPostFeedback = false;

  Future<void> postFeedback(
    double height, {
    required BuildContext context,
    String? feedback,
  }) async {
    try {
      if (feedback != null && feedback.isNotEmpty) {
        loadingPostFeedback = true;

        PostFeedbackModel? postFeedbackModel =
            await CreatorApiServices.postFeedbackDetails(context,
                feedback: feedback);
        notifyListeners();

        if (postFeedbackModel != null && postFeedbackModel.code == 100) {
          feedbackController.clear();

          Utils.commonSnackBar(context,
              height: height, message: postFeedbackModel!.msg.toString());
          await getFeedbackList(
            context: context,
          );
          loadingPostFeedback = false;

          notifyListeners();
        } else if (postFeedbackModel != null && postFeedbackModel.code == 500) {
          loadingPostFeedback = false;
          Utils.commonSnackBar(context,
              height: height, message: postFeedbackModel!.msg.toString());
          notifyListeners();
        } else {
          loadingPostFeedback = false;
          Utils.commonSnackBar(context,
              height: height, message: "Something Went Wrong");
          notifyListeners();
        }
      } else {
        Utils.commonSnackBar(context,
            height: height, message: "Enter Feedback");
        notifyListeners();
      }
    } catch (e) {
      log("post feedback error: ${e.toString()}");
    }
    notifyListeners();
  }
}
