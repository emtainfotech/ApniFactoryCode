import 'package:apni_factory/provider/single_order_detail_provider.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:apni_factory/res/widgets/round_button.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../provider/order_history_provider.dart';

class OrderNotificationScreen extends StatefulWidget {
  final orderid;
  const OrderNotificationScreen({super.key, this.orderid});

  @override
  State<OrderNotificationScreen> createState() =>
      _OrderNotificationScreenState();
}

class _OrderNotificationScreenState extends State<OrderNotificationScreen> {
  @override
  void initState() {
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
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
        appBar: AppBar(
          leading: Icon(Icons.arrow_back_ios),
        ),
        body: Consumer<SingleOrderDetailProvider>(
          builder: (context, provider, child) {
            return provider.loadingSingleOrderDetail
                ? CircularProgressIndicatorWidget()
                : Padding(
                    padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              "Order Information",
                              style: AppTextStyle.poppinsMedium20(
                                  context: context,
                                  fontWeight: FontWeight.w600),
                            ),
                            Text(
                              "Id : ${widget.orderid}",
                              style: AppTextStyle.poppinsRegular14(
                                  context: context,
                                  color: AppColors.secondaryColor),
                            )
                          ],
                        ),
                        SizedBox(height: height/24,),
                        Text(
                          "Delivery to",
                          style: AppTextStyle.poppinsRegular16(
                              context: context,
                              color: AppColors.secondaryColor,
                              fontWeight: FontWeight.w500),
                        ),
                        Text(provider.fullAddress ?? "", style: AppTextStyle.poppinsBold13(
                                  context: context,
                                  color: AppColors.secondaryColor,fontWeight: FontWeight.w600),),
                                                          SizedBox(height: height/24,),
                                                         Row(
                                    children: [
                                      // Container(
                                      //   height: height / 10,
                                      //   width: width / 7,
                                      //   decoration: BoxDecoration(
                                      //       boxShadow: [
                                      //         BoxShadow(
                                      //             blurRadius: 32,
                                      //             color: AppColors.blackColor
                                      //                 .withOpacity(0.1))
                                      //       ],
                                      //       border: Border.all(
                                      //           color: AppColors.greyColor),
                                      //       borderRadius: BorderRadius.circular(
                                      //           height / 66.6),
                                      //       color: AppColors.whiteColor,),
                                      //   child: Padding(
                                      //     padding: EdgeInsets.all(height/200),
                                      //     child: Center(
                                      //       child: Image.network(
                                      //        provider.singleOrderDetailModel.data.,
                                      //         // height: height / 30,
                                      //       ),
                                      //     ),
                                      //   ),
                                      // ),
                                      SizedBox(
                                        width: width / 30,
                                      ),
                                      Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            provider.singleOrderDetailModel?.data?.orderdetail?[0].productname??"",
                                            style:
                                                AppTextStyle.poppinsRegular16(
                                                    context: context,
                                                    fontWeight: FontWeight.w600,
                                                    color: AppColors
                                                        .secondaryColor),
                                          ),
                                          Text(
                                            "Detail",
                                            style:
                                                AppTextStyle.poppinsSemibold10(
                                                    context: context,
                                                    color: AppColors.lightText,
                                                    fontWeight:
                                                        FontWeight.w500),
                                          ),
                                          SizedBox(
                                            height: height / 120,
                                          ),
                                          Text(
                                            "MRP   ₹${ provider.singleOrderDetailModel?.data?.grandtotal}",
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
                                     // Column(
                                      //   crossAxisAlignment: CrossAxisAlignment.end,
                                      //   children: [
                                      //     Container(
                                      //       height: height / 47,
                                      //       width: width / 7,
                                      //       decoration:
                                      //           BoxDecoration(color: AppColors.stroke),
                                      //       child: Center(
                                      //           child: Text(
                                      //         "Delivered",
                                      //         style: AppTextStyle.poppinsSemibold10(
                                      //             context: context,
                                      //             color: AppColors.lightText,
                                      //             fontWeight: FontWeight.w500),
                                      //       )),
                                      //     ),
                                      //     SizedBox(
                                      //       height: height / 80,
                                      //     ),
                                      //     Text(
                                      //       "View activity >",
                                      //       style: AppTextStyle.poppinsSemibold10(
                                      //           context: context,
                                      //           color: AppColors.primaryColor),
                                      //     )
                                      //   ],
                                      // )
                                    ],
                                  ),
                        Center(
                            child: Text(
                          "Your order number ${widget.orderid} is pending for delivery.",
                          textAlign: TextAlign.center,
                          style: AppTextStyle.poppinsMedium20(context: context),
                        )),
                        SizedBox(
                          height: height / 20,
                        ),
                        Padding(
                          padding: EdgeInsets.symmetric(horizontal: width / 4),
                          child: RoundButton(
                            title: "Track Order",
                            onPressed: () {},
                          ),
                        )
                      ],
                    ),
                  );
          },
        ));
  }
}
