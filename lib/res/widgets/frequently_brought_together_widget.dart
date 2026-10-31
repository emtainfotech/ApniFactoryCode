import 'package:apni_factory/provider/product_detail_provider.dart';
import 'package:apni_factory/res/asset_image.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/screens/description_page_screen.dart';
import 'package:apni_factory/screens/product_category_page_screen.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';

class FrequentlyBroughtTogetherWidget extends StatelessWidget {
  final double width;
  final double height;
  final ProductDetailProvider provider;
  const FrequentlyBroughtTogetherWidget(
      {super.key,
      required this.width,
      required this.height,
      required this.provider});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.all(height / 90),
      child: Container(
          height: height / 2.8,
          width: width,
          decoration: BoxDecoration(
              color: AppColors.whiteColor,
              borderRadius: BorderRadius.circular(height / 30),
              boxShadow: [BoxShadow()]),
          child: Padding(
            padding: EdgeInsets.symmetric(horizontal: height / 55),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                SizedBox(
                  height: height / 30,
                ),
                Text(
                  "Frequently Brought Together",
                  style: AppTextStyle.poppinsRegular16(
                      context: context,
                      color: AppColors.secondaryColor,
                      fontWeight: FontWeight.w500),
                ),
                SizedBox(
                  height: height / 35,
                ),
                Expanded(
                  child: ListView.builder(
                    scrollDirection: Axis.horizontal,
                    itemCount: provider
                        .productDetailModel?.data?.relatedproductlist?.length,
                    shrinkWrap: true,
                    itemBuilder: (context, index) {
                      return InkWell(
                        onTap: () {
                          Navigator.push(context, MaterialPageRoute(
                            builder: (context) {
                              return DescriptionPageScreen(
                                  productId: provider.productDetailModel?.data
                                          ?.relatedproductlist?[index].id ??
                                      0,
                                  colorId: provider
                                          .productDetailModel
                                          ?.data
                                          ?.relatedproductlist?[index]
                                          .colorid ??
                                      "");
                            },
                          ));
                        },
                        child: Padding(
                          padding: EdgeInsets.only(
                              right: height / 50, bottom: height / 50),
                          child: SizedBox(width: width/3,
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Container(
                                  height: height / 8,
                                  width: width / 4,
                                  decoration: BoxDecoration(
                                      boxShadow: [BoxShadow()],
                                      borderRadius:
                                          BorderRadius.circular(height / 66.6),
                                      image: DecorationImage(
                                          image: NetworkImage(
                                              "${AppAssetImage.imageBaseUrl}storage/app/public/${(provider.productDetailModel?.data?.relatedproductlist?[index].image ?? "")}"),
                                          fit: BoxFit.cover,),),
                                ),
                                SizedBox(
                                  height: height / 75,
                                ),
                                Text(
                                    provider.productDetailModel?.data
                                            ?.relatedproductlist?[index].name ??
                                        "",
                                    style: AppTextStyle.poppinsRegular14(
                                        context: context,
                                        color: AppColors.secondaryColor,
                                        fontWeight: FontWeight.w600)),
                                SizedBox(
                                  height: height / 150,
                                ),
                                Expanded(
                                  child: Text(
                                      provider.productDetailModel?.data
                                              ?.relatedproductlist?[index].price
                                              .toString() ??
                                          "",
                                      style: AppTextStyle.poppinsBold13(
                                          context: context,
                                          color: AppColors.secondaryColor,
                                          fontWeight: FontWeight.w400)),
                                ),
                                // SizedBox(
                                //   height: height / 150,
                                // ),
                                // Text(
                                //     "₹ ${provider.productDetailModel?.data?.relatedproductlist?[index].price.toString() ?? ""}",
                                //     style: AppTextStyle.poppinsBold13(
                                //         context: context,
                                //         color: AppColors.secondaryColor,
                                //         fontWeight: FontWeight.w700)),
                              ],
                            ),
                          ),
                        ),
                      );
                    },
                  ),
                ),
                SizedBox(
                  height: height / 60,
                ),
              ],
            ),
          )),
    );
  }
}
