import 'package:apni_factory/provider/cart_screen_provider.dart';
import 'package:apni_factory/provider/company_screen_provider.dart';
import 'package:apni_factory/provider/homeScreen_provider.dart';
import 'package:apni_factory/res/asset_image.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:apni_factory/res/widgets/company_search_widget.dart';
import 'package:apni_factory/res/widgets/global_cart_container.dart';
import 'package:apni_factory/res/widgets/search_widget.dart';
import 'package:apni_factory/screens/brand_page_screen.dart';
import 'package:apni_factory/screens/category_screen.dart';
import 'package:apni_factory/screens/description_page_screen.dart';
import 'package:carousel_slider/carousel_slider.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter/widgets.dart';
import 'package:provider/provider.dart';
import '../res/colors.dart';
import '../res/textstyle.dart';
import '../utils.dart';

class CompanyPageScreen extends StatefulWidget {
  final int cid;
  const CompanyPageScreen({
    super.key,
    required this.cid,
  });

  @override
  State<CompanyPageScreen> createState() => _CompanyPageScreenState();
}

class _CompanyPageScreenState extends State<CompanyPageScreen> {
  @override
  void initState() {
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      await Provider.of<CompanyScreenProvider>(context, listen: false)
          .companyScreen(context: context, cid: widget.cid);
      await Provider.of<CartScreenProvider>(context, listen: false)
          .userCart(context: context);
    });

    super.initState();
  }

  late CompanyScreenProvider _companyProvider;

  @override
  void didChangeDependencies() {
    _companyProvider =
        Provider.of<CompanyScreenProvider>(context, listen: false);
    // TODO: implement didChangeDependencies
    super.didChangeDependencies();
  }

  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return WillPopScope(
      onWillPop: () async {
        _companyProvider.clearSearch(); // ✅ SAFE
        return true;
      },
      child: Scaffold(
        body: Consumer<CompanyScreenProvider>(
          builder: (context, provider, child) {
            return provider.loadingCompanyScreen
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
                                      CompanySearchWidget(
                                        provider:
                                            Provider.of<CompanyScreenProvider>(
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
                                                height: height / 2.2,
                                                width: width,
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
                                                  margin: const EdgeInsets
                                                      .symmetric(
                                                      horizontal: 3.0),
                                                  decoration: BoxDecoration(
                                                      border: provider
                                                                  .current ==
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
                                                          ? AppColors
                                                              .primaryColor
                                                          : AppColors
                                                              .whiteColor),
                                                ));
                                          }).toList(),
                                        ),
                                      ),
                                      SizedBox(
                                        height: height / 42.1,
                                      ),
                                      // Image.network(
                                      //   "${AppAssetImage.imageBaseUrl}storage/app/public/${(provider.companyScreenModel?.data?.advertisement?[0] ?? "")}",
                                      //   fit: BoxFit.cover,
                                      //   height: height / 2,
                                      //   width: width,
                                      // ),
                                      Column(
                                        children: [
                                          SizedBox(
                                            height: height / 26.6,
                                          ),
                                          CompanySearchWidget(
                                              provider: Provider.of<
                                                      CompanyScreenProvider>(
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
                            height: height / 24,
                          ),
                          Provider.of<CompanyScreenProvider>(context,
                                          listen: false)
                                      .searchModel ==
                                  null
                              ? Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    CarouselSlider(
                                        items: provider.advertisementImage!
                                            .map((item) {
                                          print(item.toString());
                                          return Center(
                                            child: Padding(
                                              padding: EdgeInsets.symmetric(
                                                  horizontal: height / 50),
                                              child: ClipRRect(
                                                borderRadius:
                                                    BorderRadius.circular(
                                                        height / 66.6),
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
                                          );
                                        }).toList(),
                                        carouselController:
                                            provider.carousalController,
                                        options: CarouselOptions(
                                          onPageChanged: (index, reason) {
                                            setState(() {
                                              provider.advertisementCurrent =
                                                  index;
                                            });
                                          },
                                          viewportFraction: 1,
                                          height: 150,
                                          autoPlay: true,
                                          enlargeCenterPage: false,
                                          autoPlayInterval:
                                              Duration(seconds: 3),
                                          // aspectRatio: 1,
                                        )),
                                    SizedBox(
                                      height: height / 29,
                                    ),
                                    Row(
                                      mainAxisAlignment:
                                          MainAxisAlignment.center,
                                      children: provider.advertisementImage!
                                          .asMap()
                                          .entries
                                          .map((entry) {
                                        return GestureDetector(
                                            onTap: () => provider
                                                .carousalController
                                                .animateToPage(entry.key),
                                            child: Container(
                                              width:
                                                  provider.advertisementCurrent ==
                                                          entry.key
                                                      ? 12
                                                      : 10,
                                              height:
                                                  provider.advertisementCurrent ==
                                                          entry.key
                                                      ? 12
                                                      : 10,
                                              margin:
                                                  const EdgeInsets.symmetric(
                                                      horizontal: 3.0),
                                              decoration: BoxDecoration(
                                                  border: provider
                                                              .advertisementCurrent ==
                                                          entry.key
                                                      ? Border.all(
                                                          color: AppColors
                                                              .whiteColor)
                                                      : Border.all(
                                                          color: AppColors
                                                              .primaryColor),
                                                  shape: BoxShape.circle,
                                                  color: provider
                                                              .advertisementCurrent ==
                                                          entry.key
                                                      ? AppColors.primaryColor
                                                      : AppColors.whiteColor),
                                            ));
                                      }).toList(),
                                    ),
                                    SizedBox(
                                      height: height / 29,
                                    ),
                                    Padding(
                                      padding: EdgeInsets.symmetric(
                                          horizontal: width / 16.2),
                                      child: Text(
                                        provider.companyScreenModel?.data
                                                ?.categoryname ??
                                            "",
                                        style: AppTextStyle.poppinsRegular14(
                                            context: context,
                                            fontWeight: FontWeight.w600,
                                            color: AppColors.secondaryColor),
                                      ),
                                    ),
                                    SizedBox(
                                      height: height / 50,
                                    ),
                                    provider.companyScreenModel?.data
                                                    ?.company ==
                                                null ||
                                            provider.companyScreenModel!.data!
                                                .company!.isEmpty
                                        ? Padding(
                                            padding: EdgeInsets.symmetric(
                                                horizontal: width / 16.2),
                                            child: Center(
                                              child: Text(
                                                "There are no company list in \n ${provider.companyScreenModel?.data?.categoryname ?? ""}",
                                                style: AppTextStyle
                                                    .poppinsRegular16(
                                                        context: context,
                                                        color: AppColors
                                                            .primaryColor),
                                                textAlign: TextAlign.center,
                                              ),
                                            ),
                                          )
                                        : GridView.builder(
                                            gridDelegate:
                                                SliverGridDelegateWithFixedCrossAxisCount(
                                                    childAspectRatio: 1.2,
                                                    crossAxisCount: 2),
                                            // padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                                            physics:
                                                NeverScrollableScrollPhysics(),
                                            itemCount: provider
                                                .companyScreenModel
                                                ?.data
                                                ?.company
                                                ?.length,
                                            shrinkWrap: true,
                                            itemBuilder: (context, index) {
                                              return InkWell(
                                                onTap: () {
                                                  Navigator.push(context,
                                                      MaterialPageRoute(
                                                    builder: (context) {
                                                      return BrandPageScreen(
                                                          cmpid: provider
                                                                  .companyScreenModel
                                                                  ?.data
                                                                  ?.company?[
                                                                      index]
                                                                  .id ??
                                                              0,
                                                          cid: widget.cid);
                                                    },
                                                  ));
                                                },
                                                child: Padding(
                                                  padding: EdgeInsets.all(
                                                      height / 60),
                                                  child: Column(
                                                    children: [
                                                      Container(
                                                        height: height / 9,
                                                        width: width / 3,
                                                        decoration:
                                                            BoxDecoration(
                                                                color: AppColors
                                                                    .lightPink,
                                                                borderRadius:
                                                                    BorderRadius.circular(
                                                                        height /
                                                                            60),
                                                                image: provider
                                                                            .companyScreenModel
                                                                            ?.data
                                                                            ?.company?[
                                                                                index]
                                                                            .photo ==
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
                                                                            "${AppAssetImage.imageBaseUrl}storage/app/public/${(provider.companyScreenModel?.data?.company?[index].photo ?? "")}"),
                                                                        fit: BoxFit
                                                                            .fill)),
                                                      ),
                                                      SizedBox(
                                                        height: height / 60,
                                                      ),
                                                      Expanded(
                                                        child: Text(
                                                          provider
                                                                  .companyScreenModel
                                                                  ?.data
                                                                  ?.company?[
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
                                  itemCount: Provider.of<CompanyScreenProvider>(
                                          context)
                                      .searchModel
                                      ?.data
                                      ?.length,
                                  itemBuilder: (context, index) {
                                    var searchModel =
                                        Provider.of<CompanyScreenProvider>(
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
                                                  fit: BoxFit.cover,
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
                      Provider.of<CompanyScreenProvider>(context, listen: false)
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
