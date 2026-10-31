import 'package:apni_factory/provider/cart_screen_provider.dart';
import 'package:apni_factory/provider/product_category_list_screen_provider.dart';
import 'package:apni_factory/res/asset_image.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:apni_factory/res/widgets/global_cart_container.dart';
import 'package:apni_factory/screens/description_page_screen.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class ProductCategoryPageScreen extends StatefulWidget {
  final int bid;
  final int cid;
  const ProductCategoryPageScreen({
    super.key,
    required this.bid,
    required this.cid,
  });

  @override
  State<ProductCategoryPageScreen> createState() =>
      _ProductCategoryPageScreenState();
}

class _ProductCategoryPageScreenState extends State<ProductCategoryPageScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadData();
    });
  }

  Future<void> _loadData() async {
    final provider =
        Provider.of<ProductCategoryListScreenProvider>(context, listen: false);
    final cartProvider =
        Provider.of<CartScreenProvider>(context, listen: false);

    // Reset the provider state
    provider.resetState();

    // Load fresh data
    await provider.productCategoryListScreen(
      context: context,
      bid: widget.bid,
      cid: widget.cid,
    );

    
    await cartProvider.userCart(context: context);
  }

  // final List<String> categories = ['Electronics', 'Clothing', 'Groceries'];

  // int _current = 0;

  // int currentIndex = 0;

  // Initially selected category
  // String selectedCategory = 'Electronics';

  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(
        appBar: AppBar(
          title: Text(
            'Categories & Products',
            style: AppTextStyle.poppinsRegular16(
                context: context,
                color: AppColors.secondaryColor,
                fontWeight: FontWeight.w500),
          ),
          leading: IconButton(
            icon: Icon(Icons.arrow_back),
            onPressed: () {
              // Reset provider state when going back
              Provider.of<ProductCategoryListScreenProvider>(context,
                      listen: false)
                  .resetState();
              Navigator.pop(context);
            },
          ),
        ),
        body: Consumer<ProductCategoryListScreenProvider>(
          builder: (context, provider, child) {
            return provider.loadingProductCategoryListScreen
                ? CircularProgressIndicatorWidget()
                : Stack(
                    children: [
                      Row(
                        children: [
                          // Left side: Categories List
                          provider.productCategoryListScreenModel?.data
                                          ?.subcategory ==
                                      null ||
                                  provider.productCategoryListScreenModel!.data!
                                      .subcategory!.isEmpty
                              ? Expanded(
                                  child: Padding(
                                    padding: EdgeInsets.symmetric(
                                        horizontal: width / 16.2),
                                    child: Text(
                                      "No \n Category",
                                      style: AppTextStyle.poppinsBold13(
                                          context: context,
                                          color: AppColors.primaryColor,
                                          fontWeight: FontWeight.w500),
                                      textAlign: TextAlign.center,
                                    ),
                                  ),
                                )
                              : Expanded(
                                  flex: 1,
                                  child: ListView.builder(
                                    itemCount:
                                        provider.subCategoryDetails?.length,
                                    itemBuilder: (context, index) {
                                      print(
                                          "Category $index highlight: ${provider.subCategoryDetails?[index].highlight}");
                                      // final isSelected =
                                      //     provider.selectedCategoryIndex ==
                                      //         index;
                                      return ListTile(
                                        tileColor:
                                            // isSelected
                                            //     ? AppColors.lightPink
                                            //
                                            // :
                                            provider.subCategoryDetails?[index]
                                                        .highlight ==
                                                   true
                                                ? AppColors.whiteColor
                                                : AppColors.lightPink,
                                        title: Column(crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Container(
                                              height: height / 12,
                                              width: width / 7,
                                              decoration: BoxDecoration(
                                                image: DecorationImage(
                                                    image: NetworkImage(
                                                        "${AppAssetImage.imageBaseUrl}storage/app/public/${(provider.subCategoryDetails?[index].image ?? "")}"),
                                                    fit: BoxFit.fill),
                                              ),
                                            ),
                                            SizedBox(height: height / 60),
                                            Text(
                                              provider
                                                      .subCategoryDetails?[
                                                          index]
                                                      .name ??
                                                  "",
                                              style: AppTextStyle
                                                  .poppinsSemibold10(
                                                context: context,
                                                color: AppColors.secondaryColor,
                                                fontWeight: FontWeight.w500,
                                              ),
                                              // overflow: TextOverflow.ellipsis,
                                            ),
                                          ],
                                        ),
                                        selected: provider
                                                .productCategoryListScreenModel
                                                ?.data
                                                ?.subcategory?[index]
                                                .name ==
                                            provider.selectedCategory,
                                        onTap: () {
                                          provider.selectedSubCategory(
                                            index: index,
                                            context: context,
                                          );
                                        },
                                      );
                                    },
                                  ),
                                ),
                          VerticalDivider(
                            thickness: 3,
                            color: AppColors.stroke,
                          ),
                          // Right side: Products List
                          provider.productDetails == null ||
                                  provider.productDetails!.isEmpty
                              ? Expanded(
                                  child: Padding(
                                    padding: EdgeInsets.symmetric(
                                        horizontal: width / 16.2),
                                    child: Text(
                                      "No \n product",
                                      style: AppTextStyle.poppinsBold13(
                                          context: context,
                                          color: AppColors.primaryColor,
                                          fontWeight: FontWeight.w500),
                                      textAlign: TextAlign.center,
                                    ),
                                  ),
                                )
                              : Expanded(
                                  flex: 2,
                                  child: provider
                                          .loadingProductListBySubcategory
                                      ? CircularProgressIndicatorWidget()
                                      : provider.productDetails == null ||
                                              provider.productDetails!.isEmpty
                                          ? Center(
                                              child: Text(
                                              "There are no products in this category",
                                              textAlign: TextAlign.center,
                                              style: AppTextStyle.poppinsBold17(
                                                  context: context,
                                                  fontWeight: FontWeight.w500,
                                                  color: AppColors.blackColor),
                                            ))
                                          : GridView.builder(
                                              gridDelegate:
                                                  SliverGridDelegateWithFixedCrossAxisCount(
                                                      crossAxisCount: 2,
                                                      childAspectRatio: 0.4),
                                              itemCount: provider
                                                  .productDetails?.length,
                                              itemBuilder: (context, index) {
                                                return ListTile(
                                                  shape: RoundedRectangleBorder(
                                                      side: BorderSide(
                                                          color: AppColors
                                                              .stroke)),
                                                  title: InkWell(
                                                    onTap: () async {
                                                      await Navigator.push(
                                                          context,
                                                          MaterialPageRoute(
                                                        builder: (context) {
                                                          return DescriptionPageScreen(
                                                            productId: provider
                                                                    .productDetails?[
                                                                        index]
                                                                    .pid ??
                                                                0,
                                                            colorId: provider
                                                                    .productDetails?[
                                                                        index]
                                                                    .colorid ??
                                                                "",
                                                          );
                                                        },
                                                      ));
                                                    },
                                                    child: Column(
                                                      crossAxisAlignment:
                                                          CrossAxisAlignment
                                                              .start,
                                                      children: [
                                                        Stack(children: [
                                                          Container(
                                                            height: height / 8,
                                                            width: width / 2,
                                                            decoration:
                                                                BoxDecoration(
                                                                    color: AppColors
                                                                        .whiteColor,
                                                                    image:
                                                                        DecorationImage(
                                                                      image:
                                                                          NetworkImage(
                                                                        "${AppAssetImage.imageBaseUrl}storage/app/public/${(provider.productDetails?[index].image ?? "")}",
                                                                      ),
                                                                      fit: BoxFit
                                                                          .contain,
                                                                    ),
                                                                    borderRadius:
                                                                        BorderRadius.circular(height /
                                                                            80)),
                                                          ),
                                                          Container(
                                                            height: height / 25,
                                                            width: height / 25,
                                                            decoration: provider
                                                                        .productDetails?[
                                                                            index]
                                                                        .hexcode ==
                                                                    null
                                                                ? BoxDecoration(
                                                                    image: DecorationImage(
                                                                        image: NetworkImage(
                                                                        "${AppAssetImage.imageBaseUrl}storage/app/public/${( provider
                                                                            .productDetails?[
                                                                                index]
                                                                            .hexcodeImage ?? "")}"  
                                                                          ),fit: BoxFit.cover),
                                                                    borderRadius:
                                                                        BorderRadius.circular(height /
                                                                            150))
                                                                : BoxDecoration(
                                                                    color: hexToColor(
                                                                        provider.productDetails?[index].hexcode ??
                                                                            "#111111"),
                                                                    borderRadius:
                                                                        BorderRadius.circular(height / 150)),
                                                          )
                                                        ]),
                                                        SizedBox(
                                                            height:
                                                                height / 52),
                                                        Text(
                                                          provider
                                                                  .productDetails?[
                                                                      index]
                                                                  .name ??
                                                              "",
                                                               overflow: TextOverflow.ellipsis,
                                                               maxLines: 6,
                                                              
                                                          style: AppTextStyle
                                                              .poppinsBold12(
                                                                  context:
                                                                      context,
                                                                  fontWeight:
                                                                      FontWeight
                                                                          .w600,
                                                                  color: AppColors
                                                                      .secondaryColor),
                                                        ),
                                                        Divider(),
                                                        Text(
                                                          "₹ ${provider.productDetails?[index].price}",
                                                          style: AppTextStyle
                                                              .poppinsBold12(
                                                                  context:
                                                                      context,
                                                                  color: AppColors
                                                                      .secondaryColor,
                                                                  fontWeight:
                                                                      FontWeight
                                                                          .w600),
                                                        ),
                                                        SizedBox(
                                                          height: height / 20,
                                                        )
                                                      ],
                                                    ),
                                                  ),
                                                );
                                              },
                                            ),
                                ),
                        ],
                      ),
                      GlobalCartContainer(width: width, height: height)
                    ],
                  );
          },
        ));
  }

  Color hexToColor(String hex) {
    if (hex == null || hex.isEmpty) {
      return Colors.transparent; // or any default color
    }
    hex = hex.replaceFirst('#', '');
    if (hex.length == 6) hex = 'FF$hex';

    return Color(int.parse(hex, radix: 16));
  }
}
