import 'package:apni_factory/provider/cart_screen_provider.dart';
import 'package:apni_factory/provider/product_detail_provider.dart';
import 'package:apni_factory/res/asset_image.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:apni_factory/res/widgets/frequently_brought_together_widget.dart';
import 'package:apni_factory/res/widgets/global_cart_container.dart';
import 'package:apni_factory/res/widgets/rating_review_widget.dart';
import 'package:apni_factory/res/widgets/round_button.dart';
import 'package:apni_factory/utils.dart';
import 'package:apni_factory/model/user_cart_model.dart';
import 'package:carousel_slider/carousel_slider.dart';
import 'package:flutter_widget_from_html/flutter_widget_from_html.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class DescriptionPageScreen extends StatefulWidget {
  final int productId;
  final String colorId;
  const DescriptionPageScreen(
      {super.key, required this.productId, required this.colorId});

  @override
  State<DescriptionPageScreen> createState() => _DescriptionPageScreenState();
}

class _DescriptionPageScreenState extends State<DescriptionPageScreen> {
  @override
  void initState() {
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      await Provider.of<ProductDetailProvider>(context, listen: false)
          .productDetail(
              context: context,
              productid: widget.productId.toString(),
              colorid: widget.colorId.toString());

      await Provider.of<CartScreenProvider>(context, listen: false)
          .userCart(context: context);
    });

    // TODO: implement initState\\
    super.initState();
  }

  @override
  Widget build(BuildContext globalContext) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Product Detail',
          style: AppTextStyle.poppinsRegular16(
              context: context,
              color: AppColors.secondaryColor,
              fontWeight: FontWeight.w500),
        ),
        leading: IconButton(
          icon: Icon(Icons.arrow_back),
          onPressed: () {
            // Reset provider state when going back

            Navigator.pop(context);
          },
        ),
      ),
      body: Consumer<ProductDetailProvider>(
        builder: (context, provider, child) {
          return provider.loadingproductDetail ||
                  provider.loadingAddToCart ||
                  provider.loadingAddToWishlist ||
                  Provider.of<CartScreenProvider>(context).loadingUserCart
              ? CircularProgressIndicatorWidget()
              : Stack(
                  children: [
                    ListView(
                      children: [
                        Padding(
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
                                      height: height / 60,
                                    ),
                                    provider.productDetailModel?.data
                                                ?.multipleimages ==
                                            null
                                        ? Center(
                                            child: ClipRRect(
                                              borderRadius:
                                                  BorderRadius.circular(
                                                      height / 66.6),
                                              child: Container(
                                                height: height / 4,
                                                width: width / 2,
                                                child: Image.network(
                                                  "${AppAssetImage.imageBaseUrl}storage/app/public/${(provider.productDetailModel?.data?.image)}",
                                                  fit: BoxFit.fill,
                                                ),
                                              ),
                                            ),
                                          )
                                        : CarouselSlider(
                                            items: provider.imageList
                                                .map((item) => Center(
                                                      child: ClipRRect(
                                                        borderRadius:
                                                            BorderRadius
                                                                .circular(
                                                                    height /
                                                                        66.6),
                                                        child: Container(
                                                          // height: height / 3.5,
                                                          width: width / 2,
                                                          child: Image.network(
                                                            "${AppAssetImage.imageBaseUrl}storage/app/public/${(item)}",
                                                            fit: BoxFit.cover,
                                                          ),
                                                        ),
                                                      ),
                                                    ))
                                                .toList(),
                                            carouselController: provider
                                                .carouselSliderController,
                                            options: CarouselOptions(
                                              onPageChanged: (index, reason) {
                                                setState(() {
                                                  provider.current = index;
                                                });
                                              },
                                              viewportFraction: 1,
                                              height: height / 2.8,
                                              autoPlay: true,
                                              enlargeCenterPage: false,
                                              // aspectRatio: 1,
                                            )),
                                    SizedBox(
                                      height: height / 29,
                                    ),
                                    Row(
                                      mainAxisAlignment:
                                          MainAxisAlignment.center,
                                      children: provider.imageList
                                          .asMap()
                                          .entries
                                          .map((entry) {
                                        return GestureDetector(
                                            onTap: () => provider
                                                .carouselSliderController
                                                .animateToPage(entry.key),
                                            child: Container(
                                              width:
                                                  provider.current == entry.key
                                                      ? 12
                                                      : 10,
                                              height:
                                                  provider.current == entry.key
                                                      ? 12
                                                      : 10,
                                              margin:
                                                  const EdgeInsets.symmetric(
                                                      horizontal: 3.0),
                                              decoration: BoxDecoration(
                                                  border: provider.current ==
                                                          entry.key
                                                      ? Border.all(
                                                          color: AppColors
                                                              .whiteColor)
                                                      : Border.all(
                                                          color: AppColors
                                                              .primaryColor),
                                                  shape: BoxShape.circle,
                                                  color: provider.current ==
                                                          entry.key
                                                      ? AppColors.primaryColor
                                                      : AppColors.whiteColor),
                                            ));
                                      }).toList(),
                                    ),
                                    SizedBox(
                                      height: height / 55,
                                    ),
                                    Row(
                                      mainAxisAlignment:
                                          MainAxisAlignment.spaceBetween,
                                      children: [
                                        Expanded(
                                          child: Text(
                                            provider.productDetailModel?.data
                                                    ?.name ??
                                                "",
                                            style:
                                                AppTextStyle.poppinsRegular16(
                                                    context: context,
                                                    fontWeight: FontWeight.w600,
                                                    color: AppColors
                                                        .secondaryColor),
                                          ),
                                        ),
                                        IconButton(
                                          onPressed: () {
                                            provider.addToWishList(
                                              context: context,
                                              height: height,
                                              width: width,
                                              productid: provider
                                                      .productDetailModel
                                                      ?.data
                                                      ?.productId
                                                      .toString() ??
                                                  "",
                                              color: provider.productDetailModel
                                                  ?.data?.attributes?[0].color,
                                            );
                                          },
                                          icon: provider.productDetailModel
                                                      ?.data?.wishlist ==
                                                  false
                                              ? Icon(Icons
                                                  .favorite_outline_rounded)
                                              : Icon(Icons.favorite_rounded),
                                          iconSize: height / 35,
                                          color: AppColors.primaryColor,
                                        )
                                      ],
                                    ),
                                    Row(
                                      children: [
                                        Text(
                                          "(${provider.productDetailModel?.data?.attributes?[0].color ?? ""})",
                                          style: AppTextStyle.poppinsRegular14(
                                              context: context,
                                              fontWeight: FontWeight.w500,
                                              color: AppColors.secondaryColor),
                                        ),
                                        //  Container(
                                        //                       height: height / 25,
                                        //                       width: height / 25,
                                        //                       decoration: provider
                                        //                                   .productDetails?[
                                        //                                       index]
                                        //                                   .hexcode ==
                                        //                               null
                                        //                           ? BoxDecoration(
                                        //                               image: DecorationImage(
                                        //                                   image: NetworkImage(
                                        //                                   "${AppAssetImage.imageBaseUrl}storage/app/public/${( provider
                                        //                                       .productDetails?[
                                        //                                           index]
                                        //                                       .hexcodeImage ?? "")}"
                                        //                                     ),fit: BoxFit.cover),
                                        //                               borderRadius:
                                        //                                   BorderRadius.circular(height /
                                        //                                       150))
                                        //                           : BoxDecoration(
                                        //                               color: hexToColor(
                                        //                                   provider.productDetails?[index].hexcode ??
                                        //                                       "#111111"),
                                        //                               borderRadius:
                                        //                                   BorderRadius.circular(height / 150)),
                                        //                     )
                                      ],
                                    ),
                                    SizedBox(
                                      height: height / 40,
                                    ),
                                    ListView.builder(
                                      shrinkWrap: true,
                                      physics: NeverScrollableScrollPhysics(),
                                      itemCount: provider.productDetailModel
                                              ?.data?.attributes?.length ??
                                          0,
                                      itemBuilder: (context, index) {
                                        final cartProvider =
                                            Provider.of<CartScreenProvider>(
                                                context,
                                                listen: false);

                                        // Find if this specific attribute is in cart
                                        bool isInCart = false;
                                        AttributeData? cartAttributeData;

                                        if (cartProvider.userCartModel?.data !=
                                            null) {
                                          for (var categoryList in cartProvider
                                              .userCartModel!.data.values) {
                                            for (var productList
                                                in categoryList) {
                                              for (var product in productList) {
                                                for (var attr
                                                    in product.attributeData) {
                                                  if (attr.productattributeid ==
                                                      provider
                                                          .productDetailModel
                                                          ?.data
                                                          ?.attributes?[index]
                                                          .attributeid) {
                                                    isInCart = true;
                                                    cartAttributeData = attr;
                                                    break;
                                                  }
                                                }
                                                if (isInCart) break;
                                              }
                                              if (isInCart) break;
                                            }
                                            if (isInCart) break;
                                          }
                                        }

                                        return Padding(
                                          padding: EdgeInsets.only(
                                              bottom: height / 60),
                                          child: Row(
                                            // crossAxisAlignment:
                                            //     CrossAxisAlignment.center,
                                            mainAxisAlignment:
                                                MainAxisAlignment.spaceBetween,
                                            children: [
                                              Expanded(
                                                flex: 3,
                                                child: Column(
                                                  children: [
                                                    Text(
                                                        provider
                                                                .productDetailModel
                                                                ?.data
                                                                ?.attributes?[
                                                                    index]
                                                                .boxpacking
                                                                .toString() ??
                                                            "",
                                                        maxLines: 2,
                                                        overflow: TextOverflow
                                                            .ellipsis,
                                                        style: AppTextStyle
                                                            .poppinsBold13(
                                                                context:
                                                                    context,
                                                                color: AppColors
                                                                    .secondaryColor,
                                                                fontWeight:
                                                                    FontWeight
                                                                        .w600)),
                                                    Text(
                                                        "(${provider.productDetailModel?.data?.attributes?[index].quantity.toString() ?? ""} pcs)",
                                                        style: AppTextStyle
                                                            .poppinsBold12(
                                                                context:
                                                                    context,
                                                                color: AppColors
                                                                    .secondaryColor,
                                                                fontWeight:
                                                                    FontWeight
                                                                        .w500)),
                                                  ],
                                                ),
                                              ),
                                              // SizedBox(
                                              //   width: width / 10,
                                              // ),
                                              Expanded(
                                                flex: 2,
                                                child: Text(
                                                    "₹ ${provider.productDetailModel?.data?.attributes?[index].price ?? ""} / Pcs",
                                                    textAlign: TextAlign.end,
                                                    style: AppTextStyle
                                                        .poppinsBold13(
                                                            context: context,
                                                            color: AppColors
                                                                .secondaryColor,
                                                            fontWeight:
                                                                FontWeight
                                                                    .w600)),
                                              ),
                                              SizedBox(
                                                width: width / 10,
                                              ),

                                              // SizedBox(
                                              //   width: width / 30,
                                              // ),

                                              !isInCart
                                                  ? Expanded(
                                                      flex: 2,
                                                      child: Padding(
                                                        padding:
                                                            EdgeInsets.only(
                                                                left: 5.0),
                                                        child: RoundButton(
                                                          title: "Add",
                                                          backgroundColor:
                                                              AppColors
                                                                  .primaryColor,
                                                          onPressed: () {
                                                            Provider.of<ProductDetailProvider>(context, listen: false).addToCart(
                                                                context:
                                                                    context,
                                                                globalContext:
                                                                    globalContext,
                                                                height: height,
                                                                width: width,
                                                                productid: provider
                                                                        .productDetailModel
                                                                        ?.data
                                                                        ?.id
                                                                        .toString() ??
                                                                    "",
                                                                attributes: provider.getAttributesList(
                                                                    id: provider
                                                                            .productDetailModel
                                                                            ?.data
                                                                            ?.attributes?[
                                                                                index]
                                                                            .attributeid ??
                                                                        0),
                                                                brandid: provider
                                                                        .productDetailModel
                                                                        ?.data
                                                                        ?.brandId ??
                                                                    0);
                                                          },
                                                        ),
                                                      ),
                                                    )
                                                  : cartAttributeData != null
                                                      ? Container(
                                                          height: height / 25,
                                                          width: width / 4,
                                                          decoration: BoxDecoration(
                                                              color: AppColors
                                                                  .whiteColor,
                                                              boxShadow: [
                                                                BoxShadow()
                                                              ],
                                                              borderRadius:
                                                                  BorderRadius
                                                                      .circular(
                                                                          height /
                                                                              100),
                                                              border: Border.all(
                                                                  color: AppColors
                                                                      .stroke)),
                                                          child: Row(
                                                            mainAxisAlignment:
                                                                MainAxisAlignment
                                                                    .spaceEvenly,
                                                            children: [
                                                              InkWell(
                                                                onTap: () {
                                                                  Provider.of<CartScreenProvider>(context, listen: false).decreaseCartQuantity(
                                                                      context:
                                                                          context,
                                                                      height:
                                                                          height,
                                                                      cartid: cartAttributeData!
                                                                          .attributeid
                                                                          .toString(),
                                                                      qty: cartAttributeData!
                                                                          .qty
                                                                          .toString());
                                                                },
                                                                child: Text("-",
                                                                    style: AppTextStyle.poppinsRegular14(
                                                                        context:
                                                                            context)),
                                                              ),
                                                              Text(
                                                                  cartAttributeData!
                                                                      .qty
                                                                      .toString(),
                                                                  style: AppTextStyle.poppinsRegular14(
                                                                      context:
                                                                          context,
                                                                      fontWeight:
                                                                          FontWeight
                                                                              .w600)),
                                                              InkWell(
                                                                onTap: () {
                                                                  Provider.of<CartScreenProvider>(context, listen: false).increaseCartQuantity(
                                                                      context:
                                                                          context,
                                                                      height:
                                                                          height,
                                                                      cartid: cartAttributeData!
                                                                          .attributeid
                                                                          .toString(),
                                                                      qty: cartAttributeData!
                                                                          .qty
                                                                          .toString());
                                                                },
                                                                child: Text("+",
                                                                    style: AppTextStyle.poppinsRegular14(
                                                                        context:
                                                                            context)),
                                                              )
                                                            ],
                                                          ))
                                                      : SizedBox(),
                                            ],
                                          ),
                                        );
                                      },
                                    ),
                                    SizedBox(
                                      height: height / 75,
                                    ),
                                    SizedBox(
                                      height: height / 60,
                                    ),
                                  ],
                                ),
                              )),
                        ),
                        Padding(
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
                                      height: height / 40,
                                    ),
                                    Text(
                                      "Description",
                                      style: AppTextStyle.poppinsRegular16(
                                          context: context,
                                          color: AppColors.secondaryColor,
                                          fontWeight: FontWeight.w500),
                                    ),
                                    SizedBox(
                                      height: height / 50,
                                    ),
                                    HtmlWidget(
                                      Utils.htmlDocument(provider
                                                  .productDetailModel
                                                  ?.data
                                                  ?.description ??
                                              "")
                                          .outerHtml,
                                      textStyle: AppTextStyle.poppinsBold13(
                                          context: context,
                                          color: AppColors.secondaryColor,
                                          fontWeight: FontWeight.w500),
                                    ),
                                    // Text(
                                    //   provider.productDetailModel?.data?.description??"",
                                    //   style: AppTextStyle.poppinsRegular14(
                                    //       context: context),
                                    // ),
                                    SizedBox(
                                      height: height / 60,
                                    ),
                                  ],
                                ),
                              )),
                        ),
                        provider.productDetailModel?.data?.relatedproductlist ==
                                    null ||
                                provider.productDetailModel!.data!
                                    .relatedproductlist!.isEmpty
                            ? SizedBox.shrink()
                            : FrequentlyBroughtTogetherWidget(
                                width: width,
                                height: height,
                                provider: provider,
                              ),
                        Padding(
                          padding: EdgeInsets.all(height / 90),
                          child: Container(
                              height: height / 4,
                              width: width,
                              decoration: BoxDecoration(
                                  color: AppColors.whiteColor,
                                  borderRadius:
                                      BorderRadius.circular(height / 30),
                                  boxShadow: [BoxShadow()]),
                              child: Padding(
                                padding: EdgeInsets.symmetric(
                                    horizontal: height / 55),
                                child: Row(
                                  mainAxisAlignment:
                                      MainAxisAlignment.spaceBetween,
                                  children: [
                                    Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        SizedBox(
                                          height: height / 30,
                                        ),
                                        Text(
                                          "Seller Details",
                                          style: AppTextStyle.poppinsRegular16(
                                              context: context,
                                              color: AppColors.secondaryColor,
                                              fontWeight: FontWeight.w500),
                                        ),
                                        SizedBox(
                                          height: height / 35,
                                        ),
                                        Text(
                                          "Seller Name :",
                                          style: AppTextStyle.poppinsBold13(
                                              context: context,
                                              color: AppColors.lightText,
                                              fontWeight: FontWeight.w600),
                                        ),

                                        Text(
                                          "${provider.productDetailModel?.data?.sellerdetail?.name ?? ""}",
                                          style: AppTextStyle.poppinsBold13(
                                              context: context,
                                              color: AppColors.lightText,
                                              fontWeight: FontWeight.w500),
                                        ),
                                        SizedBox(
                                          height: height / 80,
                                        ),
                                        // Text(
                                        //   "Email : ${provider.productDetailModel?.data?.sellerdetail?.email ?? ""}",
                                        //   style: AppTextStyle.poppinsBold13(
                                        //       context: context,
                                        //       color: AppColors.lightText,
                                        //       fontWeight: FontWeight.w500),
                                        // ),
                                        // Text(
                                        //   "Mobile : ${provider.productDetailModel?.data?.sellerdetail?.mobile ?? ""}",
                                        //   style: AppTextStyle.poppinsBold13(
                                        //       context: context,
                                        //       color: AppColors.lightText,
                                        //       fontWeight: FontWeight.w500),
                                        // ),
                                        Text(
                                          "City :",
                                          style: AppTextStyle.poppinsBold13(
                                              context: context,
                                              color: AppColors.lightText,
                                              fontWeight: FontWeight.w600),
                                        ),
                                        Text(
                                          "${provider.productDetailModel?.data?.sellerdetail?.city ?? ""}",
                                          style: AppTextStyle.poppinsBold13(
                                              context: context,
                                              color: AppColors.lightText,
                                              fontWeight: FontWeight.w500),
                                        ),
                                        SizedBox(
                                          height: height / 60,
                                        ),
                                      ],
                                    ),
                                    SizedBox(
                                      width: width / 20,
                                    ),
                                    Container(
                                      height: height / 10,
                                      width: height / 9,
                                      decoration: BoxDecoration(
                                          image: DecorationImage(
                                              fit: BoxFit.fill,
                                              image: provider
                                                          .productDetailModel
                                                          ?.data
                                                          ?.sellerdetail
                                                          ?.photo ==
                                                      null
                                                  ? NetworkImage(
                                                      "https://uxwing.com/wp-content/themes/uxwing/download/peoples-avatars/person-profile-image-icon.png")
                                                  : NetworkImage(
                                                      "${AppAssetImage.imageBaseUrl}storage/app/public/${provider.productDetailModel?.data?.sellerdetail?.photo ?? ""}"))),
                                    )
                                  ],
                                ),
                              )),
                        ),
                        RatingReviewWidget(
                            width: width, height: height, provider: provider)
                      ],
                    ),
                    GlobalCartContainer(width: width, height: height),
                  ],
                );
        },
      ),
    );
  }
}
