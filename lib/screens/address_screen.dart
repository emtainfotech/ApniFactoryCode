import 'package:apni_factory/provider/homeScreen_provider.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/screens/all_screen.dart';
import 'package:apni_factory/screens/home_screen.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class AddressScreen extends StatefulWidget {
  const AddressScreen({super.key});

  @override
  State<AddressScreen> createState() => _AddressScreenState();
}

class _AddressScreenState extends State<AddressScreen> {
  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(
      appBar: AppBar(
        title: Text(
          "Address",
          style: AppTextStyle.poppinsRegular16(
              context: context,
              color: AppColors.secondaryColor,
              fontWeight: FontWeight.w500),
        ),
      ),
      body: Consumer<HomeScreenProvider>(
        builder: (context, provider, child) {
          return Padding(
            padding: EdgeInsets.symmetric(horizontal: height / 30),
            child: ListView(
              children: [
                SizedBox(
                  height: height / 60,
                ),
                Text(
                  "Communication Address",
                  style: AppTextStyle.poppinsRegular16(
                      context: context,
                      fontWeight: FontWeight.w500,
                      color: AppColors.secondaryColor),
                ),
                SizedBox(
                  height: height / 80,
                ),

                provider.getAddressListModel!.data!
                        .where((e) => e.type == "personal")
                        .toList()
                        .isEmpty
                    ? Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Text(
                              "Your Communication address is not updated yet.",
                              style: AppTextStyle.poppinsBold13(
                                  context: context,
                                  color: AppColors.secondaryColor,
                                  fontWeight: FontWeight.w500),
                            ),
                          ),
                          TextButton(
                              onPressed: () {
                                Navigator.pop(context);
                                Navigator.pop(context);
                              },
                              child: Text(
                                "Update",
                                style: AppTextStyle.poppinsRegular14(
                                    context: context,
                                    color: AppColors.primaryColor,
                                    fontWeight: FontWeight.w500),
                              ))
                        ],
                      )
                    : ListView.builder(
                        shrinkWrap: true,
                        physics: NeverScrollableScrollPhysics(),
                        itemCount: provider.getAddressListModel?.data
                                ?.where((e) => e.type == "personal")
                                .length ??
                            0,
                        itemBuilder: (context, index) {
                          final personalList = provider
                              .getAddressListModel!.data!
                              .where((e) => e.type == "personal")
                              .toList();

                          final address = personalList[index];

                          return ListTile(
                            leading: Icon(
                              Icons.location_on_outlined,
                              color: AppColors.primaryColor,
                            ),
                            title: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  "${address.name}",
                                  style: AppTextStyle.poppinsBold13(
                                      context: context,
                                      color: AppColors.secondaryColor,
                                      fontWeight: FontWeight.w500),
                                ),
                                Text(
                                  "${address.landmark1}, ${address.landmark2}, ${address.city}, ${address.state}, ${address.country}, - ${address.pincode}",
                                  style: AppTextStyle.poppinsBold13(
                                      context: context,
                                      color: AppColors.secondaryColor,
                                      fontWeight: FontWeight.w500),
                                ),
                              ],
                            ),
                          );
                        },
                      ),
                SizedBox(
                  height: height / 30,
                ),
                // ListView.builder(
                //   itemCount: provider.getAddressListModel?.data?.length,
                //   shrinkWrap: true,
                //   itemBuilder: (context, index) {
                //     return provider.getAddressListModel?.data?[index].type ==
                //             "personal"
                //         ? ListTile(
                //             title: Text(provider.addressList?[index]),
                //           )
                //         : SizedBox.shrink();
                //   },
                // ),
                Divider(),
                SizedBox(
                  height: height / 60,
                ),
                Text("Delivery Address",
                    style: AppTextStyle.poppinsRegular16(
                        context: context,
                        fontWeight: FontWeight.w500,
                        color: AppColors.secondaryColor)),
                provider.getAddressListModel!.data!
                        .where((e) => e.type == "transport")
                        .toList()
                        .isEmpty
                    ? Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Text(
                              "Your delivery address is not updated yet.",
                              style: AppTextStyle.poppinsBold13(
                                  context: context,
                                  color: AppColors.secondaryColor,
                                  fontWeight: FontWeight.w500),
                            ),
                          ),
                          // TextButton(
                          //     onPressed: () {
                          //       Navigator.pop(context);
                          //       Navigator.pop(context);
                          //     },
                          //     child: Text(
                          //       "Update",
                          //       style: AppTextStyle.poppinsRegular14(
                          //           context: context,
                          //           color: AppColors.primaryColor,
                          //           fontWeight: FontWeight.w500),
                          //     ))
                        ],
                      )
                    : ListView.builder(
                        shrinkWrap: true,
                        physics: NeverScrollableScrollPhysics(),
                        itemCount: provider.getAddressListModel?.data
                                ?.where((e) => e.type == "transport")
                                .length ??
                            0,
                        itemBuilder: (context, index) {
                          final transportList = provider
                              .getAddressListModel!.data!
                              .where((e) => e.type == "transport")
                              .toList();

                          final address = transportList[index];

                          return ListTile(
                            leading: Icon(
                              Icons.location_on_outlined,
                              color: AppColors.primaryColor,
                            ),
                            title: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  "${address.name}",
                                  style: AppTextStyle.poppinsBold13(
                                      context: context,
                                      color: AppColors.secondaryColor,
                                      fontWeight: FontWeight.w500),
                                ),
                                Text(
                                  "${address.landmark1}, ${address.landmark2}, ${address.city}, ${address.state}, ${address.country}, - ${address.pincode}",
                                  style: AppTextStyle.poppinsBold13(
                                      context: context,
                                      color: AppColors.secondaryColor,
                                      fontWeight: FontWeight.w500),
                                ),
                              ],
                            ),
                          );
                        },
                      ),
                // ListView.builder(
                //   itemCount: provider.getAddressListModel?.data?.length,
                //   shrinkWrap: true,
                //   itemBuilder: (context, index) {
                //     return provider.getAddressListModel?.data?[index].type ==
                //             "transport"
                //         ? ListTile(
                //             title: Text(provider.addressList?[index]),
                //           )
                //         : SizedBox.shrink();
                //   },
                // )
              ],
            ),
          );
        },
      ),
    );
  }
}
