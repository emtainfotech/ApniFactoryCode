import 'package:apni_factory/provider/product_detail_provider.dart';
import 'package:apni_factory/provider/wishlist_screen_provider.dart';
import 'package:apni_factory/res/asset_image.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../res/colors.dart';
import '../res/textstyle.dart';
import '../res/widgets/back_button_widget.dart';
import '../utils.dart';

class WishlistScreen extends StatefulWidget {
  const WishlistScreen({super.key});

  @override
  State<WishlistScreen> createState() => _WishlistScreenState();
}

class _WishlistScreenState extends State<WishlistScreen> {
  @override
  void initState() {
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      await Provider.of<WishlistProvider>(context, listen: false)
          .userWishList(context: context);
    });
    // TODO: implement initState
    super.initState();
  }

  @override
  Widget build(BuildContext dialogContext) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(
      body: Consumer<WishlistProvider>(
        builder: (context, provider, child) {
          return provider.loadingUserWishlist || provider.loadingDeleteWishlist
              ? CircularProgressIndicatorWidget()
              : provider.userWishlistModel == null ||
                      provider.userWishlistModel!.data!.isEmpty
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
                                title: "Wishlist",
                              ),
                            ),
                          ),
                          SizedBox(
                            height: height / 3,
                          ),
                          Text(
                            "Your Wishlist is empty",
                            style: AppTextStyle.poppinsMedium20(
                                context: context, fontWeight: FontWeight.w600),
                          ),
                          SizedBox(
                            height: height / 90,
                          ),
                          Text(
                            "Tap heart button to start searching your favourite items",
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
                          padding:
                              EdgeInsets.symmetric(horizontal: width / 16.2),
                          child: InkWell(
                            onTap: () {
                              Navigator.pop(context);
                            },
                            child: BackButtonWidget(
                              title: "Wishlist",
                            ),
                          ),
                        ),
                        SizedBox(
                          height: height / 23.5,
                        ),
                        ListView.builder(
                          itemCount: provider.userWishlistModel?.data?.length,
                          shrinkWrap: true,
                          physics: NeverScrollableScrollPhysics(),
                          itemBuilder: (context, index) {
                            return Stack(
                              children: [
                                Padding(
                                  padding: EdgeInsets.symmetric(
                                      horizontal: width / 16.2,
                                      vertical: width / 70),
                                  child: Card(
                                    child: Container(
                                      height: height / 8.5,
                                      width: width,
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
                                      child: Padding(
                                        padding: EdgeInsets.symmetric(
                                            horizontal: width / 20,
                                            vertical: width / 20),
                                        child: Row(
                                          mainAxisAlignment:
                                              MainAxisAlignment.spaceBetween,
                                          children: [
                                            InkWell(
                                              onTap: () {
                                                // Navigator.push(context, MaterialPageRoute(builder: (context) {
                                                //   return DescriptionPageScreen(productId: productId, colorId: colorId);
                                                // },));
                                              },
                                              child: Container(
                                                width: height / 15,
                                                height: height / 7,
                                                child: Image.network(
                                                  "${AppAssetImage.imageBaseUrl}storage/app/public/${(provider.userWishlistModel?.data?[index].image ?? "")}",
                                                  fit: BoxFit.fill,
                                                ),
                                              ),
                                            ),
                                            SizedBox(
                                              width: width / 20,
                                            ),
                                            Expanded(
                                              child: Column(
                                                crossAxisAlignment:
                                                    CrossAxisAlignment.start,
                                                mainAxisAlignment:
                                                    MainAxisAlignment
                                                        .spaceBetween,
                                                children: [
                                                  Text(
                                                    provider
                                                            .userWishlistModel
                                                            ?.data?[index]
                                                            .name ??
                                                        "",
                                                    overflow:
                                                        TextOverflow.ellipsis,
                                                    style: AppTextStyle
                                                        .poppinsRegular14(
                                                            context: context,
                                                            fontWeight:
                                                                FontWeight.w600,
                                                            color: AppColors
                                                                .secondaryColor),
                                                  ),
                                                  Row(
                                                    mainAxisAlignment:
                                                        MainAxisAlignment
                                                            .spaceBetween,
                                                    children: [
                                                      InkWell(
                                                        onTap: () {
                                                          final productDetailProvider =
                                                              context.read<
                                                                  ProductDetailProvider>();

                                                          final wishlistItem =
                                                              provider
                                                                  .userWishlistModel!
                                                                  .data![index];

                                                          List<
                                                                  Map<String,
                                                                      String>>
                                                              attributes =
                                                              wishlistItem
                                                                  .attribute!
                                                                  .map(
                                                                      (attr) =>
                                                                          {
                                                                            "id":
                                                                                attr.id.toString(),
                                                                            "qty":
                                                                                "1",
                                                                          })
                                                                  .toList();

                                                          productDetailProvider
                                                              .addToCart(
                                                            context: context,
                                                            globalContext:
                                                                context,
                                                            height: height,
                                                            width: width,
                                                            productid:
                                                                wishlistItem
                                                                    .productid
                                                                    .toString(),
                                                            brandid:
                                                                wishlistItem
                                                                    .brandid!,
                                                            attributes:
                                                                attributes,
                                                          );
                                                        },
                                                        child: Container(
                                                            height: height / 30,
                                                            padding: EdgeInsets
                                                                .symmetric(
                                                                    horizontal:
                                                                        width /
                                                                            30),
                                                            decoration: BoxDecoration(
                                                                color: AppColors
                                                                    .primaryColor,
                                                                borderRadius: BorderRadius.all(
                                                                    Radius.circular(
                                                                        height /
                                                                            30))),
                                                            alignment: Alignment
                                                                .center,
                                                            child: Text(
                                                              "Add to cart",
                                                              style: AppTextStyle.poppinsBold11(
                                                                  context:
                                                                      context,
                                                                  fontWeight:
                                                                      FontWeight
                                                                          .w500,
                                                                  color: AppColors
                                                                      .whiteColor),
                                                            )),
                                                      ),
                                                    ],
                                                  ),
                                                ],
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ),
                                  ),
                                ),
                                Padding(
                                  padding: EdgeInsets.symmetric(
                                      horizontal: width / 30),
                                  child: Align(
                                    alignment: Alignment.centerRight,
                                    child: InkWell(
                                      onTap: () {
                                        Utils.commonShowDialog(
                                          context: dialogContext,
                                          height: height,
                                          title:
                                              "Are you sure you want to remove this item from wishlist?",
                                          cancelButton: () {
                                            Navigator.pop(context);
                                          },
                                          okayButton: () {
                                            provider.deleteWishList(
                                                context: context,
                                                dialogContext: dialogContext,
                                                height: height,
                                                wishlistid: provider
                                                        .userWishlistModel
                                                        ?.data?[index]
                                                        .id
                                                        .toString() ??
                                                    "");
                                          },
                                        );
                                      },
                                      child: Container(
                                        height: height / 25,
                                        width: height / 25,
                                        decoration: BoxDecoration(
                                            shape: BoxShape.circle,
                                            color: AppColors.primaryColor),
                                        child: Icon(
                                          Icons.delete,
                                          size: height / 40,
                                          color: AppColors.whiteColor,
                                        ),
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                            );
                          },
                        ),
                        SizedBox(
                          height: height / 23.3,
                        ),
                      ],
                    );
        },
      ),
    );
  }
}
