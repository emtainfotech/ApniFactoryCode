import 'dart:developer';

import 'package:apni_factory/provider/feedback_provider.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:apni_factory/res/widgets/round_button.dart';
import 'package:apni_factory/utils.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../res/colors.dart';
import '../res/textstyle.dart';
import '../res/widgets/back_button_widget.dart';

class SendFeedbackScreen extends StatefulWidget {
  const SendFeedbackScreen({super.key});

  @override
  State<SendFeedbackScreen> createState() => _SendFeedbackScreenState();
}

class _SendFeedbackScreenState extends State<SendFeedbackScreen> {
  @override
  void initState() {
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      await Provider.of<FeedbackProvider>(context, listen: false)
          .getFeedbackList(context: context);
    });
    // TODO: implement initState
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(
      body: Consumer<FeedbackProvider>(
        builder: (context, provider, child) {
          return provider.loadingGetFeedbackList
              ? CircularProgressIndicatorWidget()
              : ListView(
                  children: [
                    SizedBox(
                      height: height / 26.6,
                    ),
                    Padding(
                      padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                      child: InkWell(
                        onTap: () {
                          Navigator.pop(context);
                        },
                        child: BackButtonWidget(
                          title: provider.getFeedbackListModel != null &&
                                  provider
                                      .getFeedbackListModel!.data!.isNotEmpty
                              ? "Your Feedback"
                              : "Send Feedback",
                        ),
                      ),
                    ),
                    SizedBox(
                      height: height / 23.5,
                    ),
                    Padding(
                      padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                      child: Column(
                        children: [
                          provider.getFeedbackListModel != null &&
                                  provider
                                      .getFeedbackListModel!.data!.isNotEmpty
                              ? Container(
                                  width: width,
                                  decoration: BoxDecoration(
                                    color: AppColors.whiteColor,
                                    borderRadius: BorderRadius.circular(8.0),
                                    boxShadow: [
                                      BoxShadow(
                                        color: AppColors.greyColor
                                            .withOpacity(0.2),
                                        spreadRadius: 0,
                                        blurRadius: 32,
                                      ),
                                    ],
                                  ),
                                  child: Padding(
                                    padding: EdgeInsets.symmetric(
                                        horizontal: width / 16.2,
                                        vertical: height / 50),
                                    child: Text(
                                      provider.getFeedbackListModel!.data![0]
                                              .feedback ??
                                          "",
                                      style: AppTextStyle.poppinsRegular16(
                                          context: context,
                                          color: AppColors.secondaryColor),
                                    ),
                                  ),
                                )
                              : Utils.commonTextFormField(context,
                                  height: height,
                                  width: width,
                                  labelText: "Enter Feedback",
                                  controller: provider.feedbackController),
                          SizedBox(
                            height: height / 40,
                          ),
                          Container(
                            height: height / 9,
                            width: width,
                            decoration: BoxDecoration(
                                boxShadow: [
                                  BoxShadow(
                                      blurRadius: 32,
                                      color:
                                          AppColors.blackColor.withOpacity(0.1))
                                ], // border: Border.all(color: AppColors.primaryColor),
                                borderRadius:
                                    BorderRadius.circular(height / 66.6),
                                color: AppColors.whiteColor),
                            child: Padding(
                              padding: EdgeInsets.symmetric(
                                  horizontal: width / 16.2),
                              child: Row(
                                children: [
                                  Icon(
                                    Icons.star,
                                    color: AppColors.primaryColor,
                                  ),
                                  SizedBox(
                                    width: width / 30,
                                  ),
                                  Expanded(
                                    child: Text(
                                        "Need help with your order?\nGet instant help from our customer support team ",
                                        style: AppTextStyle.poppinsMedium15(
                                            context: context,
                                            color: AppColors.secondaryColor)),
                                  ),
                                ],
                              ),
                            ),
                          ),
                          SizedBox(
                            height: height / 2,
                          ),
                          provider.getFeedbackListModel != null &&
                                  provider
                                      .getFeedbackListModel!.data!.isNotEmpty
                              ? SizedBox.shrink()
                              : provider.loadingPostFeedback
                                  ? CircularProgressIndicatorWidget()
                                  : RoundButton(
                                      title: "Submit Feedback",
                                      onPressed: () {
                                        provider.postFeedback(height,
                                            context: context,
                                            feedback: provider
                                                .feedbackController.text
                                                .trim());
                                      },
                                    )
                        ],
                      ),
                    )
                  ],
                );
        },
      ),
    );
  }
}
