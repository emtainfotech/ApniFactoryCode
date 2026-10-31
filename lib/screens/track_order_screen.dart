import 'dart:convert';
import 'dart:developer';
import 'package:apni_factory/provider/single_order_detail_provider.dart';
import 'package:apni_factory/res/asset_image.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/res/widgets/back_button_widget.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:apni_factory/res/widgets/round_button.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';

class TrackOrderScreen extends StatefulWidget {
  final orderid;
  const TrackOrderScreen({super.key, required this.orderid});

  @override
  State<TrackOrderScreen> createState() => _TrackOrderScreenState();
}

class _TrackOrderScreenState extends State<TrackOrderScreen> {
  @override
  void initState() {
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      log(widget.orderid);
      await Provider.of<SingleOrderDetailProvider>(context, listen: false)
          .singleOrderDetail(context: context, orderno: widget.orderid);
    });
    // TODO: implement initState
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(
        backgroundColor: AppColors.whiteColor,
        body: Consumer<SingleOrderDetailProvider>(
          builder: (context, provider, child) {
            return provider.loadingSingleOrderDetail
                ? CircularProgressIndicatorWidget()
                : Stack(
                    children: [
                      provider.singleOrderDetailModel?.data?.status?.last
                                      .status ==
                                  "rejected" ||
                              provider.singleOrderDetailModel?.data?.status
                                      ?.last.status ==
                                  "pending"
                          ? Center(
                              child: Transform.rotate(
                                angle: -0.5,
                                child: Opacity(
                                  opacity: 0.1,
                                  child: Text(
                                    provider.singleOrderDetailModel?.data
                                            ?.status?.last.status
                                            .toString().toUpperCase() ??
                                        "",
                                    style: TextStyle(
                                      fontSize: height / 12,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ),
                              ),
                            )
                          : SizedBox.shrink(),
                      ListView(
                        children: [
                          SizedBox(
                            height: height / 26.6,
                          ),
                          Padding(
                            padding:
                                EdgeInsets.symmetric(horizontal: width / 16.2),
                            child: BackButtonWidget(
                              title: "Order Summary",
                            ),
                          ),
                          SizedBox(
                            height: height / 46,
                          ),
                          Padding(
                              padding: EdgeInsets.symmetric(
                                  horizontal: width / 16.2),
                              child: Divider()),
                          SizedBox(
                            height: height / 46,
                          ),
                          Padding(
                            padding:
                                EdgeInsets.symmetric(horizontal: width / 16.2),
                            child: Text(
                              "Order Details",
                              style: AppTextStyle.poppinsRegular16(
                                  context: context,
                                  fontWeight: FontWeight.w600,
                                  color: AppColors.secondaryColor),
                            ),
                          ),
                          SizedBox(
                            height: height / 80,
                          ),
                          Padding(
                              padding: EdgeInsets.symmetric(
                                  horizontal: width / 16.2),
                              child: Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    "ORDER PLACED",
                                    style: AppTextStyle.poppinsBold13(
                                        context: context,
                                        fontWeight: FontWeight.w400,
                                        color: AppColors.secondaryColor),
                                  ),
                                  Text(
                                    formatDate(provider.singleOrderDetailModel
                                            ?.data?.createdAt ??
                                        ""),
                                    style: AppTextStyle.poppinsRegular14(
                                        context: context,
                                        fontWeight: FontWeight.w500,
                                        color: AppColors.secondaryColor),
                                  )
                                ],
                              )),
                          Padding(
                              padding: EdgeInsets.symmetric(
                                  horizontal: width / 16.2),
                              child: Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    "ORDER NUMBER",
                                    style: AppTextStyle.poppinsBold13(
                                        context: context,
                                        fontWeight: FontWeight.w400,
                                        color: AppColors.secondaryColor),
                                  ),
                                  Text(
                                    provider.singleOrderDetailModel?.data
                                            ?.orderno ??
                                        "",
                                    style: AppTextStyle.poppinsRegular14(
                                        context: context,
                                        fontWeight: FontWeight.w500,
                                        color: AppColors.secondaryColor),
                                  )
                                ],
                              )),
                          Padding(
                              padding: EdgeInsets.symmetric(
                                  horizontal: width / 16.2),
                              child: Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    "Total",
                                    style: AppTextStyle.poppinsBold13(
                                        context: context,
                                        fontWeight: FontWeight.w400,
                                        color: AppColors.secondaryColor),
                                  ),
                                  Text(
                                    "₹ ${provider.singleOrderDetailModel?.data?.grandtotal ?? ""}",
                                    style: AppTextStyle.poppinsRegular14(
                                        context: context,
                                        fontWeight: FontWeight.w500,
                                        color: AppColors.secondaryColor),
                                  )
                                ],
                              )),
                          SizedBox(
                            height: height / 30,
                          ),
                          ListView.builder(
                            shrinkWrap: true,
                            physics: NeverScrollableScrollPhysics(),
                            itemCount: provider.singleOrderDetailModel?.data
                                    ?.status?.length ??
                                0,
                            itemBuilder: (context, index) {
                              return Padding(
                                padding: EdgeInsets.symmetric(
                                    horizontal: width / 16.2,
                                    vertical: height / 100),
                                child: Row(
                                  children: [
                                    Icon(
                                      Icons.check_circle,
                                      color:
                                          const Color.fromARGB(255, 10, 84, 13),
                                    ),
                                    SizedBox(
                                      width: width / 30,
                                    ),
                                    Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          provider.singleOrderDetailModel?.data
                                                  ?.status?[index].status.toString().toUpperCase() ??
                                              "",
                                          style: AppTextStyle.poppinsRegular16(
                                              context: context,
                                              fontWeight: FontWeight.w500,
                                              color: AppColors.secondaryColor),
                                        ),
                                        Text(
                                          provider.singleOrderDetailModel?.data
                                                  ?.status?[index].msg ??
                                              "",
                                          style: AppTextStyle.poppinsBold12(
                                              context: context,
                                              fontWeight: FontWeight.w500,
                                              color: AppColors.lightText),
                                        ),
                                        SizedBox(
                                          height: height / 80,
                                        ),
                                        Text(
                                          formatDate(provider
                                                  .singleOrderDetailModel
                                                  ?.data
                                                  ?.status?[index]
                                                  .createdAt ??
                                              ""),
                                          style: AppTextStyle.poppinsRegular14(
                                              context: context,
                                              fontWeight: FontWeight.w500,
                                              color: AppColors.lightText),
                                        ),
                                      ],
                                    )
                                  ],
                                ),
                              );
                            },
                          ),
                          SizedBox(
                            height: height / 46,
                          ),
                          Padding(
                              padding: EdgeInsets.symmetric(
                                  horizontal: width / 16.2),
                              child: Divider()),
                          Padding(
                            padding: EdgeInsets.symmetric(
                                horizontal: width / 16.2, vertical: width / 70),
                            child: Card(
                              child: Container(
                                width: width,
                                decoration: BoxDecoration(
                                    boxShadow: [
                                      BoxShadow(
                                          blurRadius: 5,
                                          color: AppColors.blackColor
                                              .withOpacity(0.1))
                                    ],
                                    // border: Border.all(color: AppColors.primaryColor),
                                    borderRadius:
                                        BorderRadius.circular(height / 66.6),
                                    color: AppColors.whiteColor),
                                child: ListView.builder(
                                  shrinkWrap: true,
                                  physics: NeverScrollableScrollPhysics(),
                                  itemCount: provider.singleOrderDetailModel
                                          ?.data?.orderdetail?.length ??
                                      0,
                                  itemBuilder: (context, index) {
                                    return Padding(
                                      padding: EdgeInsets.symmetric(
                                        horizontal: width / 20,
                                      ),
                                      child: Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        children: [
                                          // SizedBox(
                                          //   height: height / 60,
                                          // ),
                                          SizedBox(
                                            height: height / 70,
                                          ),
                                          Column(
                                            crossAxisAlignment:
                                                CrossAxisAlignment.start,
                                            children: [
                                              Row(
                                                children: [
                                                  Container(
                                                    height: height / 19,
                                                    width: width / 10,
                                                    decoration: BoxDecoration(
                                                      boxShadow: [
                                                        BoxShadow(
                                                            blurRadius: 32,
                                                            color: AppColors
                                                                .blackColor
                                                                .withOpacity(
                                                                    0.1))
                                                      ],
                                                      border: Border.all(
                                                          color: AppColors
                                                              .greyColor),
                                                      borderRadius:
                                                          BorderRadius.circular(
                                                              height / 66.6),
                                                      color:
                                                          AppColors.whiteColor,
                                                    ),
                                                    child: Padding(
                                                      padding: EdgeInsets.all(
                                                          height / 200),
                                                      child: Center(
                                                        child: Image.network(
                                                          "${AppAssetImage.imageBaseUrl}storage/app/public/${(provider.singleOrderDetailModel?.data?.orderdetail?[index].productimage)}",
                                                          // height: height / 30,
                                                        ),
                                                      ),
                                                    ),
                                                  ),
                                                  SizedBox(
                                                    width: width / 30,
                                                  ),
                                                  Expanded(
                                                    child: Column(
                                                      crossAxisAlignment:
                                                          CrossAxisAlignment
                                                              .start,
                                                      children: [
                                                        Text(
                                                          provider
                                                                  .singleOrderDetailModel
                                                                  ?.data
                                                                  ?.orderdetail?[
                                                                      index]
                                                                  .productname ??
                                                              "",
                                                          style: AppTextStyle
                                                              .poppinsRegular14(
                                                                  context:
                                                                      context,
                                                                  fontWeight:
                                                                      FontWeight
                                                                          .w600,
                                                                  color: AppColors
                                                                      .secondaryColor),
                                                        ),
                                                        Text(
                                                          provider
                                                                  .singleOrderDetailModel
                                                                  ?.data
                                                                  ?.orderdetail?[
                                                                      index]
                                                                  .brdcmpcat ??
                                                              "",
                                                          style: AppTextStyle
                                                              .poppinsSemibold10(
                                                                  context:
                                                                      context,
                                                                  color: AppColors
                                                                      .lightText,
                                                                  fontWeight:
                                                                      FontWeight
                                                                          .w500),
                                                        ),
                                                      ],
                                                    ),
                                                  ),
                                                ],
                                              ),
                                              SizedBox(
                                                height: height / 120,
                                              ),
                                              Padding(
                                                padding: EdgeInsets.only(
                                                    bottom: height / 60),
                                                child: Row(
                                                  mainAxisAlignment:
                                                      MainAxisAlignment
                                                          .spaceBetween,
                                                  children: [
                                                    Column(
                                                      children: [
                                                        Text("Size",
                                                            style: AppTextStyle.poppinsBold13(
                                                                context:
                                                                    context,
                                                                color: AppColors
                                                                    .secondaryColor,
                                                                fontWeight:
                                                                    FontWeight
                                                                        .w600)),
                                                      ],
                                                    ),
                                                    SizedBox(
                                                      width: width / 10,
                                                    ),
                                                    Text("Quantity",
                                                        style: AppTextStyle
                                                            .poppinsBold13(
                                                                context:
                                                                    context,
                                                                color: AppColors
                                                                    .secondaryColor,
                                                                fontWeight:
                                                                    FontWeight
                                                                        .w600)),
                                                    SizedBox(
                                                      width: width / 10,
                                                    ),
                                                    Text("Price",
                                                        style: AppTextStyle
                                                            .poppinsBold13(
                                                                context:
                                                                    context,
                                                                color: AppColors
                                                                    .secondaryColor,
                                                                fontWeight:
                                                                    FontWeight
                                                                        .w600)),
                                                    SizedBox(
                                                      width: width / 10,
                                                    ),
                                                  ],
                                                ),
                                              ),
                                              ListView.builder(
                                                shrinkWrap: true,
                                                physics:
                                                    NeverScrollableScrollPhysics(),
                                                itemCount: provider
                                                        .singleOrderDetailModel
                                                        ?.data
                                                        ?.orderdetail?[index]
                                                        .attribute
                                                        ?.length ??
                                                    0,
                                                itemBuilder:
                                                    (context, attrindex) {
                                                  return Padding(
                                                    padding: EdgeInsets.only(
                                                        bottom: height / 60),
                                                    child: Row(
                                                      mainAxisAlignment:
                                                          MainAxisAlignment
                                                              .spaceBetween,
                                                      children: [
                                                        Column(
                                                          children: [
                                                            Row(
                                                              children: [
                                                                Text(
                                                                    "${provider.singleOrderDetailModel?.data?.orderdetail?[index].attribute?[attrindex].boxpacking ?? ""} * ${provider.singleOrderDetailModel?.data?.orderdetail?[index].attribute?[attrindex].boxpcs.toString() ?? ""}",
                                                                    style: AppTextStyle.poppinsBold13(
                                                                        context:
                                                                            context,
                                                                        color: AppColors
                                                                            .secondaryColor,
                                                                        fontWeight:
                                                                            FontWeight.w500)),
                                                              ],
                                                            ),
                                                          ],
                                                        ),
                                                        SizedBox(
                                                          width: width / 10,
                                                        ),
                                                        Text(
                                                            provider
                                                                    .singleOrderDetailModel
                                                                    ?.data
                                                                    ?.orderdetail?[
                                                                        index]
                                                                    .attribute?[
                                                                        attrindex]
                                                                    .qty
                                                                    .toString() ??
                                                                "",
                                                            style: AppTextStyle.poppinsBold13(
                                                                context:
                                                                    context,
                                                                color: AppColors
                                                                    .secondaryColor,
                                                                fontWeight:
                                                                    FontWeight
                                                                        .w500)),
                                                        SizedBox(
                                                          width: width / 10,
                                                        ),
                                                        Text(
                                                            "₹ ${provider.singleOrderDetailModel?.data?.orderdetail?[index].attribute?[attrindex].totalprice ?? ""}",
                                                            style: AppTextStyle.poppinsBold13(
                                                                context:
                                                                    context,
                                                                color: AppColors
                                                                    .secondaryColor,
                                                                fontWeight:
                                                                    FontWeight
                                                                        .w500)),
                                                        SizedBox(
                                                          width: width / 10,
                                                        ),
                                                      ],
                                                    ),
                                                  );
                                                },
                                              )
                                            ],
                                          ),

                                          Divider(),
                                        ],
                                      ),
                                    );
                                  },
                                ),
                              ),
                            ),
                          ),
                          SizedBox(
                            height: height / 80,
                          ),
                          Padding(
                            padding:
                                EdgeInsets.symmetric(horizontal: width / 16.2),
                            child: Divider(),
                          ),
                          SizedBox(
                            height: height / 46,
                          ),
                          Padding(
                            padding:
                                EdgeInsets.symmetric(horizontal: width / 16.2),
                            child: Text(
                              "Ship to",
                              style: AppTextStyle.poppinsRegular16(
                                  context: context,
                                  fontWeight: FontWeight.w600,
                                  color: AppColors.secondaryColor),
                            ),
                          ),
                          SizedBox(
                            height: height / 80,
                          ),
                          Padding(
                            padding:
                                EdgeInsets.symmetric(horizontal: width / 16.2),
                            child: Text(
                              formatAddress(
                                provider.singleOrderDetailModel?.data
                                        ?.address ??
                                    "",
                              ),
                              style: AppTextStyle.poppinsRegular14(
                                  context: context,
                                  color: AppColors.secondaryColor,
                                  fontWeight: FontWeight.w400),
                            ),
                          ),
                          SizedBox(
                            height: height / 80,
                          ),
                          Padding(
                            padding:
                                EdgeInsets.symmetric(horizontal: width / 16.2),
                            child: Divider(),
                          ),
                          SizedBox(
                            height: height / 46,
                          ),
                          Padding(
                            padding:
                                EdgeInsets.symmetric(horizontal: width / 16.2),
                            child: Text(
                              "Order Summary",
                              style: AppTextStyle.poppinsRegular16(
                                  context: context,
                                  fontWeight: FontWeight.w600,
                                  color: AppColors.secondaryColor),
                            ),
                          ),
                          SizedBox(
                            height: height / 80,
                          ),
                          Padding(
                            padding:
                                EdgeInsets.symmetric(horizontal: width / 16.2),
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
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      SizedBox(
                                        height: height / 30,
                                      ),
                                      Row(
                                        mainAxisAlignment:
                                            MainAxisAlignment.spaceBetween,
                                        children: [
                                          Text(
                                            "Item(s) Subtotal",
                                            style: AppTextStyle.poppinsBold13(
                                                context: context,
                                                color: AppColors.secondaryColor,
                                                fontWeight: FontWeight.w500),
                                          ),
                                          Text(
                                            "₹ ${provider.singleOrderDetailModel?.data?.netamount ?? ""}",
                                            style: AppTextStyle.poppinsBold13(
                                                context: context,
                                                color: AppColors.secondaryColor,
                                                fontWeight: FontWeight.w500),
                                          ),
                                        ],
                                      ),
                                      SizedBox(
                                        height: height / 120,
                                      ),
                                      Row(
                                        mainAxisAlignment:
                                            MainAxisAlignment.spaceBetween,
                                        children: [
                                          Text(
                                            "Tax Amount",
                                            style: AppTextStyle.poppinsBold13(
                                                context: context,
                                                color: AppColors.secondaryColor,
                                                fontWeight: FontWeight.w500),
                                          ),
                                          Text(
                                            "₹ +${provider.singleOrderDetailModel?.data?.taxamount ?? ""}",
                                            style: AppTextStyle.poppinsBold13(
                                                context: context,
                                                color: AppColors.secondaryColor,
                                                fontWeight: FontWeight.w500),
                                          ),
                                        ],
                                      ),
                                      SizedBox(
                                        height: height / 120,
                                      ),
                                      Row(
                                        mainAxisAlignment:
                                            MainAxisAlignment.spaceBetween,
                                        children: [
                                          Text(
                                            "Coupon Amount \n(Seller + Admin)",
                                            style: AppTextStyle.poppinsBold13(
                                                context: context,
                                                color: AppColors.secondaryColor,
                                                fontWeight: FontWeight.w500),
                                          ),
                                          Text(
                                            "- ₹ (${provider.singleOrderDetailModel?.data?.sellercouponamount.toString() ?? ""} + ${provider.singleOrderDetailModel?.data?.admincouponamount.toString()}) ",
                                            style: AppTextStyle.poppinsBold13(
                                                context: context,
                                                color: AppColors.secondaryColor,
                                                fontWeight: FontWeight.w500),
                                          ),
                                        ],
                                      ),
                                      SizedBox(
                                        height: height / 120,
                                      ),
                                      Row(
                                        mainAxisAlignment:
                                            MainAxisAlignment.spaceBetween,
                                        children: [
                                          Text(
                                            "Total",
                                            style: AppTextStyle.poppinsBold13(
                                                context: context,
                                                color: AppColors.secondaryColor,
                                                fontWeight: FontWeight.w500),
                                          ),
                                          Text(
                                            "₹ ${provider.singleOrderDetailModel?.data?.grandtotal ?? ""}",
                                            style: AppTextStyle.poppinsBold13(
                                                context: context,
                                                color: AppColors.secondaryColor,
                                                fontWeight: FontWeight.w500),
                                          ),
                                        ],
                                      ),
                                      Divider(),
                                      Row(
                                        mainAxisAlignment:
                                            MainAxisAlignment.spaceBetween,
                                        children: [
                                          Text(
                                            "Grand Total",
                                            style: AppTextStyle.poppinsBold13(
                                                context: context,
                                                color: AppColors.secondaryColor,
                                                fontWeight: FontWeight.w600),
                                          ),
                                          Text(
                                            "₹ ${provider.singleOrderDetailModel?.data?.grandtotal ?? ""}",
                                            style: AppTextStyle.poppinsBold13(
                                                context: context,
                                                color: AppColors.secondaryColor,
                                                fontWeight: FontWeight.w600),
                                          ),
                                        ],
                                      ),
                                      SizedBox(
                                        height: height / 30,
                                      ),
                                    ],
                                  ),
                                )),
                          ),
                          SizedBox(
                            height: height / 80,
                          ),
                          Padding(
                            padding:
                                EdgeInsets.symmetric(horizontal: width / 16.2),
                            child: Divider(),
                          ),
                          SizedBox(
                            height: height / 46,
                          ),
                          Padding(
                            padding:
                                EdgeInsets.symmetric(horizontal: width / 16.2),
                            child: Text(
                              "Transaction",
                              style: AppTextStyle.poppinsRegular16(
                                  context: context,
                                  fontWeight: FontWeight.w600,
                                  color: AppColors.secondaryColor),
                            ),
                          ),
                          SizedBox(
                            height: height / 80,
                          ),
                          Padding(
                              padding: EdgeInsets.symmetric(
                                  horizontal: width / 16.2),
                              child: Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    "Id",
                                    style: AppTextStyle.poppinsBold13(
                                        context: context,
                                        fontWeight: FontWeight.w400,
                                        color: AppColors.secondaryColor),
                                  ),
                                  Text(
                                    provider.singleOrderDetailModel?.data
                                            ?.transection?.txnid ??
                                        "",
                                    style: AppTextStyle.poppinsRegular14(
                                        context: context,
                                        fontWeight: FontWeight.w500,
                                        color: AppColors.secondaryColor),
                                  )
                                ],
                              )),
                          Padding(
                              padding: EdgeInsets.symmetric(
                                  horizontal: width / 16.2),
                              child: Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    "Status",
                                    style: AppTextStyle.poppinsBold13(
                                        context: context,
                                        fontWeight: FontWeight.w400,
                                        color: AppColors.secondaryColor),
                                  ),
                                  Text(
                                    provider.singleOrderDetailModel?.data
                                            ?.transection?.status ??
                                        "",
                                    style: AppTextStyle.poppinsRegular14(
                                        context: context,
                                        fontWeight: FontWeight.w500,
                                        color: AppColors.secondaryColor),
                                  )
                                ],
                              )),
                          SizedBox(
                            height: height / 80,
                          ),
                          Padding(
                            padding:
                                EdgeInsets.symmetric(horizontal: width / 16.2),
                            child: Text(
                              formatTxnDetail(
                                provider.singleOrderDetailModel?.data
                                        ?.transection?.txndetail ??
                                    "",
                              ),
                              style: AppTextStyle.poppinsRegular14(
                                  context: context,
                                  color: AppColors.secondaryColor,
                                  fontWeight: FontWeight.w400),
                            ),
                          ),
                          Padding(
                            padding:
                                EdgeInsets.symmetric(horizontal: width / 16.2),
                            child: Divider(),
                          ),
                          SizedBox(
                            height: height / 46,
                          ),
                          Padding(
                            padding:
                                EdgeInsets.symmetric(horizontal: width / 16.2),
                            child: Text(
                              "Track Details",
                              style: AppTextStyle.poppinsRegular16(
                                  context: context,
                                  fontWeight: FontWeight.w600,
                                  color: AppColors.secondaryColor),
                            ),
                          ),
                          SizedBox(
                            height: height / 80,
                          ),
                          Padding(
                              padding: EdgeInsets.symmetric(
                                  horizontal: width / 16.2),
                              child: Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    "Trans name",
                                    style: AppTextStyle.poppinsBold13(
                                        context: context,
                                        fontWeight: FontWeight.w400,
                                        color: AppColors.secondaryColor),
                                  ),
                                  Text(
                                    provider.singleOrderDetailModel?.data?.track
                                            ?.transname ??
                                        "",
                                    style: AppTextStyle.poppinsRegular14(
                                        context: context,
                                        fontWeight: FontWeight.w500,
                                        color: AppColors.secondaryColor),
                                  )
                                ],
                              )),
                          Padding(
                              padding: EdgeInsets.symmetric(
                                  horizontal: width / 16.2),
                              child: Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    "Contact",
                                    style: AppTextStyle.poppinsBold13(
                                        context: context,
                                        fontWeight: FontWeight.w400,
                                        color: AppColors.secondaryColor),
                                  ),
                                  Text(
                                    provider.singleOrderDetailModel?.data?.track
                                            ?.transcontact
                                            .toString() ??
                                        "",
                                    style: AppTextStyle.poppinsRegular14(
                                        context: context,
                                        fontWeight: FontWeight.w500,
                                        color: AppColors.secondaryColor),
                                  )
                                ],
                              )),
                          Padding(
                              padding: EdgeInsets.symmetric(
                                  horizontal: width / 16.2),
                              child: Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    "Lr No",
                                    style: AppTextStyle.poppinsBold13(
                                        context: context,
                                        fontWeight: FontWeight.w400,
                                        color: AppColors.secondaryColor),
                                  ),
                                  Text(
                                    provider.singleOrderDetailModel?.data?.track
                                            ?.lrno
                                            .toString() ??
                                        "",
                                    style: AppTextStyle.poppinsRegular14(
                                        context: context,
                                        fontWeight: FontWeight.w500,
                                        color: AppColors.secondaryColor),
                                  )
                                ],
                              )),
                          SizedBox(
                            height: height / 80,
                          ),
                          Padding(
                            padding:
                                EdgeInsets.symmetric(horizontal: width / 16.2),
                            child: Divider(),
                          ),
                          SizedBox(
                            height: height / 46,
                          ),
                          provider.singleOrderDetailModel?.data?.invoiceurl ==
                                      null ||
                                  provider.singleOrderDetailModel!.data!
                                      .invoiceurl!.isEmpty
                              ? SizedBox.shrink()
                              : Row(
                                  children: [
                                    Padding(
                                      padding:
                                          EdgeInsets.only(left: width / 16.2),
                                      child: Text(
                                        "Invoice",
                                        style: AppTextStyle.poppinsRegular16(
                                            context: context,
                                            fontWeight: FontWeight.w600,
                                            color: AppColors.secondaryColor),
                                      ),
                                    ),
                                    SizedBox(
                                      width: width / 3,
                                    ),
                                    Expanded(
                                      child: Padding(
                                        padding: EdgeInsets.only(
                                            right: width / 16.2),
                                        child: RoundButton(
                                          title: "Download",
                                          onPressed: () {
                                            openInvoice(
                                              provider.singleOrderDetailModel
                                                      ?.data?.invoiceurl ??
                                                  '',
                                            );
                                          },
                                        ),
                                      ),
                                    )
                                  ],
                                ),
                          SizedBox(
                            height: height / 30,
                          ),
                        ],
                      )
                    ],
                  );
          },
        ));
  }
}

String formatDate(String date) {
  if (date.isEmpty) return "";
  try {
    return DateTime.parse(date).toLocal().toString().split(' ')[0];
  } catch (e) {
    return "";
  }
}

Map<String, dynamic> parseAddress(String address) {
  return jsonDecode(address);
}

String formatAddress(String addressString) {
  if (addressString.isEmpty || addressString == "0") {
    return "Address not available";
  }

  try {
    final address = jsonDecode(addressString);

    return '''
${address['name']} (${address['identityname']})
${address['landmark1']}
${address['landmark2']}
${address['city']}, ${address['state']} - ${address['pincode']}
${address['country']}
Phone: ${address['phoneno']}
''';
  } catch (e) {
    return "Address not available";
  }
}

Map<String, dynamic> parseTxnDetail(String txnDetail) {
  return jsonDecode(txnDetail);
}

String formatTxnDetail(String txnDetail) {
  if (txnDetail.isEmpty || txnDetail == "0") {
    return "Transaction details not available";
  }

  try {
    final txn = jsonDecode(txnDetail);

    return '''
Payment Source: ${txn['payment_source']}

''';
  } catch (e) {
    return "Transaction details not available";
  }
}

Future<void> openInvoice(String url) async {
  final Uri uri = Uri.parse(url);

  if (await canLaunchUrl(uri)) {
    await launchUrl(
      uri,
      mode: LaunchMode.externalApplication,
    );
  } else {
    throw Exception('Could not launch $url');
  }
}
