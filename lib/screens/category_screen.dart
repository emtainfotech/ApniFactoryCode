import 'package:apni_factory/provider/cart_screen_provider.dart';
import 'package:apni_factory/provider/category_screen_provider.dart';
import 'package:apni_factory/provider/homeScreen_provider.dart';
import 'package:apni_factory/res/asset_image.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/res/widgets/category_search_widget.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:apni_factory/res/widgets/global_cart_container.dart';
import 'package:apni_factory/res/widgets/search_widget.dart';
import 'package:apni_factory/screens/brand_page_screen.dart';
import 'package:apni_factory/screens/company_page_screen.dart';
import 'package:apni_factory/screens/description_page_screen.dart';
import 'package:apni_factory/utils.dart';
import 'package:carousel_slider/carousel_slider.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class CategoryScreen extends StatefulWidget {
  final int mid;

  const CategoryScreen({
    super.key,
    required this.mid,
  });

  @override
  State<CategoryScreen> createState() => _CategoryScreenState();
}

class _CategoryScreenState extends State<CategoryScreen> {
  @override
  void initState() {
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      await Provider.of<CategoryScreenProvider>(context, listen: false)
          .categoryScreen(
        context: context,
        mid: widget.mid,
      );
      await Provider.of<CartScreenProvider>(context, listen: false)
          .userCart(context: context);
    });
    // TODO: implement initState
    super.initState();
  }

  late CategoryScreenProvider _categoryProvider;

  @override
  void didChangeDependencies() {
    _categoryProvider =
        Provider.of<CategoryScreenProvider>(context, listen: false);
    // TODO: implement didChangeDependencies
    super.didChangeDependencies();
  }

  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return WillPopScope(
      onWillPop: () async {
        _categoryProvider.clearSearch(); // ✅ SAFE
        return true;
      },
      child: Scaffold(body: Consumer<CategoryScreenProvider>(
        builder: (context, provider, child) {
          return provider.loadingCategoryScreen
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
                                    CategorySearchWidget(
                                      provider:
                                          Provider.of<CategoryScreenProvider>(
                                              context),
                                    ),
                                    SizedBox(height: height / 42.1),
                                  ],
                                ),
                              )
                            : Container(
                                height: height / 2,
                                width: width,
                                child: Stack(
                                  children: [
                                    CarouselSlider(
                                        items: provider.slider!.map((item) {
                                          return Center(
                                            child: Container(
                                              // height: height ,
                                              width: width,
                                              height: height / 2.2,
                                              child: Image.network(
                                                "${AppAssetImage.imageBaseUrl}storage/app/public/${(item ?? "")}",
                                                fit: BoxFit.fill,
                                              ),
                                            ),
                                          );
                                        }).toList(),
                                        carouselController:
                                            provider.carousalController,
                                        options: CarouselOptions(
                                          onPageChanged: (index, reason) {
                                            setState(() {
                                              provider.current = index;
                                            });
                                          },
                                          viewportFraction: 1,
                                          height: height / 2.2,
                                          autoPlay: true,
                                          enlargeCenterPage: false,
                                          // aspectRatio: 1,
                                        )),
                                    SizedBox(
                                      height: height / 29,
                                    ),
                                    Align(
                                      alignment: Alignment.bottomCenter,
                                      child: Row(
                                        mainAxisAlignment:
                                            MainAxisAlignment.center,
                                        children: provider.slider!
                                            .asMap()
                                            .entries
                                            .map((entry) {
                                          return GestureDetector(
                                              onTap: () => provider
                                                  .carousalController
                                                  .animateToPage(entry.key),
                                              child: Container(
                                                width: provider.current ==
                                                        entry.key
                                                    ? 12
                                                    : 10,
                                                height: provider.current ==
                                                        entry.key
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
                                    ),
                                    SizedBox(
                                      height: height / 42.1,
                                    ),
                                    // Image.network(
                                    //    "${AppAssetImage.imageBaseUrl}storage/app/public/${(provider.categoryScreenModel?.data?.bannerimage?[0]??"")}",

                                    //   fit: BoxFit.cover,
                                    //   height: height / 2,
                                    //   width: width,
                                    // ),
                                    Column(
                                      children: [
                                       
                                        SizedBox(
                                          height: height / 26.6,
                                        ),
                                        CategorySearchWidget(
                                            provider: Provider.of<
                                                    CategoryScreenProvider>(
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
                        provider.categoryScreenModel?.data?.advertisement ==
                                    null ||
                                provider.categoryScreenModel!.data!
                                    .advertisement!.isEmpty
                            ? SizedBox.shrink()
                            : Provider.of<CategoryScreenProvider>(context,
                                            listen: false)
                                        .searchModel ==
                                    null
                                ? Column(
                                    children: [
                                      CarouselSlider(
                                        
                                          items: provider.advertisement!
                                              .map((item) => Center(
                                                    child: Padding(
                                                      padding:
                                                          EdgeInsets.symmetric(
                                                              horizontal:
                                                                  height / 50),
                                                      child: ClipRRect(
                                                        borderRadius:
                                                            BorderRadius
                                                                .circular(
                                                                    height /
                                                                        66.6),
                                                        child: Container(
                                                          height: height / 5,
                                                          width: width,
                                                          child: Image.network(
                                                            "${AppAssetImage.imageBaseUrl}storage/app/public/${(item ?? "")}",
                                                            fit: BoxFit.fill,
                                                          ),
                                                        ),
                                                      ),
                                                    ),
                                                  ))
                                              .toList(),
                                          carouselController:
                                              provider.carousalController,
                                          options: CarouselOptions(
                                            onPageChanged: (index, reason) {
                                              setState(() {
                                                provider.bannercurrent = index;
                                              });
                                            },
                                           autoPlayInterval: Duration(seconds: 3),
                                            viewportFraction: 1.0,
                                            height: 150,
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
                                        children: provider.advertisement!
                                            .asMap()
                                            .entries
                                            .map((entry) {
                                          return GestureDetector(
                                              onTap: () => provider
                                                  .carousalController
                                                  .animateToPage(entry.key),
                                              child: Container(
                                                width: provider.bannercurrent ==
                                                        entry.key
                                                    ? 12
                                                    : 10,
                                                height:
                                                    provider.bannercurrent ==
                                                            entry.key
                                                        ? 12
                                                        : 10,
                                                margin:
                                                    const EdgeInsets.symmetric(
                                                        horizontal: 3.0),
                                                decoration: BoxDecoration(
                                                    border: provider
                                                                .bannercurrent ==
                                                            entry.key
                                                        ? Border.all(
                                                            color: AppColors
                                                                .whiteColor)
                                                        : Border.all(
                                                            color: AppColors
                                                                .primaryColor),
                                                    shape: BoxShape.circle,
                                                    color: provider
                                                                .bannercurrent ==
                                                            entry.key
                                                        ? AppColors.primaryColor
                                                        : AppColors.whiteColor),
                                              ));
                                        }).toList(),
                                      ),
                                      SizedBox(
                                        height: height / 29,
                                      ),
                                      provider.categoryScreenModel?.data
                                                      ?.category ==
                                                  null ||
                                              provider.categoryScreenModel!
                                                  .data!.category!.isEmpty
                                          ? Padding(
                                              padding: EdgeInsets.symmetric(
                                                  horizontal: width / 16.2,vertical: width/16),
                                              child: Text(
                                                "There are no category to explore",
                                                textAlign: TextAlign.center,
                                                style:
                                                    AppTextStyle.poppinsBold13(
                                                        context: context,
                                                        fontWeight:
                                                            FontWeight.w500,
                                                        color: AppColors
                                                            .primaryColor),
                                              ),
                                            )
                                          : GridView.builder(
                                              padding: EdgeInsets.symmetric(
                                                  horizontal: height / 50),
                                              physics:
                                                  NeverScrollableScrollPhysics(),
                                              itemCount: provider
                                                  .categoryScreenModel
                                                  ?.data
                                                  ?.category
                                                  ?.length,
                                              shrinkWrap: true,
                                              gridDelegate:
                                                  SliverGridDelegateWithFixedCrossAxisCount(
                                                      crossAxisCount: 2,
                                                      childAspectRatio: 1),
                                              itemBuilder:
                                                  (context, categoryindex) {
                                                return Padding(
                                                  padding: EdgeInsets.only(
                                                    right: height / 150,
                                                  ),
                                                  child: Column(
                                                    children: [
                                                      InkWell(
                                                        onTap: () {
                                                          Navigator.push(
                                                              context,
                                                              MaterialPageRoute(
                                                            builder: (context) {
                                                              return CompanyPageScreen(
                                                                cid: provider
                                                                        .categoryScreenModel
                                                                        ?.data
                                                                        ?.category?[
                                                                            categoryindex]
                                                                        .cid ??
                                                                    0,
                                                              );
                                                            },
                                                          ));
                                                        },
                                                        child: Card(
                                                          child: Container(
                                                            height: height / 9,
                                                            width: width / 3,
                                                            decoration:
                                                                BoxDecoration(
                                                                    image: DecorationImage(
                                                                        image: NetworkImage(
                                                                            "${AppAssetImage.imageBaseUrl}storage/app/public/${(provider.categoryScreenModel?.data?.category?[categoryindex].image ?? "")}"),
                                                                        fit: BoxFit
                                                                            .fill),
                                                                    boxShadow: [
                                                                      BoxShadow(
                                                                          blurRadius:
                                                                              32,
                                                                          color: AppColors
                                                                              .blackColor
                                                                              .withOpacity(0.1))
                                                                    ],
                                                                    // border: Border.all(color: AppColors.primaryColor),
                                                                    borderRadius:
                                                                        BorderRadius.circular(height /
                                                                            66.6),
                                                                    color: AppColors
                                                                        .whiteColor),
                                                          ),
                                                        ),
                                                      ),
                                                      SizedBox(
                                                        height: height / 110,
                                                      ),
                                                      Expanded(
                                                        child: Text(
                                                          provider
                                                                  .categoryScreenModel
                                                                  ?.data
                                                                  ?.category?[
                                                                      categoryindex]
                                                                  .name ??
                                                              "",
                                                          textAlign:
                                                              TextAlign.center,
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
                                                      ),
                                                    ],
                                                  ),
                                                );
                                              },
                                            ),
                                      // SizedBox(
                                      //   height: height / 25,
                                      // ),
                                      Padding(
                                        padding: EdgeInsets.symmetric(
                                            horizontal: width / 16.2),
                                        child: Text(
                                          "Top Company to explore",
                                          textAlign: TextAlign.left,
                                          style: AppTextStyle.poppinsRegular14(
                                              context: context,
                                              fontWeight: FontWeight.w600,
                                              color: AppColors.secondaryColor),
                                        ),
                                      ),
                                      SizedBox(
                                        height: height / 42.1,
                                      ),
                                      provider.categoryScreenModel?.data
                                                      ?.company ==
                                                  null ||
                                              provider.categoryScreenModel!
                                                  .data!.company!.isEmpty
                                          ? Padding(
                                              padding: EdgeInsets.symmetric(
                                                  horizontal: width / 16.2),
                                              child: Text(
                                                "There are no company to explore",
                                                textAlign: TextAlign.center,
                                                style:
                                                    AppTextStyle.poppinsBold13(
                                                        context: context,
                                                        fontWeight:
                                                            FontWeight.w500,
                                                        color: AppColors
                                                            .primaryColor),
                                              ),
                                            )
                                          : ListView.builder(
                                              padding: EdgeInsets.symmetric(
                                                  horizontal: width / 16.2),
                                              shrinkWrap: true,
                                              physics:
                                                  NeverScrollableScrollPhysics(),
                                              itemCount: provider
                                                  .categoryScreenModel
                                                  ?.data
                                                  ?.company
                                                  ?.length,
                                              itemBuilder: (context, index) {
                                                return Padding(
                                                  padding: EdgeInsets.only(
                                                      bottom: height / 20),
                                                  child: InkWell(
                                                    onTap: () {
                                                      Navigator.push(context,
                                                          MaterialPageRoute(
                                                        builder: (context) {
                                                          return BrandPageScreen(
                                                            cmpid: provider
                                                                    .categoryScreenModel
                                                                    ?.data
                                                                    ?.company?[
                                                                        index]
                                                                    .cmpid ??
                                                                0,
                                                            cid:
                                                                0,
                                                          );
                                                        },
                                                      ));
                                                    },
                                                    child: Row(
                                                      children: [
                                                        Container(
                                                          height: height / 9,
                                                          width: height / 7,
                                                          decoration:
                                                              BoxDecoration(
                                                                  color: AppColors
                                                                      .lightPink,
                                                                  boxShadow: [
                                                                    BoxShadow(
                                                                        blurRadius:
                                                                            32,
                                                                        color: AppColors
                                                                            .blackColor
                                                                            .withOpacity(0.1))
                                                                  ],
                                                                  // border: Border.all(color: AppColors.primaryColor),
                                                                  borderRadius:
                                                                      BorderRadius.circular(
                                                                          height /
                                                                              66.6),
                                                                  image: provider
                                                                              .categoryScreenModel
                                                                              ?.data
                                                                              ?.company?[
                                                                                  index]
                                                                              .image ==
                                                                          null
                                                                      ? DecorationImage(
                                                                          image:
                                                                              AssetImage(
                                                                            "assets/images/paint-roller.png",
                                                                          ),
                                                                          fit: BoxFit
                                                                              .fill)
                                                                      : DecorationImage(
                                                                          image: NetworkImage(
                                                                              "${AppAssetImage.imageBaseUrl}storage/app/public/${(provider.categoryScreenModel?.data?.company?[index].image ?? "")}"),
                                                                          fit: BoxFit
                                                                              .fill)),
                                                        ),
                                                        SizedBox(
                                                          width: width / 20,
                                                        ),
                                                        Column(
                                                          mainAxisAlignment:
                                                              MainAxisAlignment
                                                                  .start,
                                                          crossAxisAlignment:
                                                              CrossAxisAlignment
                                                                  .start,
                                                          children: [
                                                            SizedBox(
                                                                height: height /
                                                                    100),
                                                            Text(
                                                              provider
                                                                      .categoryScreenModel
                                                                      ?.data
                                                                      ?.company?[
                                                                          index]
                                                                      .name ??
                                                                  "",
                                                              style: AppTextStyle.poppinsRegular14(
                                                                  context:
                                                                      context,
                                                                  color: AppColors
                                                                      .secondaryColor,
                                                                  fontWeight:
                                                                      FontWeight
                                                                          .w700),
                                                            ),
                                                            Text(
                                                              "${provider.categoryScreenModel?.data?.company?[index].count.toString() ?? ""} Category",
                                                              style: AppTextStyle.poppinsBold13(
                                                                  context:
                                                                      context,
                                                                  color: AppColors
                                                                      .secondaryColor,
                                                                  fontWeight:
                                                                      FontWeight
                                                                          .w400),
                                                            ),
                                                            Text(
                                                              provider
                                                                      .categoryScreenModel
                                                                      ?.data
                                                                      ?.company?[
                                                                          index]
                                                                      .city ??
                                                                  "",
                                                              style: AppTextStyle.poppinsBold13(
                                                                  context:
                                                                      context,
                                                                  color: AppColors
                                                                      .secondaryColor,
                                                                  fontWeight:
                                                                      FontWeight
                                                                          .w400),
                                                            ),
                                                          ],
                                                        ),
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
                                : Provider.of<CategoryScreenProvider>(context)
                                        .loadingSearch
                                    ? Padding(
                                        padding: EdgeInsets.only(top: 20),
                                        child: Center(
                                            child: CircularProgressIndicator()),
                                      )
                                    : ListView.builder(
                                        shrinkWrap: true,
                                        physics: NeverScrollableScrollPhysics(),
                                        itemCount:
                                            Provider.of<CategoryScreenProvider>(
                                                    context)
                                                .searchModel
                                                ?.data
                                                ?.length,
                                        itemBuilder: (context, index) {
                                          var searchModel = Provider.of<
                                                      CategoryScreenProvider>(
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
                                                                  ?.data?[index]
                                                                  .id ??
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
                                                        fit: BoxFit.cover,
                                                      ),
                                                    ),
                                                  ),
                                                  SizedBox(
                                                    width: width / 20,
                                                  ),
                                                  Text(
                                                      searchModel?.data?[index]
                                                              .name ??
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
                    Provider.of<CategoryScreenProvider>(context, listen: false)
                                .searchModel ==
                            null
                        ? GlobalCartContainer(width: width, height: height)
                        : SizedBox.shrink()
                  ],
                );
        },
      )),
    );
  }
}
