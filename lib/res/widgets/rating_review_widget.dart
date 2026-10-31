import 'package:apni_factory/provider/product_detail_provider.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:apni_factory/res/widgets/round_button.dart';
import 'package:apni_factory/res/widgets/star_widget.dart';
import 'package:apni_factory/utils.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:smooth_star_rating_null_safety/smooth_star_rating_null_safety.dart';

class RatingReviewWidget extends StatelessWidget {
    final double width;
  final double height;
  final ProductDetailProvider provider;
  const RatingReviewWidget({super.key, required this.width, required this.height, required this.provider});

  @override
  Widget build(BuildContext context) {
    return Padding(
                          padding: EdgeInsets.all(height / 90),
                          child: Container(
                              width: width,
                              decoration: BoxDecoration(
                                  color: AppColors.whiteColor,
                                  borderRadius:
                                      BorderRadius.circular(height / 30),
                                  boxShadow: [BoxShadow()]),
                              child: Padding(
                                padding: EdgeInsets.symmetric(
                                    horizontal: height / 55),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    SizedBox(
                                      height: height / 30,
                                    ),
                                    Text(
                                      "Rating & Review",
                                       style: AppTextStyle.poppinsRegular16(
                                          context: context,
                                         color: AppColors.secondaryColor,fontWeight: FontWeight.w500),
                                    ),
                                    SizedBox(
                                      height: height / 35,
                                    ),
                                    StarWidget(
                                      provider: provider,
                                    ),
                                    SizedBox(
                                      height: height / 60,
                                    ),
                                    Utils.commonTextFormField(context,
                                        height: height,
                                        width: width,
                                        labelText: "Enter Review",
                                        controller: provider.revierController),
                                    SizedBox(
                                      height: height / 30,
                                    ),
                                    provider.loadingProductReview
                                        ? CircularProgressIndicatorWidget()
                                        : RoundButton(
                                            title: "Submit",
                                            onPressed: () {
                                              provider.productDetailModel?.data
                                                          ?.postreview ==
                                                      true
                                                  ? provider.productReview(
                                                      context: context,
                                                      height: height,
                                                      productid: provider
                                                              .productDetailModel
                                                              ?.data
                                                              ?.productId
                                                              .toString() ??
                                                          "",
                                                      review: provider
                                                          .revierController.text
                                                          .trim())
                                                  : Utils.commonSnackBar(
                                                      context,
                                                      height: height,
                                                      message:
                                                          "Please buy the product first");
                                            },
                                          ),
                                    SizedBox(
                                      height: height / 40,
                                    ),
                                    ListView.builder(
                                      physics: NeverScrollableScrollPhysics(),
                                      shrinkWrap: true,
                                      itemCount: provider.productDetailModel
                                          ?.data?.reviewlist?.length,
                                      itemBuilder: (context, index) {
                                        return ListTile(
                                          subtitle: Row(
                                              mainAxisAlignment:
                                                  MainAxisAlignment
                                                      .spaceBetween,
                                              children: [
                                                Text(
                                                  provider
                                                          .productDetailModel
                                                          ?.data
                                                          ?.reviewlist?[index]
                                                          .review ??
                                                      "",
                                                  style: AppTextStyle
                                                      .poppinsMedium15(
                                                          context: context,
                                                          color: AppColors
                                                              .blackColor),
                                                ),
                                                SmoothStarRating(
                                                    allowHalfRating: false,
                                                    onRatingChanged: (v) {},
                                                    starCount: provider
                                                            .productDetailModel
                                                            ?.data
                                                            ?.reviewlist?[index]
                                                            .rating ??
                                                        0,
                                                    // rating: provider.rating,
                                                    size: height / 40,
                                                    filledIconData: Icons.star,
                                                    halfFilledIconData:
                                                        Icons.star_border,
                                                    color:
                                                        AppColors.primaryColor,
                                                    borderColor:
                                                        AppColors.primaryColor,
                                                    spacing: 0.0)
                                              ]),
                                          title: Text(
                                              provider
                                                      .productDetailModel
                                                      ?.data
                                                      ?.reviewlist?[index]
                                                      .customername ??
                                                  "",
                                              style:
                                                  AppTextStyle.poppinsRegular16(
                                                      context: context,
                                                      color:
                                                          AppColors.blackColor,
                                                      fontWeight:
                                                          FontWeight.w500)),
                                        );
                                      },
                                    ),
                                    SizedBox(
                                      height: height / 3,
                                    ),
                                  ],
                                ),
                              )),
                        );
  }
}