import 'package:apni_factory/api_services/notification_services.dart';
import 'package:apni_factory/provider/cart_screen_provider.dart';
import 'package:apni_factory/provider/homeScreen_provider.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/res/widgets/global_cart_container.dart';
import 'package:apni_factory/res/widgets/personal_add_address_widget.dart';
import 'package:apni_factory/res/widgets/search_widget.dart';
import 'package:apni_factory/screens/category_screen.dart';
import 'package:apni_factory/screens/company_page_screen.dart';
import 'package:apni_factory/screens/description_page_screen.dart';
import 'package:apni_factory/screens/notification_screen.dart';
import 'package:carousel_slider/carousel_slider.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../res/asset_image.dart';
import '../utils.dart'; 

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  NotificationServices notificationServices = NotificationServices();

  @override
  void initState() {
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      await Provider.of<HomeScreenProvider>(context, listen: false)
          .homeScreen(context: context);
      await Provider.of<HomeScreenProvider>(context, listen: false)
          .getAddressList(context: context);
      await Provider.of<CartScreenProvider>(context, listen: false)
          .userCart(context: context);
      await Provider.of<HomeScreenProvider>(context, listen: false)
          .stateDropDown(context: context);
      await Provider.of<HomeScreenProvider>(context, listen: false)
          .adminAppPages(context: context);
    });

    // TODO: implement initState
    super.initState();
  }

  late HomeScreenProvider _homeProvider;
  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    _homeProvider = Provider.of<HomeScreenProvider>(context, listen: false);
  }

  @override
  Widget build(BuildContext context) {
    // CartScreenProvider cartprovider =
    //     Provider.of<CartScreenProvider>(context, listen: false);
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Consumer<HomeScreenProvider>(
      builder: (context, provider, child) {
        return WillPopScope(
          onWillPop: () async {
            _homeProvider.clearSearch(); // ✅ SAFE
            return true;
          },
          child: Scaffold(
              key: provider.key,
              drawer: Utils.drawer(
                  height: height,
                  width: width,
                  context: context,
                  provider: provider),
              body: provider.loadingHomeScreen ||
                      Provider.of<CartScreenProvider>(context)
                          .loadingUserCart ||
                      provider.loadingGetAddressList ||
                      provider.loadingAddAddress ||
                      provider.loadingGetAddressDetail ||
                      provider.loadingUpdateAddress ||
                      provider.loadingDeleteAddress ||
                      provider.loadingStateDropDown ||
                      provider.loadingCityDropDown ||
                      provider.loadingPincodeDropDown ||
                      provider.loadingAdminAppPages
                  ? Center(
                      child: CircularProgressIndicator(),
                    )
                  : Stack(
                      children: [
                        SizedBox(
                          height: height,
                          width: width,
                          child: ListView(
                            children: [
                              AppBar(
                                automaticallyImplyLeading: false,
                                leading: InkWell(
                                  onTap: () {
                                    provider.key.currentState!.openDrawer();
                                  },
                                  child: Icon(
                                    Icons.menu,
                                    color: AppColors.lightText,
                                  ),
                                ),
                                backgroundColor: Colors.transparent,
                                centerTitle: true,
                                title: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      children: [
                                        Icon(
                                          Icons.location_on_outlined,
                                          color: AppColors.secondaryColor,
                                          size: height / 35,
                                        ),
                                        SizedBox(
                                          width: width / 100,
                                        ),
                                        Expanded(
                                          child: Text(
                                            provider.selectedAddressText ??
                                                "Select Address",
                                            overflow: TextOverflow.ellipsis,
                                            style: AppTextStyle.poppinsBold13(
                                                context: context,
                                                color: AppColors.secondaryColor,
                                                fontWeight: FontWeight.w500),
                                          ),
                                        ),
                                        SizedBox(
                                          width: width / 100,
                                        ),
                                        InkWell(
                                          onTap: () {
                                            final personalAddresses = provider
                                                    .getAddressListModel?.data
                                                    ?.where((e) =>
                                                        e.type == "personal")
                                                    .toList() ??
                                                [];

                                            final personalAddress =
                                                personalAddresses.isNotEmpty
                                                    ? personalAddresses.first
                                                    : null;
                                            showModalBottomSheet(
                                              context: context,
                                              builder: (context) {
                                                return Container(
                                                  width: width,
                                                  child: Column(
                                                    crossAxisAlignment:
                                                        CrossAxisAlignment
                                                            .start,
                                                    children: [
                                                      Container(
                                                          width: width,
                                                          height: height / 10,
                                                          decoration:
                                                              BoxDecoration(
                                                                  borderRadius:
                                                                      BorderRadius
                                                                          .only(
                                                                    topLeft: Radius.circular(
                                                                        height /
                                                                            60),
                                                                    topRight: Radius.circular(
                                                                        height /
                                                                            60),
                                                                  ),
                                                                  color: AppColors
                                                                      .stroke),
                                                          child: Padding(
                                                            padding: EdgeInsets
                                                                .symmetric(
                                                                    horizontal:
                                                                        width /
                                                                            29),
                                                            child: Column(
                                                              crossAxisAlignment:
                                                                  CrossAxisAlignment
                                                                      .start,
                                                              mainAxisAlignment:
                                                                  MainAxisAlignment
                                                                      .center,
                                                              children: [
                                                                Text(
                                                                  "Choose your Communication Address",
                                                                  style: AppTextStyle.poppinsRegular16(
                                                                      context:
                                                                          context,
                                                                      color: AppColors
                                                                          .secondaryColor,
                                                                      fontWeight:
                                                                          FontWeight
                                                                              .w600),
                                                                ),
                                                              ],
                                                            ),
                                                          )),
                                                      Expanded(
                                                        child: ListView(
                                                          children: [
                                                            SizedBox(
                                                              height:
                                                                  height / 30,
                                                            ),
                                                            if (personalAddress ==
                                                                null)
                                                              Padding(
                                                                padding: EdgeInsets
                                                                    .symmetric(
                                                                        horizontal:
                                                                            width /
                                                                                29),
                                                                child: InkWell(
                                                                  onTap: () {
                                                                    showModalBottomSheet(
                                                                      context:
                                                                          context,
                                                                      builder:
                                                                          (context) {
                                                                        return PersonalAddAddressWidget(
                                                                            provider:
                                                                                provider);
                                                                      },
                                                                    );
                                                                  },
                                                                  child: Row(
                                                                    children: [
                                                                      Icon(
                                                                        Icons
                                                                            .add_circle_outline,
                                                                        color: AppColors
                                                                            .primaryColor,
                                                                      ),
                                                                      SizedBox(
                                                                        width: width /
                                                                            40,
                                                                      ),
                                                                      Text(
                                                                        "Add New Communication Address",
                                                                        style: AppTextStyle.poppinsRegular16(
                                                                            context:
                                                                                context,
                                                                            color:
                                                                                AppColors.primaryColor,
                                                                            fontWeight: FontWeight.w600),
                                                                      )
                                                                    ],
                                                                  ),
                                                                ),
                                                              ),
                                                            SizedBox(
                                                              height:
                                                                  height / 30,
                                                            ),
                                                            personalAddress ==
                                                                    null
                                                                ? SizedBox
                                                                    .shrink()
                                                                : ListTile(
                                                                    leading:
                                                                        Icon(
                                                                      Icons
                                                                          .location_on,
                                                                      color: AppColors
                                                                          .primaryColor,
                                                                    ),
                                                                    title: Text(
                                                                      personalAddress
                                                                              .identityname ??
                                                                          "",
                                                                      style: AppTextStyle
                                                                          .poppinsBold17(
                                                                        context:
                                                                            context,
                                                                        color: AppColors
                                                                            .blackColor,
                                                                        fontWeight:
                                                                            FontWeight.w500,
                                                                      ),
                                                                    ),
                                                                    subtitle:
                                                                        Column(
                                                                      crossAxisAlignment:
                                                                          CrossAxisAlignment
                                                                              .start,
                                                                      children: [
                                                                        Text(personalAddress.name ??
                                                                            ""),
                                                                        Text(
                                                                          "${personalAddress.landmark1 ?? ""}, "
                                                                          "${personalAddress.landmark2 ?? ""}, "
                                                                          "${personalAddress.city ?? ""}, "
                                                                          "${personalAddress.state ?? ""}, "
                                                                          "${personalAddress.pincode ?? ""}",
                                                                        ),
                                                                      ],
                                                                    ),
                                                                    trailing:
                                                                        IconButton(
                                                                      icon: Icon(
                                                                          Icons
                                                                              .edit),
                                                                      onPressed:
                                                                          () async {
                                                                        await provider
                                                                            .getIdAddress(
                                                                          context:
                                                                              context,
                                                                          addressId: personalAddress
                                                                              .id
                                                                              .toString(),
                                                                        );
                                                                      },
                                                                    ),
                                                                    onTap: () {
                                                                      provider
                                                                          .selectAddress(
                                                                        personalAddress
                                                                            .id
                                                                            .toString(),
                                                                        context:
                                                                            context,
                                                                        height:
                                                                            height,
                                                                        addressId: personalAddress
                                                                            .id
                                                                            .toString(),
                                                                        addressText:
                                                                            "${personalAddress.landmark1 ?? ""}, "
                                                                            "${personalAddress.landmark2 ?? ""}, "
                                                                            "${personalAddress.city ?? ""}, "
                                                                            "${personalAddress.state ?? ""}, "
                                                                            "${personalAddress.pincode ?? ""}",
                                                                      );
                                                                    },
                                                                  )
                                                          ],
                                                        ),
                                                      )
                                                    ],
                                                  ),
                                                );
                                              },
                                            );
                                          },
                                          child: Icon(
                                            Icons.keyboard_arrow_down_rounded,
                                            color: AppColors.secondaryColor,
                                          ),
                                        )
                                      ],
                                    ),
                                    // Text(
                                    //   "171,Shree Ram Path, Raghunath Puri, Jhotwara",
                                    //   style: AppTextStyle.poppinsMedium15(
                                    //       context: context,
                                    //       color: AppColors.secondaryColor),
                                    // ),
                                  ],
                                ),
                                actions: [
                                  Stack(
                                    children: [
                                      IconButton(
                                          onPressed: () {
                                            Navigator.push(context,
                                                MaterialPageRoute(
                                              builder: (context) {
                                                return NotificationScreen();
                                              },
                                            ));
                                          },
                                          icon: Icon(
                                            Icons.notifications,
                                            color: AppColors.lightText,
                                          )),
                                      Padding(
                                        padding: EdgeInsets.only(
                                            left: height / 60,
                                            top: height / 130),
                                        child: Text(
                                          provider.homeScreenModel?.data
                                                  ?.notification
                                                  .toString() ??
                                              "",
                                          style: AppTextStyle.poppinsBold12(
                                              context: context,
                                              color: AppColors.primaryColor),
                                        ),
                                      )
                                    ],
                                  )
                                ],
                              ),
                              SizedBox(
                                height: height / 23.5,
                              ),
                              SearchWidget(
                                provider: provider,
                              ),
                              SizedBox(
                                height: height / 42.1,
                              ),
                              provider.searchModel == null
                                  ? Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        CarouselSlider(
                                            items: provider.slider!.map((item) {
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
                                                  provider.slidercurrent =
                                                      index;
                                                });
                                              },
                                              viewportFraction: 1,
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
                                          children: provider.slider!
                                              .asMap()
                                              .entries
                                              .map((entry) {
                                            return GestureDetector(
                                                onTap: () => provider
                                                    .carousalController
                                                    .animateToPage(entry.key),
                                                child: Container(
                                                  width:
                                                      provider.slidercurrent ==
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
                                                                  .slidercurrent ==
                                                              entry.key
                                                          ? Border.all(
                                                              color: AppColors
                                                                  .whiteColor)
                                                          : Border.all(
                                                              color: AppColors
                                                                  .primaryColor),
                                                      shape: BoxShape.circle,
                                                      color: provider
                                                                  .slidercurrent ==
                                                              entry.key
                                                          ? AppColors
                                                              .primaryColor
                                                          : AppColors
                                                              .whiteColor),
                                                ));
                                          }).toList(),
                                        ),
                                        SizedBox(
                                          height: height / 42.1,
                                        ),
                                        CarouselSlider(
                                            items: provider.advertisement!
                                                .map((item) {
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
                                                        fit: BoxFit.cover,
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
                                                  provider.current = index;
                                                });
                                              },
                                              autoPlayInterval:
                                                  Duration(seconds: 3),

                                              viewportFraction: 1,
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
                                        SizedBox(
                                          height: height / 42.1,
                                        ),
                                        Padding(
                                          padding: EdgeInsets.symmetric(
                                              horizontal: width / 16.2),
                                          child: Text(
                                            "We deal into",
                                            style: AppTextStyle.poppinsBold13(
                                                context: context,
                                                fontWeight: FontWeight.w600,
                                                color:
                                                    AppColors.secondaryColor),
                                          ),
                                        ),
                                        SizedBox(
                                          height: height / 42.1,
                                        ),
                                        GridView.builder(
                                          physics:
                                              NeverScrollableScrollPhysics(),
                                          padding: EdgeInsets.only(
                                              left: height / 50),
                                          itemCount: provider.homeScreenModel
                                              ?.data?.maincategory?.length,
                                          shrinkWrap: true,
                                          gridDelegate:
                                              SliverGridDelegateWithFixedCrossAxisCount(
                                                  crossAxisCount: 2),
                                          itemBuilder: (context, index) {
                                            return InkWell(
                                              onTap: () {
                                                Navigator.push(context,
                                                    MaterialPageRoute(
                                                  builder: (context) {
                                                    return CategoryScreen(
                                                      mid: provider
                                                              .homeScreenModel
                                                              ?.data
                                                              ?.maincategory?[
                                                                  index]
                                                              .id ??
                                                          0,
                                                    );
                                                  },
                                                ));
                                              },
                                              child: Padding(
                                                padding: EdgeInsets.only(
                                                    right: height / 50,
                                                    bottom: height / 50),
                                                child: Container(
                                                  height: height / 2.33,
                                                  width: width / 2,
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
                                                              .primaryColor),
                                                      borderRadius:
                                                          BorderRadius.circular(
                                                              height / 66.6),
                                                      color:
                                                          AppColors.whiteColor),
                                                  child: Stack(
                                                    children: [
                                                      Align(
                                                          alignment: Alignment
                                                              .bottomLeft,
                                                          child: Image.asset(
                                                            AppAssetImage
                                                                .gridImage,
                                                            color: AppColors
                                                                .primaryColor,
                                                          )),
                                                      Padding(
                                                        padding:
                                                            EdgeInsets.only(
                                                                left: width /
                                                                    25.7,
                                                                top: width /
                                                                    25.7),
                                                        child: Column(
                                                          crossAxisAlignment:
                                                              CrossAxisAlignment
                                                                  .start,
                                                          children: [
                                                            // Text(
                                                            //   "Upto 48% off",
                                                            //   style:
                                                            //       AppTextStyle.poppinsSemibold10(
                                                            //           context: context,
                                                            //           color:
                                                            //               AppColors.primaryColor),
                                                            // ),
                                                            SizedBox(
                                                              height: height /
                                                                  133.3,
                                                            ),
                                                            Text(
                                                              provider
                                                                      .homeScreenModel
                                                                      ?.data
                                                                      ?.maincategory?[
                                                                          index]
                                                                      .name ??
                                                                  "",
                                                              style: AppTextStyle.poppinsBold12(
                                                                  context:
                                                                      context,
                                                                  fontWeight:
                                                                      FontWeight
                                                                          .w700,
                                                                  color: AppColors
                                                                      .secondaryColor),
                                                            ),
                                                            Text(
                                                              provider
                                                                      .homeScreenModel
                                                                      ?.data
                                                                      ?.maincategory?[
                                                                          index]
                                                                      .title ??
                                                                  "",
                                                              style: AppTextStyle.poppinsSemibold10(
                                                                  context:
                                                                      context,
                                                                  color: AppColors
                                                                      .lightText,
                                                                  fontWeight:
                                                                      FontWeight
                                                                          .w400),
                                                            ),
                                                          ],
                                                        ),
                                                      ),
                                                      Align(
                                                        alignment: Alignment
                                                            .bottomRight,
                                                        child: Padding(
                                                            padding:
                                                                EdgeInsets.only(
                                                                    right:
                                                                        width /
                                                                            25.7,
                                                                    bottom:
                                                                        width /
                                                                            25.7),
                                                            child: Container(
                                                              height:
                                                                  height / 15,
                                                              width:
                                                                  height / 15,
                                                              decoration: BoxDecoration(
                                                                  shape: BoxShape
                                                                      .circle,
                                                                  image: DecorationImage(
                                                                      image: NetworkImage(
                                                                          "${AppAssetImage.imageBaseUrl}storage/app/public/${(provider.homeScreenModel?.data?.maincategory?[index].image ?? "")}"),
                                                                      fit: BoxFit
                                                                          .fill)),
                                                            )),
                                                      )
                                                    ],
                                                  ),
                                                ),
                                              ),
                                            );
                                          },
                                        ),
                                        SizedBox(
                                          height: height / 42.1,
                                        ),
                                        SizedBox(
                                          height: height / 7,
                                        )
                                      ],
                                    )
                                  : provider.loadingSearch
                                      ? Padding(
                                          padding: EdgeInsets.only(top: 20),
                                          child: Center(
                                              child:
                                                  CircularProgressIndicator()),
                                        )
                                      : ListView.builder(
                                          shrinkWrap: true,
                                          physics:
                                              NeverScrollableScrollPhysics(),
                                          itemCount: provider
                                              .searchModel?.data?.length,
                                          itemBuilder: (context, index) {
                                            return Padding(
                                              padding: EdgeInsets.symmetric(
                                                  horizontal: width / 16.2,
                                                  vertical: height / 60),
                                              child: InkWell(
                                                onTap: () {
                                                  if (provider
                                                          .searchModel
                                                          ?.data?[index]
                                                          .screen ==
                                                      "category") {
                                                    Navigator.push(context,
                                                        MaterialPageRoute(
                                                      builder: (context) {
                                                        return CategoryScreen(
                                                            mid: provider
                                                                    .searchModel
                                                                    ?.data?[
                                                                        index]
                                                                    .relatedid ??
                                                                0);
                                                      },
                                                    ));
                                                  } else if (provider
                                                          .searchModel
                                                          ?.data?[index]
                                                          .screen ==
                                                      "company") {
                                                    Navigator.push(context,
                                                        MaterialPageRoute(
                                                      builder: (context) {
                                                        return CompanyPageScreen(
                                                            cid: provider
                                                                    .searchModel
                                                                    ?.data?[
                                                                        index]
                                                                    .relatedid ??
                                                                0);
                                                      },
                                                    ));
                                                  } else if (provider
                                                          .searchModel
                                                          ?.data?[index]
                                                          .screen ==
                                                      "product") {
                                                    Navigator.push(context,
                                                        MaterialPageRoute(
                                                      builder: (context) {
                                                        return DescriptionPageScreen(
                                                            productId: provider
                                                                    .searchModel
                                                                    ?.data?[
                                                                        index]
                                                                    .id ??
                                                                0,
                                                            colorId: provider
                                                                    .searchModel
                                                                    ?.data?[
                                                                        index]
                                                                    .relatedid
                                                                    .toString() ??
                                                                "");
                                                      },
                                                    ));
                                                  } else {
                                                    Utils.commonSnackBar(
                                                        context,
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
                                                            BorderRadius
                                                                .circular(
                                                                    height /
                                                                        60),
                                                        image: DecorationImage(
                                                          image: NetworkImage(
                                                              "${AppAssetImage.imageBaseUrl}storage/app/public/${(provider.searchModel?.data?[index].image ?? "")}"),
                                                          fit: BoxFit.cover,
                                                        ),
                                                      ),
                                                    ),
                                                    SizedBox(
                                                      width: width / 20,
                                                    ),
                                                    Text(
                                                        provider
                                                                .searchModel
                                                                ?.data?[index]
                                                                .name ??
                                                            "",
                                                        style: AppTextStyle
                                                            .poppinsMedium15(
                                                                context:
                                                                    context,
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
                        ),
                        provider.searchModel == null
                            ? GlobalCartContainer(width: width, height: height)
                            : SizedBox.shrink()
                      ],
                    )),
        );
      },
    );
  }
}
