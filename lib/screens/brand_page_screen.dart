import 'package:apni_factory/provider/brand_screen_provider.dart';
import 'package:apni_factory/provider/cart_screen_provider.dart';
import 'package:apni_factory/provider/homeScreen_provider.dart';
import 'package:apni_factory/res/asset_image.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/widgets/brand_search_widget.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:apni_factory/res/widgets/global_cart_container.dart';
import 'package:apni_factory/res/widgets/search_widget.dart';
import 'package:apni_factory/screens/category_screen.dart';
import 'package:apni_factory/screens/company_page_screen.dart';
import 'package:apni_factory/screens/description_page_screen.dart';
import 'package:apni_factory/screens/product_category_page_screen.dart';
import 'package:apni_factory/utils.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../res/textstyle.dart';

class BrandPageScreen extends StatefulWidget {
  final int cmpid;
  final int cid;
  const BrandPageScreen({super.key, required this.cmpid, required this.cid});

  @override
  State<BrandPageScreen> createState() => _BrandPageScreenState();
}

class _BrandPageScreenState extends State<BrandPageScreen> {
// @override
// void dispose() {
//   _brandProvider.clearSearch();
//   super.dispose();
// }

  @override
  void initState() {
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      await Provider.of<BrandScreenProvider>(context, listen: false)
          .brandScreen(context: context, cmpid: widget.cmpid, cid: widget.cid);
      await Provider.of<CartScreenProvider>(context, listen: false)
          .userCart(context: context);
    });

    // TODO: implement initState
    super.initState();
  }

  late BrandScreenProvider _brandProvider;
  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    _brandProvider = Provider.of<BrandScreenProvider>(context, listen: false);
  }

  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return WillPopScope(
      onWillPop: () async {
        _brandProvider.clearSearch(); // ✅ SAFE
        return true;
      },
      child: Scaffold(
        body: Consumer<BrandScreenProvider>(
          builder: (context, provider, child) {
            return provider.loadingBrandScreen
                ? CircularProgressIndicatorWidget()
                : Stack(
                    children: [
                      ListView(
                        children: [
                          provider.searchModel != null
                              ? Container(
                                  height: height / 6, // or any minimal height
                                  width: width,
                                  color: Colors.pink[30], // ✅ white background
                                  child: Column(
                                    children: [
                                      SizedBox(height: height / 26.6),
                                      BrandSearchWidget(
                                        provider:
                                            Provider.of<BrandScreenProvider>(
                                                context),
                                      ),
                                      SizedBox(height: height / 42.1),
                                    ],
                                  ),
                                )
                              : Container(
                                  height: height / 3,
                                  width: width,
                                  child: Stack(
                                    children: [
                                      Image.network(
                                        "${AppAssetImage.imageBaseUrl}storage/app/public/${(provider.brandScreenModel?.data?.bannerimage ?? "")}",
                                        fit: BoxFit.fill,
                                        height: height / 2,
                                        width: width,
                                      ),
                                      Column(
                                        children: [
                                          SizedBox(
                                            height: height / 26.6,
                                          ),
                                          BrandSearchWidget(
                                              provider: Provider.of<
                                                      BrandScreenProvider>(
                                                  context)),
                                          SizedBox(
                                            height: height / 42.1,
                                          ),
                                        ],
                                      )
                                    ],
                                  ),
                                ),
                          SizedBox(
                            height: height / 29,
                          ),
                          Provider.of<BrandScreenProvider>(context,
                                          listen: false)
                                      .searchModel ==
                                  null
                              ? Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Padding(
                                      padding: EdgeInsets.symmetric(
                                          horizontal: width / 16.2),
                                      child: Text(
                                        "Category Name  >   ${provider.brandScreenModel?.data?.companyname ?? ""}",
                                        textAlign: TextAlign.left,
                                        style: AppTextStyle.poppinsRegular14(
                                            context: context,
                                            fontWeight: FontWeight.w600,
                                            color: AppColors.secondaryColor),
                                      ),
                                    ),
                                    SizedBox(
                                      height: height / 50,
                                    ),
                                    provider.brandScreenModel?.data?.brand ==
                                                null ||
                                            provider.brandScreenModel!.data!
                                                .brand!.isEmpty
                                        ? Padding(
                                            padding: EdgeInsets.symmetric(
                                                horizontal: width / 16.2),
                                            child: Text(
                                              "There are no brand to explore in this company",
                                              textAlign: TextAlign.center,
                                              style: AppTextStyle.poppinsBold13(
                                                  context: context,
                                                  fontWeight: FontWeight.w500,
                                                  color:
                                                      AppColors.primaryColor),
                                            ),
                                          )
                                        : GridView.builder(
                                            gridDelegate:
                                                SliverGridDelegateWithFixedCrossAxisCount(
                                                    childAspectRatio: 1,
                                                    crossAxisCount: 2),
                                            // padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                                            physics:
                                                NeverScrollableScrollPhysics(),
                                            itemCount: provider.brandScreenModel
                                                ?.data?.brand?.length,
                                            shrinkWrap: true,
                                            itemBuilder: (context, index) {
                                              return InkWell(
                                                onTap: () {
                                                  Navigator.push(context,
                                                      MaterialPageRoute(
                                                    builder: (context) {
                                                      return ProductCategoryPageScreen(
                                                        bid: provider
                                                                .brandScreenModel
                                                                ?.data
                                                                ?.brand?[index]
                                                                .id ??
                                                            0,
                                                        cid: provider
                                                                .brandScreenModel
                                                                ?.data
                                                                ?.brand?[index]
                                                                .categoryId ??
                                                            0,
                                                      );
                                                    },
                                                  ));
                                                },
                                                child: Padding(
                                                  padding: EdgeInsets.all(
                                                      height / 60),
                                                  child: Column(
                                                    children: [
                                                      Stack(
                                                        children: [
                                                          Container(
                                                            height: height / 8, 
                                                            width: width / 3.2,
                                                            decoration: BoxDecoration(
                                                                borderRadius:
                                                                    BorderRadius.circular(
                                                                        height /
                                                                            60),
                                                                image: DecorationImage(
                                                                    image: NetworkImage(
                                                                        "${AppAssetImage.imageBaseUrl}storage/app/public/${(provider.brandScreenModel?.data?.brand?[index].image ?? "")}"),
                                                                    fit: BoxFit
                                                                        .cover)),
                                                          ),
                                                          Container(
                                                              height:
                                                                  height / 40,
                                                              width:
                                                                  height / 40,
                                                              decoration: BoxDecoration(
                                                                  color: Colors
                                                                      .white,
                                                                  borderRadius:
                                                                      BorderRadius.circular(
                                                                          height /
                                                                              150)),
                                                              child: provider
                                                                          .brandScreenModel
                                                                          ?.data
                                                                          ?.brand?[
                                                                              index]
                                                                          .type ==
                                                                      "Processing"
                                                                  ? Center(
                                                                      child:
                                                                          Text(
                                                                        "PR",
                                                                        style: AppTextStyle.poppinsBold11(
                                                                            context:
                                                                                context,
                                                                            color:
                                                                                AppColors.primaryColor),
                                                                      ),
                                                                    )
                                                                  : provider
                                                                              .brandScreenModel
                                                                              ?.data
                                                                              ?.brand?[index]
                                                                              .type ==
                                                                          "Unregistred"
                                                                      ? Center(
                                                                          child:
                                                                              Text(
                                                                            "UR",
                                                                            style:
                                                                                AppTextStyle.poppinsBold11(context: context, color: AppColors.primaryColor),
                                                                          ),
                                                                        )
                                                                      : Center(
                                                                          child:
                                                                              Text(
                                                                            "RG",
                                                                            style:
                                                                                AppTextStyle.poppinsBold11(context: context, color: AppColors.primaryColor),
                                                                          ),
                                                                        )),
                                                        ],
                                                      ),
                                                      SizedBox(
                                                        height: height / 60,
                                                      ),
                                                      Expanded(
                                                        child: Text(
                                                          provider
                                                                  .brandScreenModel
                                                                  ?.data
                                                                  ?.brand?[
                                                                      index]
                                                                  .name ??
                                                              "",
                                                          textAlign:
                                                              TextAlign.left,
                                                          style: AppTextStyle
                                                              .poppinsBold12(
                                                                  context:
                                                                      context,
                                                                  color: AppColors
                                                                      .blackColor,
                                                                  fontWeight:
                                                                      FontWeight
                                                                          .w600),
                                                        ),
                                                      )
                                                    ],
                                                  ),
                                                ),
                                              );
                                            },
                                          ),
                                    SizedBox(
                                      height: height / 7,
                                    ),
                                  ],
                                )
                              : ListView.builder(
                                  shrinkWrap: true,
                                  physics: NeverScrollableScrollPhysics(),
                                  itemCount:
                                      Provider.of<BrandScreenProvider>(context)
                                          .searchModel
                                          ?.data
                                          ?.length,
                                  itemBuilder: (context, index) {
                                    var searchModel =
                                        Provider.of<BrandScreenProvider>(
                                                context)
                                            .searchModel;
                                    return Padding(
                                      padding: EdgeInsets.symmetric(
                                          horizontal: width / 16.2,
                                          vertical: height / 60),
                                      child: InkWell(
                                        onTap: () {
                                          if (searchModel
                                                  ?.data?[index].screen ==
                                              "category") {
                                            Navigator.push(context,
                                                MaterialPageRoute(
                                              builder: (context) {
                                                return CategoryScreen(
                                                    mid: searchModel
                                                            ?.data?[index]
                                                            .relatedid ??
                                                        0);
                                              },
                                            ));
                                          } else if (searchModel
                                                  ?.data?[index].screen ==
                                              "company") {
                                            Navigator.push(context,
                                                MaterialPageRoute(
                                              builder: (context) {
                                                return CompanyPageScreen(
                                                    cid: searchModel
                                                            ?.data?[index]
                                                            .relatedid ??
                                                        0);
                                              },
                                            ));
                                          } else if (searchModel
                                                  ?.data?[index].screen ==
                                              "product") {
                                            Navigator.push(context,
                                                MaterialPageRoute(
                                              builder: (context) {
                                                return DescriptionPageScreen(
                                                    productId: searchModel
                                                            ?.data?[index].id ??
                                                        0,
                                                    colorId: searchModel
                                                            ?.data?[index]
                                                            .relatedid
                                                            .toString() ??
                                                        "");
                                              },
                                            ));
                                          } else {
                                            Utils.commonSnackBar(context,
                                                height: height,
                                                message:
                                                    "Search categroy, company and product");
                                          }
                                        },
                                        child: Row(
                                          children: [
                                            Container(
                                              height: height / 20,
                                              width: height / 20,
                                              decoration: BoxDecoration(
                                                borderRadius:
                                                    BorderRadius.circular(
                                                        height / 60),
                                                image: DecorationImage(
                                                  image: NetworkImage(
                                                      "${AppAssetImage.imageBaseUrl}storage/app/public/${(searchModel?.data?[index].image ?? "")}"),
                                                  fit: BoxFit.fill,
                                                ),
                                              ),
                                            ),
                                            SizedBox(
                                              width: width / 20,
                                            ),
                                            Text(
                                                searchModel
                                                        ?.data?[index].name ??
                                                    "",
                                                style: AppTextStyle
                                                    .poppinsMedium15(
                                                        context: context,
                                                        color: AppColors
                                                            .secondaryColor)),
                                          ],
                                        ),
                                      ),
                                    );
                                  },
                                )
                        ],
                      ),
                      Provider.of<BrandScreenProvider>(context, listen: false)
                                  .searchModel ==
                              null
                          ? GlobalCartContainer(width: width, height: height)
                          : SizedBox.shrink()
                    ],
                  );
          },
        ),
      ),
    );
  }
}
