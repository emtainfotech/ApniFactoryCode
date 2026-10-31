import 'package:apni_factory/provider/coupon_screen_provider.dart';
import 'package:apni_factory/res/asset_image.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:flutter/material.dart';
import 'package:flutter_widget_from_html/flutter_widget_from_html.dart';
import 'package:provider/provider.dart';
import '../res/colors.dart';
import '../res/textstyle.dart';
import '../res/widgets/back_button_widget.dart';
import '../utils.dart';

class CouponOfferScreen extends StatefulWidget {
  final String couponof;
  const CouponOfferScreen({super.key, required this.couponof});

  @override
  State<CouponOfferScreen> createState() => _CouponOfferScreenState();
}

class _CouponOfferScreenState extends State<CouponOfferScreen> {
  @override
  void initState() {
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      await Provider.of<CouponScreenProvider>(context, listen: false)
          .getCouponList(context: context, couponof: widget.couponof);
    });
    // TODO: implement initState
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;

    return Scaffold(
      body: Consumer<CouponScreenProvider>(
        builder: (context, provider, child) {
          return provider.loadingGetCouponList || provider.loadingApplyCoupon
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
                          title: "Coupons for you",
                        ),
                      ),
                    ),
                    // SizedBox(
                    //   height: height / 26.6,
                    // ),
                    // Padding(
                    //   padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                    //   child: Utils.commonTextFormField(context,
                    //       height: height,
                    //       width: width,
                    //       labelText: "Type coupon code here",
                    //       suffixIcon: Icon(
                    //         Icons.add,
                    //         color: AppColors.lightText,
                    //       )),
                    // ),
                    SizedBox(
                      height: height / 23.5,
                    ),
                    Padding(
                      padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                      child: Text(
                        "BEST OFFERS FOR YOU",
                        style: AppTextStyle.poppinsRegular14(
                            context: context,
                            fontWeight: FontWeight.w600,
                            color: AppColors.secondaryColor),
                      ),
                    ),
                    SizedBox(
                      height: height / 42.1,
                    ),
                    provider.getCouponListModel?.data == null ||
                            provider.getCouponListModel!.data!.isEmpty
                        ? Padding(
                            padding: EdgeInsets.only(top: height / 3.4),
                            child: Center(
                              child: Text(
                                "Coming Soon",
                                style: AppTextStyle.poppinsBold24(
                                    context: context,
                                    color: AppColors.primaryColor),
                              ),
                            ),
                          )
                        : ListView.builder(
                            physics: NeverScrollableScrollPhysics(),
                            itemCount:
                                provider.getCouponListModel?.data?.length,
                            shrinkWrap: true,
                            itemBuilder: (context, index) {
                              return Padding(
                                padding: EdgeInsets.symmetric(
                                    horizontal: width / 16.2,
                                    vertical: width / 40),
                                child: Card(
                                  child: Container(
                                      width: width / 4.9,
                                      decoration: BoxDecoration(
                                          boxShadow: [
                                            BoxShadow(
                                                blurRadius: 32,
                                                color: AppColors.blackColor
                                                    .withOpacity(0.1))
                                          ],
                                          // border: Border.all(color: AppColors.primaryColor),
                                          borderRadius: BorderRadius.circular(
                                              height / 66.6),
                                          color: AppColors.whiteColor),
                                      child: Column(
                                        children: [
                                          SizedBox(
                                            height: height / 40,
                                          ),
                                          Padding(
                                            padding: EdgeInsets.symmetric(
                                              horizontal: width / 16.2,
                                            ),
                                            child: Row(
                                              crossAxisAlignment:
                                                  CrossAxisAlignment.start,
                                              children: [
                                                provider
                                                            .getCouponListModel
                                                            ?.data?[index]
                                                            .image ==
                                                        null
                                                    ? Image.network(
                                                        "https://cdn-icons-png.freepik.com/512/5629/5629119.png",
                                                        height: height / 20,
                                                      )
                                                    : Image.network(
                                                        "${AppAssetImage.imageBaseUrl}storage/app/public/${(provider.getCouponListModel?.data?[index].image)}",
                                                        height: height / 20,
                                                        width: width / 5,
                                                      ),
                                                SizedBox(
                                                  width: width / 30,
                                                ),
                                                Expanded(
                                                    child: Column(
                                                  crossAxisAlignment:
                                                      CrossAxisAlignment.start,
                                                  children: [
                                                    Row(
                                                      mainAxisAlignment:
                                                          MainAxisAlignment
                                                              .spaceBetween,
                                                      children: [
                                                        Text(
                                                          provider
                                                                  .getCouponListModel
                                                                  ?.data?[index]
                                                                  .code ??
                                                              "",
                                                          style: AppTextStyle
                                                              .poppinsMedium15(
                                                                  context:
                                                                      context,
                                                                  color: AppColors
                                                                      .secondaryColor),
                                                        ),
                                                        InkWell(
                                                          onTap: () {
                                                            provider
                                                                .applyCoupon(
                                                              height: height,
                                                              context: context,
                                                              couponId: provider
                                                                      .getCouponListModel
                                                                      ?.data?[
                                                                          index]
                                                                      .id
                                                                      .toString() ??
                                                                  "0",
                                                            );
                                                          },
                                                          child: Text(
                                                            "Apply",
                                                            style: AppTextStyle
                                                                .poppinsMedium15(
                                                                    context:
                                                                        context,
                                                                    color: AppColors
                                                                        .primaryColor),
                                                          ),
                                                        ),
                                                      ],
                                                    ),
                                                    SizedBox(
                                                      height: height / 120,
                                                    ),
                                                    Text(
                                                      provider
                                                              .getCouponListModel
                                                              ?.data?[index]
                                                              .name ??
                                                          "",
                                                      style: AppTextStyle
                                                          .poppinsBold13(
                                                              context: context,
                                                              fontWeight:
                                                                  FontWeight
                                                                      .w500,
                                                              color: AppColors
                                                                  .lightText),
                                                    ),
                                                    SizedBox(
                                                      height: height / 120,
                                                    ),
                                                    Text(
                                                      provider
                                                              .getCouponListModel
                                                              ?.data?[index]
                                                              .title ??
                                                          "",
                                                      style: AppTextStyle
                                                          .poppinsBold12(
                                                              context: context,
                                                              fontWeight:
                                                                  FontWeight
                                                                      .w400,
                                                              color: AppColors
                                                                  .lightText),
                                                    ),
                                                    Divider(),
                                                    SizedBox(
                                                      height: height / 120,
                                                    ),
                                                    HtmlWidget(
                                                      Utils.htmlDocument(provider
                                                                  .getCouponListModel
                                                                  ?.data?[index]
                                                                  .description ??
                                                              "")
                                                          .outerHtml,
                                                      textStyle: AppTextStyle
                                                          .poppinsBold13(
                                                              context: context,
                                                              fontWeight:
                                                                  FontWeight
                                                                      .w400,
                                                              color: AppColors
                                                                  .lightText),
                                                    ),
                                                    SizedBox(
                                                      height: height / 120,
                                                    ),
                                                    // Text(
                                                    //   "Save 1.9 with this code",
                                                    //   style: AppTextStyle
                                                    //       .poppinsMedium15(
                                                    //           context: context,
                                                    //           color: AppColors
                                                    //               .primaryColor),
                                                    // ),
                                                    SizedBox(
                                                      height: height / 120,
                                                    ),
                                                  ],
                                                ))
                                              ],
                                            ),
                                          ),
                                        ],
                                      )),
                                ),
                              );
                            },
                          )
                  ],
                );
        },
      ),
    );
  }
}
