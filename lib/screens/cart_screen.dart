import 'dart:developer';
import 'package:apni_factory/provider/cart_screen_provider.dart';
import 'package:apni_factory/res/asset_image.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/res/widgets/add_address_widget.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:apni_factory/screens/Coupon_offer_screen.dart';
import 'package:apni_factory/screens/description_page_screen.dart';
import 'package:apni_factory/utils.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../res/colors.dart';
import '../res/widgets/round_button.dart';
import 'checkout_screen.dart';

class CartScreen extends StatefulWidget {
  const CartScreen({super.key});

  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  @override
  void initState() {
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      await Provider.of<CartScreenProvider>(context, listen: false)
          .getAddressList(context: context);
      await Provider.of<CartScreenProvider>(context, listen: false)
          .relatedProduct(context: context);
      await Provider.of<CartScreenProvider>(context, listen: false)
          .userCart(context: context);
      await Provider.of<CartScreenProvider>(context, listen: false)
          .stateDropDown(context: context);
    });

    // TODO: implement initState
    super.initState();
  }

  @override
  Widget build(BuildContext dialogContext) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(
      appBar: AppBar(
        backgroundColor: AppColors.whiteColor,
        title: Text(
          "Your Cart",
          style: AppTextStyle.poppinsRegular16(
              context: context,
              color: AppColors.secondaryColor,
              fontWeight: FontWeight.w500),
        ),
        actions: [
          Padding(
            padding: EdgeInsets.only(right: height / 30),
            child: InkWell(
              onTap: () {
                Provider.of<CartScreenProvider>(context, listen: false)
                    .emptyCart(
                        context: context,
                        globalcontext: dialogContext,
                        height: height);
              },
              child: Container(
                decoration: BoxDecoration(
                    color: AppColors.stroke,
                    borderRadius: BorderRadius.circular(height / 30)),
                child: Padding(
                  padding: const EdgeInsets.all(8.0),
                  child: Row(
                    children: [
                      Icon(
                        Icons.delete,
                        size: height / 40,
                      ),
                      SizedBox(
                        width: width / 90,
                      ),
                      Text(
                        "Clear cart",
                        style: AppTextStyle.poppinsBold13(
                          fontWeight: FontWeight.w500,
                          context: context,
                          color: AppColors.secondaryColor,
                        ),
                      )
                    ],
                  ),
                ),
              ),
            ),
          )
        ],
      ),
      body: Consumer<CartScreenProvider>(
        builder: (context, provider, child) {
          return provider.loadingGetAddressList ||
                  provider.loadingAddAddress ||
                  provider.loadingGetAddressDetail ||
                  provider.loadingUpdateAddress ||
                  provider.loadingDeleteAddress ||
                  provider.loadingUserCart ||
                  provider.loadingIncreaseCartQuantity ||
                  provider.loadingDeleteCart ||
                  provider.loadingEmptyCart ||
                  provider.loadingDeleteCartAttribute ||
                  provider.loadingStateDropDown ||
                  provider.loadingCityDropDown ||
                  provider.loadingPincodeDropDown ||
                  provider.loadingCheckAvailability ||
                  provider.loadingUpdateCartAddress ||
                  provider.loadingRelatedProduct
              ? CircularProgressIndicatorWidget()
              : provider.userCartModel?.data == null ||
                      provider.userCartModel!.data.isEmpty
                  ? Center(
                      child: Text(
                        "Your cart is empty",
                        style: AppTextStyle.poppinsBold24(
                            context: context, fontWeight: FontWeight.w600),
                      ),
                    )
                  : Stack(
                      children: [
                        ListView(
                          children: [
                            Divider(
                              thickness: 0.5,
                              color: AppColors.stroke,
                            ),
                            InkWell(
                              onTap: () {
                                showModalBottomSheet(
                                  context: context,
                                  builder: (context) {
                                    return Container(
                                      width: width,
                                      child: Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        children: [
                                          Container(
                                              width: width,
                                              height: height / 10,
                                              decoration: BoxDecoration(
                                                  borderRadius:
                                                      BorderRadius.only(
                                                    topLeft: Radius.circular(
                                                        height / 60),
                                                    topRight: Radius.circular(
                                                        height / 60),
                                                  ),
                                                  color: AppColors.stroke),
                                              child: Padding(
                                                padding: EdgeInsets.symmetric(
                                                    horizontal: width / 29),
                                                child: Column(
                                                  crossAxisAlignment:
                                                      CrossAxisAlignment.start,
                                                  mainAxisAlignment:
                                                      MainAxisAlignment.center,
                                                  children: [
                                                    Text(
                                                      "Choose a delivery address",
                                                      style: AppTextStyle
                                                          .poppinsRegular16(
                                                              context: context,
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
                                                  height: height / 30,
                                                ),
                                                
                                                Padding(
                                                  padding: EdgeInsets.symmetric(
                                                      horizontal: width / 29),
                                                  child: InkWell(
                                                    onTap: () {
                                                      showModalBottomSheet(
                                                        context: context,
                                                        builder: (context) {
                                                          return AddAddressWidget(
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
                                                          width: width / 40,
                                                        ),
                                                        Text(
                                                          "Add New Delivery Address",
                                                          style: AppTextStyle
                                                              .poppinsRegular16(
                                                                  context:
                                                                      context,
                                                                  color: AppColors
                                                                      .primaryColor,
                                                                  fontWeight:
                                                                      FontWeight
                                                                          .w600),
                                                        )
                                                      ],
                                                    ),
                                                  ),
                                                ),
                                                SizedBox(
                                                  height: height / 30,
                                                ),
                                                ListView.builder(
                                                  physics:
                                                      NeverScrollableScrollPhysics(),
                                                  shrinkWrap: true,
                                                  itemCount: provider
                                                          .getAddressListModel
                                                          ?.data
                                                          ?.length ??
                                                      0,
                                                  itemBuilder:
                                                      (context, index) {
                                                    final address = provider
                                                        .getAddressListModel!
                                                        .data![index];
                                                    if (address.type !=
                                                        "transport") {
                                                      return const SizedBox
                                                          .shrink();
                                                    }

                                                    return RadioListTile<
                                                        String>(
                                                      value:
                                                          address.id.toString(),
                                                      groupValue: provider
                                                          .selectedAddressId,
                                                      activeColor: AppColors
                                                          .primaryColor,
                                                      onChanged: (value) {
                                                        if (value != null) {
                                                          provider.selectAddress(
                                                              value,
                                                              context: context,
                                                              height: height,
                                                              addressId: value,
                                                              addressText:
                                                                  "${address.landmark1 ?? ""}, ${address.landmark2 ?? ""}, "
                                                                  "${address.city ?? ""}, ${address.state ?? ""}, "
                                                                  "${address.pincode ?? ""}");
                                                        }
                                                      },
                                                      title: Text(
                                                        provider
                                                                .getAddressListModel
                                                                ?.data?[index]
                                                                .identityname ??
                                                            "",
                                                        style: AppTextStyle
                                                            .poppinsBold17(
                                                                context:
                                                                    context,
                                                                color: AppColors
                                                                    .blackColor,
                                                                fontWeight:
                                                                    FontWeight
                                                                        .w500),
                                                      ),
                                                      subtitle: Column(
                                                        crossAxisAlignment:
                                                            CrossAxisAlignment
                                                                .start,
                                                        children: [
                                                          Text(provider
                                                                  .getAddressListModel
                                                                  ?.data?[index]
                                                                  .name ??
                                                              ""),
                                                          Text(
                                                              "${(provider.getAddressListModel?.data?[index].landmark1 ?? "")} ${(provider.getAddressListModel?.data?[index].landmark2 ?? "")} ${(provider.getAddressListModel?.data?[index].city ?? "")} ${(provider.getAddressListModel?.data?[index].state ?? "")} ${(provider.getAddressListModel?.data?[index].pincode ?? "")}")
                                                        ],
                                                      ),
                                                      secondary: IconButton(
                                                        icon: const Icon(
                                                            Icons.edit),
                                                        onPressed: () async {
                                                          await provider.getIdAddress(
                                                              context: context,
                                                              addressId: provider
                                                                      .getAddressListModel
                                                                      ?.data?[
                                                                          index]
                                                                      .id
                                                                      .toString() ??
                                                                  "");
                                                        },
                                                      ),
                                                    );
                                                    // ListTile(
                                                    //         leading:
                                                    //             Radio<String>(
                                                    //           value: provider
                                                    //                       .addressList?[
                                                    //                   index] ??
                                                    //               "",
                                                    //           groupValue: provider
                                                    //               .selectedAddress,
                                                    //           onChanged:
                                                    //               (value) {
                                                    //             if (value !=
                                                    //                 null) {
                                                    //               provider.selectAddress(
                                                    //                   value,
                                                    //                   context:
                                                    //                       context,
                                                    //                   height:
                                                    //                       height);
                                                    //             }
                                                    //           },
                                                    //         ),
                                                    //         trailing:
                                                    //             IconButton(
                                                    //                 onPressed:
                                                    //                     () async {
                                                    //                   await provider.getIdAddress(
                                                    //                       context:
                                                    //                           context,
                                                    //                       addressId:
                                                    //                           provider.getAddressListModel?.data?[index].id.toString() ?? "");
                                                    //                 },
                                                    //                 icon: Icon(Icons
                                                    //                     .edit)),
                                                    //         subtitle: Column(
                                                    //           crossAxisAlignment:
                                                    //               CrossAxisAlignment
                                                    //                   .start,
                                                    //           children: [
                                                    //             Text(provider
                                                    //                     .getAddressListModel
                                                    //                     ?.data?[
                                                    //                         index]
                                                    //                     .name ??
                                                    //                 ""),
                                                    //             Text(
                                                    //                 "${(provider.getAddressListModel?.data?[index].landmark1 ?? "")} ${(provider.getAddressListModel?.data?[index].landmark2 ?? "")} ${(provider.getAddressListModel?.data?[index].city ?? "")} ${(provider.getAddressListModel?.data?[index].state ?? "")} ${(provider.getAddressListModel?.data?[index].pincode ?? "")}")
                                                    //           ],
                                                    //         ),
                                                    //         title: Text(
                                                    //           provider
                                                    //                   .getAddressListModel
                                                    //                   ?.data?[
                                                    //                       index]
                                                    //                   .identityname ??
                                                    //               "",
                                                    //           style: AppTextStyle.poppinsBold17(
                                                    //               context:
                                                    //                   context,
                                                    //               color: AppColors
                                                    //                   .blackColor,
                                                    //               fontWeight:
                                                    //                   FontWeight
                                                    //                       .w500),
                                                    //         ),
                                                    //       )
                                                    //     : SizedBox.shrink();
                                                  },
                                                ),
                                              ],
                                            ),
                                          )
                                        ],
                                      ),
                                    );
                                  },
                                );
                              },
                              child: Container(
                                height: height / 15,
                                width: width,
                                decoration: BoxDecoration(
                                    color: AppColors.whiteColor,
                                    boxShadow: [BoxShadow()],
                                    borderRadius: BorderRadius.only(
                                        bottomLeft:
                                            Radius.circular(height / 30),
                                        bottomRight:
                                            Radius.circular(height / 30))),
                                child: Padding(
                                  padding: EdgeInsets.symmetric(
                                      horizontal: width / 16.2),
                                  child: Row(
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
                                          style: AppTextStyle.poppinsRegular14(
                                              context: context,
                                              color: AppColors.secondaryColor),
                                        ),
                                      ),
                                      Icon(
                                        Icons.keyboard_arrow_down,
                                        color: AppColors.secondaryColor,
                                        size: height / 35,
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ),
                            SizedBox(
                              height: height / 30,
                            ),
                            ListView.builder(
                              physics: NeverScrollableScrollPhysics(),
                              itemCount: provider.userCartModel?.data.length,
                              shrinkWrap: true,
                              itemBuilder: (context, categoryIndex) {
                                final List<String>? categories =
                                    provider.userCartModel?.data.keys.toList();

                                final categoryName = categories?[categoryIndex];

                                final productLists =
                                    provider.userCartModel?.data[categoryName];

                                return Padding(
                                  padding: EdgeInsets.all(height / 50),
                                  child: Container(
                                      width: width,
                                      decoration: BoxDecoration(
                                          color: AppColors.whiteColor,
                                          borderRadius: BorderRadius.circular(
                                              height / 30),
                                          boxShadow: [BoxShadow()]),
                                      child: Padding(
                                        padding: EdgeInsets.symmetric(
                                            horizontal: height / 55),
                                        child: Column(
                                          crossAxisAlignment:
                                              CrossAxisAlignment.start,
                                          children: [
                                            Theme(
                                              data: ThemeData().copyWith(
                                                  dividerColor:
                                                      Colors.transparent),
                                              child: ExpansionTile(
                                                initiallyExpanded: true,
                                                title: Text(
                                                  categories?[categoryIndex] ??
                                                      "",
                                                  style: AppTextStyle
                                                      .poppinsRegular14(
                                                    context: context,
                                                    fontWeight: FontWeight.w600,
                                                    color: AppColors
                                                        .secondaryColor,
                                                  ),
                                                ),
                                                children: [
                                                  Container(
                                                    width: width,
                                                    decoration: BoxDecoration(
                                                        color: AppColors
                                                            .whiteColor,
                                                        borderRadius:
                                                            BorderRadius
                                                                .circular(
                                                                    height /
                                                                        40)),
                                                    child: Column(
                                                      children: [
                                                        Padding(
                                                          padding: EdgeInsets
                                                              .symmetric(
                                                                  horizontal:
                                                                      height /
                                                                          60),
                                                          child:
                                                              ListView.builder(
                                                            physics:
                                                                NeverScrollableScrollPhysics(),
                                                            itemCount:
                                                                productLists?[0]
                                                                    .length,
                                                            shrinkWrap: true,
                                                            itemBuilder:
                                                                (context,
                                                                    index) {
                                                              return Padding(
                                                                padding: EdgeInsets
                                                                    .symmetric(
                                                                        vertical:
                                                                            height /
                                                                                70),
                                                                child: Column(
                                                                  children: [
                                                                    Row(
                                                                      children: [
                                                                        Container(
                                                                          height:
                                                                              height / 15,
                                                                          width:
                                                                              height / 15,
                                                                          decoration: BoxDecoration(
                                                                              borderRadius: BorderRadius.circular(height / 60),
                                                                              image: DecorationImage(image: NetworkImage("${AppAssetImage.imageBaseUrl}storage/app/public/${(productLists?[0][index].productImage ?? "")}"), fit: BoxFit.cover)),
                                                                        ),
                                                                        SizedBox(
                                                                          width:
                                                                              width / 40,
                                                                        ),
                                                                        Text(
                                                                          productLists?[0][index].productName ??
                                                                              "",
                                                                          style: AppTextStyle.poppinsRegular14(
                                                                              context: context,
                                                                              color: AppColors.secondaryColor,
                                                                              fontWeight: FontWeight.w600),
                                                                          overflow:
                                                                              TextOverflow.ellipsis,
                                                                        ),
                                                                        Spacer(),
                                                                        InkWell(
                                                                            onTap:
                                                                                () {
                                                                              provider.deleteCart(context: context, globalcontext: dialogContext, height: height, cartid: productLists?[0][index].cartid.toString() ?? "");
                                                                            },
                                                                            child:
                                                                                Icon(
                                                                              Icons.delete,
                                                                              size: height / 40,
                                                                            ))
                                                                      ],
                                                                    ),
                                                                    SizedBox(
                                                                      height:
                                                                          height /
                                                                              52,
                                                                    ),
                                                                    ListView
                                                                        .builder(
                                                                      itemCount: productLists?[0]
                                                                              [
                                                                              index]
                                                                          .attributeData
                                                                          .length,
                                                                      shrinkWrap:
                                                                          true,
                                                                      physics:
                                                                          NeverScrollableScrollPhysics(),
                                                                      itemBuilder:
                                                                          (context,
                                                                              boxindex) {
                                                                        return Row(
                                                                          children: [
                                                                            Expanded(
                                                                              child: Column(
                                                                                crossAxisAlignment: CrossAxisAlignment.start,
                                                                                mainAxisAlignment: MainAxisAlignment.center,
                                                                                children: [
                                                                                  Text("${productLists?[0][index].attributeData[boxindex].boxPacking ?? ""} * ${productLists?[0][index].attributeData[boxindex].boxpcs ?? "1"} pcs", style: AppTextStyle.poppinsBold13(context: context, color: AppColors.secondaryColor, fontWeight: FontWeight.w600)),
                                                                                  Text("₹ ${productLists?[0][index].attributeData[boxindex].prprice.toString() ?? ""}/pcs"  , style: AppTextStyle.poppinsBold13(context: context, color: AppColors.secondaryColor, fontWeight: FontWeight.w500)),
                                                                                  Text(productLists?[0][index].attributeData[boxindex].color ?? "", style: AppTextStyle.poppinsBold13(context: context, color: AppColors.secondaryColor, fontWeight: FontWeight.w500)),
                                                                                ],
                                                                              ),
                                                                            ),
                                                                            SizedBox(
                                                                              width: width / 40,
                                                                            ),
                                                                            Container(
                                                                                height: height / 25,
                                                                                width: width / 6,
                                                                                decoration: BoxDecoration(color: AppColors.whiteColor, boxShadow: [BoxShadow()], borderRadius: BorderRadius.circular(height / 100), border: Border.all(color: AppColors.stroke)),
                                                                                child: Row(
                                                                                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                                                                                  children: [
                                                                                    InkWell(
                                                                                      onTap: () {
                                                                                        provider.decreaseCartQuantity(context: context, height: height, cartid: productLists?[0][index].attributeData[boxindex].attributeid.toString() ?? "", qty: productLists?[0][index].attributeData[boxindex].qty.toString() ?? "");
                                                                                      },
                                                                                      child: Text("-", style: AppTextStyle.poppinsRegular14(context: context)),
                                                                                    ),
                                                                                    Text(productLists?[0][index].attributeData[boxindex].qty.toString() ?? "", style: AppTextStyle.poppinsRegular14(context: context, fontWeight: FontWeight.w600)),
                                                                                    InkWell(
                                                                                      onTap: () {
                                                                                        provider.increaseCartQuantity(context: context, height: height, cartid: productLists?[0][index].attributeData[boxindex].attributeid.toString() ?? "", qty: productLists?[0][index].attributeData[boxindex].qty.toString() ?? "");
                                                                                      },
                                                                                      child: Text("+", style: AppTextStyle.poppinsRegular14(context: context)),
                                                                                    )
                                                                                  ],
                                                                                )),
                                                                            SizedBox(
                                                                              width: width / 40,
                                                                            ),
                                                                            Text("₹ ${productLists?[0][index].attributeData[boxindex].totalPrice}",
                                                                                style: AppTextStyle.poppinsBold13(context: context, color: AppColors.secondaryColor, fontWeight: FontWeight.w600)),
                                                                            SizedBox(
                                                                              width: width / 40,
                                                                            ),
                                                                            InkWell(
                                                                                onTap: () {
                                                                                  provider.deleteCartAttribute(context: context, globalcontext: dialogContext, height: height, cartattributeid: productLists?[0][index].attributeData[boxindex].attributeid.toString() ?? "");
                                                                                },
                                                                                child: Icon(
                                                                                  Icons.delete,
                                                                                  size: height / 40,
                                                                                ))
                                                                          ],
                                                                        );
                                                                      },
                                                                    )
                                                                  ],
                                                                ),
                                                              );
                                                            },
                                                          ),
                                                        ),
                                                      ],
                                                    ),
                                                  ),
                                                ],
                                              ),
                                            ),
                                            SizedBox(
                                              height: height / 35,
                                            ),
                                          ],
                                        ),
                                      )),
                                );
                              },
                            ),
                            SizedBox(
                              height: height / 40,
                            ),
                            Padding(
                              padding: EdgeInsets.symmetric(
                                  horizontal: width / 16.2),
                              child: Row(
                                children: [
                                  Icon(Icons.access_alarm),
                                  SizedBox(
                                    width: width / 50,
                                  ),
                                  Text(
                                    "Your last minute add-ons",
                                    style: AppTextStyle.poppinsRegular14(
                                        context: context,
                                        color: AppColors.secondaryColor,
                                        fontWeight: FontWeight.w500),
                                  ),
                                ],
                              ),
                            ),
                            SizedBox(
                              height: height / 70,
                            ),
                            Padding(
                              padding: EdgeInsets.symmetric(
                                  horizontal: width / 16.2),
                              child: Container(
                                  height: height / 3.2,
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
                                        Text(
                                          "Did you forget?",
                                          style: AppTextStyle.poppinsMedium15(
                                            context: context,
                                            color: AppColors.primaryColor,
                                          ),
                                        ),
                                        SizedBox(
                                          height: height / 35,
                                        ),
                                        Expanded(
                                          child: ListView.builder(
                                            scrollDirection: Axis.horizontal,
                                            itemCount: provider
                                                .relatedProductModel
                                                ?.data
                                                ?.length,
                                            shrinkWrap: true,
                                            itemBuilder: (context, index) {
                                              return InkWell(
                                                onTap: () {
                                                  Navigator.push(context,
                                                      MaterialPageRoute(
                                                    builder: (context) {
                                                      return DescriptionPageScreen(
                                                          productId: provider
                                                                  .relatedProductModel
                                                                  ?.data?[index]
                                                                  .id ??
                                                              0,
                                                          colorId: provider
                                                                  .relatedProductModel
                                                                  ?.data?[index]
                                                                  .colorid ??
                                                              "");
                                                    },
                                                  ));
                                                },
                                                child: Padding(
                                                  padding: EdgeInsets.only(
                                                      right: height / 50,
                                                      bottom: height / 50),
                                                  child: SizedBox(
                                                    width: width / 4,
                                                    child: Column(
                                                      crossAxisAlignment:
                                                          CrossAxisAlignment
                                                              .center,
                                                      children: [
                                                        Container(
                                                          height: height / 13,
                                                          width: height / 13,
                                                          decoration: BoxDecoration(
                                                              boxShadow: [
                                                                BoxShadow()
                                                              ],
                                                              borderRadius:
                                                                  BorderRadius
                                                                      .circular(
                                                                          height /
                                                                              66.6),
                                                              image: DecorationImage(
                                                                  image: NetworkImage(
                                                                      "${AppAssetImage.imageBaseUrl}storage/app/public/${(provider.relatedProductModel?.data?[index].image ?? "")}"),
                                                                  fit: BoxFit
                                                                      .fill)),
                                                        ),
                                                        SizedBox(
                                                          height: height / 75,
                                                        ),
                                                        Text(
                                                            provider
                                                                    .relatedProductModel
                                                                    ?.data?[
                                                                        index]
                                                                    .name ??
                                                                "",
                                                            textAlign: TextAlign
                                                                .center,
                                                            style: AppTextStyle.poppinsBold13(
                                                                context:
                                                                    context,
                                                                color: AppColors
                                                                    .secondaryColor,
                                                                fontWeight:
                                                                    FontWeight
                                                                        .w600)),
                                                        Text(
                                                            provider
                                                                    .relatedProductModel
                                                                    ?.data?[
                                                                        index]
                                                                    .price ??
                                                                "",
                                                            textAlign: TextAlign
                                                                .center,
                                                            style: AppTextStyle.poppinsBold13(
                                                                context:
                                                                    context,
                                                                color: AppColors
                                                                    .secondaryColor,
                                                                fontWeight:
                                                                    FontWeight
                                                                        .w400)),
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
                            ),
                            SizedBox(
                              height: height / 30,
                            ),
                            Padding(
                              padding: EdgeInsets.symmetric(
                                  horizontal: width / 16.2),
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
                                        Text(
                                          "SAVINGS CORNER",
                                          style: AppTextStyle.poppinsRegular14(
                                              context: context,
                                              color: AppColors.greyColor,
                                              fontWeight: FontWeight.w600),
                                        ),
                                        SizedBox(
                                          height: height / 80,
                                        ),
                                        Row(
                                          children: [
                                            Container(
                                                height: height / 40,
                                                width: height / 40,
                                                decoration: BoxDecoration(
                                                    shape: BoxShape.circle,
                                                    color:
                                                        AppColors.primaryColor),
                                                child: Center(
                                                    child: Icon(
                                                  Icons.percent,
                                                  color: AppColors.whiteColor,
                                                  size: height / 60,
                                                ))),
                                            SizedBox(
                                              width: width / 50,
                                            ),
                                            Expanded(
                                              child: Column(
                                                crossAxisAlignment:
                                                    CrossAxisAlignment.start,
                                                children: [
                                                  Text(
                                                    "Apply Coupon",
                                                    style: AppTextStyle
                                                        .poppinsBold13(
                                                            context: context,
                                                            color: AppColors
                                                                .secondaryColor),
                                                  ),
                                                  // Text(
                                                  //   "Save ₹ 50 with WELCOMEBACK50",
                                                  //   overflow:
                                                  //       TextOverflow.ellipsis,
                                                  //   style: AppTextStyle
                                                  //       .poppinsMedium15(
                                                  //           context: context,
                                                  //           color: AppColors
                                                  //               .secondaryColor),
                                                  // ),
                                                ],
                                              ),
                                            ),
                                            TextButton(
                                                onPressed: () {
                                                  Navigator.push(context,
                                                      MaterialPageRoute(
                                                    builder: (context) {
                                                      return CouponOfferScreen(
                                                        couponof: "admin",
                                                      );
                                                    },
                                                  ));
                                                },
                                                child: Text(
                                                  ">",
                                                  style: AppTextStyle
                                                      .poppinsMedium20(
                                                    context: context,
                                                    color:
                                                        AppColors.primaryColor,
                                                  ),
                                                ))
                                          ],
                                        ),
                                        // SizedBox(
                                        //   height: height / 100,
                                        // ),
                                        Divider(),
                                        Row(
                                          children: [
                                            Container(
                                                height: height / 40,
                                                width: height / 40,
                                                decoration: BoxDecoration(
                                                    shape: BoxShape.circle,
                                                    color:
                                                        AppColors.primaryColor),
                                                child: Center(
                                                    child: Icon(
                                                  Icons.percent,
                                                  color: AppColors.whiteColor,
                                                  size: height / 60,
                                                ))),
                                            SizedBox(
                                              width: width / 50,
                                            ),
                                            Expanded(
                                              child: Column(
                                                crossAxisAlignment:
                                                    CrossAxisAlignment.start,
                                                children: [
                                                  Text(
                                                    "Apply Seller Coupon",
                                                    style: AppTextStyle
                                                        .poppinsBold13(
                                                            context: context,
                                                            color: AppColors
                                                                .secondaryColor),
                                                  ),
                                                  // Text(
                                                  //   "Save ₹ 50 with WELCOMEBACK50",
                                                  //   overflow:
                                                  //       TextOverflow.ellipsis,
                                                  //   style: AppTextStyle
                                                  //       .poppinsMedium15(
                                                  //           context: context,
                                                  //           color: AppColors
                                                  //               .secondaryColor),
                                                  // ),
                                                ],
                                              ),
                                            ),
                                            TextButton(
                                                onPressed: () {
                                                  Navigator.push(context,
                                                      MaterialPageRoute(
                                                    builder: (context) {
                                                      return CouponOfferScreen(
                                                        couponof: "seller",
                                                      );
                                                    },
                                                  ));
                                                },
                                                child: Text(
                                                  ">",
                                                  style: AppTextStyle
                                                      .poppinsMedium20(
                                                    context: context,
                                                    color:
                                                        AppColors.primaryColor,
                                                  ),
                                                ))
                                          ],
                                        ),
                                        // SizedBox(
                                        //   height: height / 35,
                                        // ),
                                        SizedBox(
                                          height: height / 60,
                                        ),
                                      ],
                                    ),
                                  )),
                            ),
                            SizedBox(
                              height: height / 30,
                            ),
                            Padding(
                              padding: EdgeInsets.symmetric(
                                  horizontal: width / 16.2),
                              child: Text(
                                "Bill Details",
                                style: AppTextStyle.poppinsRegular14(
                                    context: context,
                                    color: AppColors.secondaryColor,
                                    fontWeight: FontWeight.w600),
                              ),
                            ),
                            SizedBox(
                              height: height / 70,
                            ),
                            Padding(
                              padding: EdgeInsets.symmetric(
                                  horizontal: width / 16.2),
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
                                              "MRP Total",
                                              style: AppTextStyle.poppinsBold13(
                                                  context: context,
                                                  color:
                                                      AppColors.secondaryColor,
                                                  fontWeight: FontWeight.w500),
                                            ),
                                            Text(
                                              "₹ ${provider.userCartModel?.netamount ?? ""}",
                                              style: AppTextStyle.poppinsBold13(
                                                  context: context,
                                                  color:
                                                      AppColors.secondaryColor,
                                                  fontWeight: FontWeight.w500),
                                            ),
                                          ],
                                        ),
                                        SizedBox(
                                          height: height / 60,
                                        ),
                                        provider.userCartModel?.igst == 0
                                            ? Column(
                                                children: [
                                                  Row(
                                                    mainAxisAlignment:
                                                        MainAxisAlignment
                                                            .spaceBetween,
                                                    children: [
                                                      Text(
                                                        "CGST",
                                                        style: AppTextStyle
                                                            .poppinsBold13(
                                                                context:
                                                                    context,
                                                                color: AppColors
                                                                    .secondaryColor,
                                                                fontWeight:
                                                                    FontWeight
                                                                        .w500),
                                                      ),
                                                      Text(
                                                        "9%",
                                                        style: AppTextStyle
                                                            .poppinsBold13(
                                                                context:
                                                                    context,
                                                                color: AppColors
                                                                    .secondaryColor,
                                                                fontWeight:
                                                                    FontWeight
                                                                        .w500),
                                                      ),
                                                      Text(
                                                        "+ ₹ ${provider.userCartModel?.cgst.toStringAsFixed(2) ?? ""}",
                                                        style: AppTextStyle
                                                            .poppinsBold13(
                                                                context:
                                                                    context,
                                                                color: AppColors
                                                                    .secondaryColor,
                                                                fontWeight:
                                                                    FontWeight
                                                                        .w500),
                                                      ),
                                                    ],
                                                  ),
                                                  Row(
                                                    mainAxisAlignment:
                                                        MainAxisAlignment
                                                            .spaceBetween,
                                                    children: [
                                                      Text(
                                                        "SGST",
                                                        style: AppTextStyle
                                                            .poppinsBold13(
                                                                context:
                                                                    context,
                                                                color: AppColors
                                                                    .secondaryColor,
                                                                fontWeight:
                                                                    FontWeight
                                                                        .w500),
                                                      ),
                                                      Text(
                                                        "9%",
                                                        style: AppTextStyle
                                                            .poppinsBold13(
                                                                context:
                                                                    context,
                                                                color: AppColors
                                                                    .secondaryColor,
                                                                fontWeight:
                                                                    FontWeight
                                                                        .w500),
                                                      ),
                                                      Text(
                                                        "+ ₹ ${provider.userCartModel?.sgst.toStringAsFixed(2) ?? ""}",
                                                        style: AppTextStyle
                                                            .poppinsBold13(
                                                                context:
                                                                    context,
                                                                color: AppColors
                                                                    .secondaryColor,
                                                                fontWeight:
                                                                    FontWeight
                                                                        .w500),
                                                      ),
                                                    ],
                                                  )
                                                ],
                                              )
                                            : Row(
                                                mainAxisAlignment:
                                                    MainAxisAlignment
                                                        .spaceBetween,
                                                children: [
                                                  Text(
                                                    "IGST",
                                                    style: AppTextStyle
                                                        .poppinsBold13(
                                                            context: context,
                                                            color: AppColors
                                                                .secondaryColor,
                                                            fontWeight:
                                                                FontWeight
                                                                    .w500),
                                                  ),
                                                  Text(
                                                    "18%",
                                                    style: AppTextStyle
                                                        .poppinsBold13(
                                                            context: context,
                                                            color: AppColors
                                                                .secondaryColor,
                                                            fontWeight:
                                                                FontWeight
                                                                    .w500),
                                                  ),
                                                  Text(
                                                    "+ ₹ ${provider.userCartModel?.igst.toStringAsFixed(2) ?? ""}",
                                                    style: AppTextStyle
                                                        .poppinsBold13(
                                                            context: context,
                                                            color: AppColors
                                                                .secondaryColor,
                                                            fontWeight:
                                                                FontWeight
                                                                    .w500),
                                                  ),
                                                ],
                                              ),
                                        SizedBox(
                                          height: height / 60,
                                        ),
                                        Row(
                                          mainAxisAlignment:
                                              MainAxisAlignment.spaceBetween,
                                          children: [
                                            Text(
                                              "Coupon Amount",
                                              style: AppTextStyle.poppinsBold13(
                                                  context: context,
                                                  color:
                                                      AppColors.secondaryColor,
                                                  fontWeight: FontWeight.w500),
                                            ),
                                            Text(
                                              "- ₹ (${provider.userCartModel?.combinecoupon.toString() ?? ""})",
                                              style: AppTextStyle.poppinsBold13(
                                                  context: context,
                                                  color:
                                                      AppColors.secondaryColor,
                                                  fontWeight: FontWeight.w500),
                                            ),
                                          ],
                                        ),
                                        Divider(),
                                        SizedBox(
                                          height: height / 60,
                                        ),
                                        Row(
                                          mainAxisAlignment:
                                              MainAxisAlignment.spaceBetween,
                                          children: [
                                            Text(
                                              "To Pay",
                                              style: AppTextStyle.poppinsBold13(
                                                  context: context,
                                                  color:
                                                      AppColors.secondaryColor,
                                                  fontWeight: FontWeight.w500),
                                            ),
                                            Text(
                                              "₹ ${provider.userCartModel?.grandTotal ?? ""}",
                                              style: AppTextStyle.poppinsBold13(
                                                  context: context,
                                                  color:
                                                      AppColors.secondaryColor,
                                                  fontWeight: FontWeight.w500),
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
                              height: height / 3,
                            )
                          ],
                        ),
                        Align(
                          alignment: Alignment.bottomCenter,
                          child: Container(
                            width: width,
                            height: height / 7,
                            decoration: BoxDecoration(
                                color: AppColors.whiteColor,
                                boxShadow: [BoxShadow()],
                                borderRadius:
                                    BorderRadius.circular(height / 40)),
                            child: Padding(
                              padding:
                                  EdgeInsets.symmetric(horizontal: height / 60),
                              child: Row(
                                children: [
                                  Text(
                                      "₹ ${provider.userCartModel?.grandTotal?.toStringAsFixed(2) ?? ""}",
                                      style: AppTextStyle.poppinsBold17(
                                          context: context,
                                          color: AppColors.secondaryColor)),
                                  SizedBox(
                                    width: width / 3,
                                  ),
                                  Expanded(
                                      child: RoundButton(
                                    title: "Proceed to pay",
                                    backgroundColor: AppColors.primaryColor,
                                    onPressed: () async {
                                      if (provider.selectedAddressId == null) {
                                        Utils.commonSnackBar(context,
                                            height: height,
                                            message: "Please Select Address");
                                        return;
                                      }

                                      // await provider.updateCartAddress(
                                      //   context: context,
                                      //   height: height,
                                      //   addressId: provider.selectedId!,
                                      // );

                                      // LOCK amount

                                      Navigator.push(
                                        context,
                                        MaterialPageRoute(
                                          builder: (_) => CheckOutScreen(),
                                        ),
                                      );
                                      // provider.selectedAddress == null ||
                                      //         provider.selectedAddress!.isEmpty
                                      //     ? Utils.commonSnackBar(context,
                                      //         height: height,
                                      //         message: "Please Select Address")
                                      //     : Navigator.push(context,
                                      //         MaterialPageRoute(
                                      //         builder: (context) {
                                      //           return CheckOutScreen();
                                      //         },
                                      //       ));
                                    },
                                  ))
                                ],
                              ),
                            ),
                          ),
                        )
                      ],
                    );
        },
      ),
    );
  }
}
