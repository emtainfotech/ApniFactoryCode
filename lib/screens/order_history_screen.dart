import 'package:apni_factory/model/order_histort_model.dart';
import 'package:apni_factory/provider/order_history_provider.dart';
import 'package:apni_factory/res/asset_image.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:apni_factory/res/widgets/round_button.dart';
import 'package:apni_factory/screens/track_order_screen.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../res/colors.dart';
import '../res/textstyle.dart';
import '../res/widgets/back_button_widget.dart';
import '../utils.dart';

class OrderHistoryScreen extends StatefulWidget {
  const OrderHistoryScreen({super.key});

  @override
  State<OrderHistoryScreen> createState() => _OrderHistoryScreenState();
}

class _OrderHistoryScreenState extends State<OrderHistoryScreen> {
  @override
  void initState() {
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      await Provider.of<OrderHistoryProvider>(context, listen: false)
          .orderHistory(context: context);
    });
    // TODO: implement initState
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(body: Consumer<OrderHistoryProvider>(
      builder: (context, provider, child) {
        return provider.loadingOrderHistory
            ? CircularProgressIndicatorWidget()
            : provider.orderHistoryModel?.data == null ||
                    provider.orderHistoryModel!.data!.isEmpty
                ? Padding(
                    padding: EdgeInsets.symmetric(horizontal: width / 20),
                    child: Column(
                      children: [
                        SizedBox(
                          height: height / 26.6,
                        ),
                        Padding(
                          padding:
                              EdgeInsets.symmetric(horizontal: width / 16.2),
                          child: InkWell(
                            onTap: () {
                              Navigator.pop(context);
                            },
                            child: BackButtonWidget(
                              title: "Order History",
                            ),
                          ),
                        ),
                        SizedBox(
                          height: height / 3,
                        ),
                        Text(
                          "Your Order History is empty",
                          style: AppTextStyle.poppinsMedium20(
                              context: context, fontWeight: FontWeight.w600),
                        ),
                        SizedBox(
                          height: height / 90,
                        ),
                        Text(
                          "To see your orders purchase items",
                          style: AppTextStyle.poppinsMedium15(
                              context: context,
                              color: AppColors.secondaryColor),
                          textAlign: TextAlign.center,
                        ),
                      ],
                    ),
                  )
                : ListView(
                    children: [
                      SizedBox(
                        height: height / 26.6,
                      ),
                      Padding(
                        padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                        child: BackButtonWidget(
                          title: "Order History",
                        ),
                      ),
                      SizedBox(
                        height: height / 23.5,
                      ),
                      // Padding(
                      //   padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                      //   child: Utils.commonTextFormField(context,
                      //       height: height,
                      //       width: width,
                      //       labelText: "Search",
                      //       suffixIcon: Icon(
                      //         Icons.search,
                      //         color: AppColors.lightText,
                      //       )),
                      // ),
                      // SizedBox(
                      //   height: height / 23.5,
                      // ),
                      ListView.builder(
                        itemCount:
                            provider.orderHistoryModel?.data?.length ?? 0,
                        shrinkWrap: true,
                        physics: NeverScrollableScrollPhysics(),
                        itemBuilder: (context, index) {
                          return Padding(
                            padding: EdgeInsets.symmetric(
                                horizontal: width / 16.2, vertical: width / 70),
                            child: Card(
                              child: Container(
                                width: width,
                                decoration: BoxDecoration(
                                    boxShadow: [
                                      BoxShadow(
                                          blurRadius: 32,
                                          color: AppColors.blackColor
                                              .withOpacity(0.1))
                                    ],
                                    // border: Border.all(color: AppColors.primaryColor),
                                    borderRadius:
                                        BorderRadius.circular(height / 66.6),
                                    color: AppColors.whiteColor),
                                child: Padding(
                                  padding: EdgeInsets.symmetric(
                                    horizontal: width / 20,
                                  ),
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      SizedBox(
                                        height: height / 60,
                                      ),
                                      Row(
                                        mainAxisAlignment:
                                            MainAxisAlignment.spaceBetween,
                                        children: [
                                          Column(
                                            crossAxisAlignment:
                                                CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                "ORDER NUMBER",
                                                style:
                                                    AppTextStyle.poppinsBold11(
                                                        context: context,
                                                        fontWeight:
                                                            FontWeight.w400,
                                                        color: AppColors
                                                            .secondaryColor),
                                              ),
                                              SizedBox(
                                                height: height / 180,
                                              ),
                                              Text(
                                                provider
                                                        .orderHistoryModel
                                                        ?.data?[index]
                                                        .orderno ??
                                                    "",
                                                style:
                                                    AppTextStyle.poppinsBold11(
                                                        context: context,
                                                        fontWeight:
                                                            FontWeight.w500,
                                                        color: AppColors
                                                            .secondaryColor),
                                              ),
                                            ],
                                          ),
                                          InkWell(
                                              onTap: () {
                                                Navigator.push(context,
                                                    MaterialPageRoute(
                                                  builder: (context) {
                                                    return TrackOrderScreen(
                                                      orderid: provider
                                                              .orderHistoryModel
                                                              ?.data?[index]
                                                              .orderno ??
                                                          "",
                                                    );
                                                  },
                                                ));
                                              },
                                              child: Icon(
                                                Icons.arrow_forward_ios,
                                                size: height / 50,
                                              ))
                                        ],
                                      ),
                                      SizedBox(
                                        height: height / 70,
                                      ),
                                      Row(
                                        children: [
                                          Icon(Icons.delivery_dining),
                                          SizedBox(
                                            width: width / 30,
                                          ),
                                          Column(
                                            crossAxisAlignment:
                                                CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                provider
                                                                .orderHistoryModel
                                                                ?.data?[index]
                                                                .orderstatus !=
                                                            null &&
                                                        provider
                                                            .orderHistoryModel!
                                                            .data![index]
                                                            .orderstatus!
                                                            .isNotEmpty
                                                    ? (provider
                                                                .orderHistoryModel!
                                                                .data![index]
                                                                .orderstatus![0]
                                                                .status ??
                                                            "")
                                                        .toUpperCase()
                                                    : "PENDING",
                                                style:
                                                    AppTextStyle.poppinsBold13(
                                                  context: context,
                                                  fontWeight: FontWeight.w500,
                                                  color:
                                                      AppColors.secondaryColor,
                                                ),
                                              ),
                                              if (provider
                                                          .orderHistoryModel
                                                          ?.data?[index]
                                                          .orderstatus !=
                                                      null &&
                                                  provider
                                                      .orderHistoryModel!
                                                      .data![index]
                                                      .orderstatus!
                                                      .isNotEmpty)
                                                Text(
                                                  provider
                                                          .orderHistoryModel!
                                                          .data![index]
                                                          .orderstatus![0]
                                                          .msg ??
                                                      "",
                                                  style: AppTextStyle
                                                      .poppinsBold12(
                                                    context: context,
                                                    fontWeight: FontWeight.w500,
                                                    color: AppColors.lightText,
                                                  ),
                                                ),
                                              SizedBox(height: height / 80),
                                              Text(
                                                formatDate(
                                                  provider
                                                          .orderHistoryModel
                                                          ?.data?[index]
                                                          .createdAt ??
                                                      "",
                                                ),
                                                style:
                                                    AppTextStyle.poppinsBold12(
                                                  context: context,
                                                  fontWeight: FontWeight.w500,
                                                  color: AppColors.lightText,
                                                ),
                                              ),
                                            ],
                                          ),
                                          Spacer(),
                                          Text(
                                            "MRP\n₹${provider.orderHistoryModel?.data?[index].grandtotal}",
                                            textAlign: TextAlign.right,
                                            style:
                                                AppTextStyle.poppinsRegular14(
                                                    context: context,
                                                    color: AppColors
                                                        .secondaryColor,
                                                    fontWeight:
                                                        FontWeight.w500),
                                          ),
                                        ],
                                      ),

                                      SizedBox(
                                        height: height / 70,
                                      ),
                                      // Row(
                                      //   children: [
                                      //     Container(
                                      //       height: height / 15,
                                      //       width: width / 10,
                                      //       decoration: BoxDecoration(
                                      //         boxShadow: [
                                      //           BoxShadow(
                                      //               blurRadius: 32,
                                      //               color: AppColors.blackColor
                                      //                   .withOpacity(0.1))
                                      //         ],
                                      //         border: Border.all(
                                      //             color: AppColors.greyColor),
                                      //         borderRadius:
                                      //             BorderRadius.circular(
                                      //                 height / 66.6),
                                      //         color: AppColors.whiteColor,
                                      //       ),
                                      //       child: Padding(
                                      //         padding:
                                      //             EdgeInsets.all(height / 200),
                                      //         child: Center(
                                      //           child: Image.network(
                                      //        "${AppAssetImage.imageBaseUrl}storage/app/public/${(provider.orderHistoryModel?.data?[index].orderdetail?[0].productimage)}"    ,
                                      //             // height: height / 30,
                                      //           ),
                                      //         ),
                                      //       ),
                                      //     ),
                                      //     SizedBox(
                                      //       width: width / 30,
                                      //     ),
                                      //     Column(
                                      //       crossAxisAlignment:
                                      //           CrossAxisAlignment.start,
                                      //       children: [
                                      //         Text(
                                      //           provider
                                      //                   .orderHistoryModel
                                      //                   ?.data?[index]
                                      //                   .orderdetail?[0]
                                      //                   .productname ??
                                      //               "",
                                      //           style: AppTextStyle
                                      //               .poppinsRegular14(
                                      //                   context: context,
                                      //                   fontWeight:
                                      //                       FontWeight.w600,
                                      //                   color: AppColors
                                      //                       .secondaryColor),
                                      //         ),

                                      //         SizedBox(
                                      //           height: height / 120,
                                      //         ),
                                      //         Text(
                                      //           "MRP   ₹${provider.orderHistoryModel?.data?[index].grandtotal}",
                                      //           style: AppTextStyle
                                      //               .poppinsRegular14(
                                      //                   context: context,
                                      //                   color: AppColors
                                      //                       .secondaryColor,
                                      //                   fontWeight:
                                      //                       FontWeight.w500),
                                      //         ),
                                      //       ],
                                      //     ),
                                      //     // Column(
                                      //     //   crossAxisAlignment: CrossAxisAlignment.end,
                                      //     //   children: [
                                      //     //     Container(
                                      //     //       height: height / 47,
                                      //     //       width: width / 7,
                                      //     //       decoration:
                                      //     //           BoxDecoration(color: AppColors.stroke),
                                      //     //       child: Center(
                                      //     //           child: Text(
                                      //     //         "Delivered",
                                      //     //         style: AppTextStyle.poppinsSemibold10(
                                      //     //             context: context,
                                      //     //             color: AppColors.lightText,
                                      //     //             fontWeight: FontWeight.w500),
                                      //     //       )),
                                      //     //     ),
                                      //     //     SizedBox(
                                      //     //       height: height / 80,
                                      //     //     ),
                                      //     //     Text(
                                      //     //       "View activity >",
                                      //     //       style: AppTextStyle.poppinsSemibold10(
                                      //     //           context: context,
                                      //     //           color: AppColors.primaryColor),
                                      //     //     )
                                      //     //   ],
                                      //     // )
                                      //   ],
                                      // ),
                                      // SizedBox(
                                      //   height: height / 60,
                                      // ),
                                      // Divider(),
                                      // InkWell(
                                      //   onTap: () {},
                                      //   child: Text(
                                      //     "Exchange/Return available till 17 June ",
                                      //     style: AppTextStyle.poppinsRegular14(
                                      //         context: context,
                                      //         color: AppColors.neutralGrey),
                                      //   ),
                                      // ),
                                      // Divider(),
                                      // SizedBox(
                                      //   height: height / 60,
                                      // ),
                                      // Row(
                                      //   children: [
                                      //     Text(
                                      //       "28 Aug 2023 at 5:20PM",
                                      //       style: AppTextStyle.poppinsRegular14(
                                      //           context: context),
                                      //     ),
                                      //     Spacer(),
                                      //     Text(
                                      //       "23.00",
                                      //       style: AppTextStyle.poppinsRegular14(
                                      //           context: context,
                                      //           fontWeight: FontWeight.w600),
                                      //     ),
                                      //   ],
                                      // ),

                                      // SizedBox(
                                      //   height: height / 50,
                                      // ),
                                      // RoundButton(
                                      //   title: "Return",
                                      //   onPressed: () {},
                                      // ),
                                      SizedBox(
                                        height: height / 40,
                                      )
                                    ],
                                  ),
                                ),
                              ),
                            ),
                          );
                        },
                      ),
                      SizedBox(
                        height: height / 23.3,
                      ),
                    ],
                  );
      },
    ));
  }
}

String formatDate(String date) {
  return DateTime.parse(date).toLocal().toString().split(' ')[0];
}
