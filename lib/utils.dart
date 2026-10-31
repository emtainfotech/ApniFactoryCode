import 'package:apni_factory/provider/homeScreen_provider.dart';
import 'package:apni_factory/res/asset_image.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/textstyle.dart';

import 'package:apni_factory/screens/address_screen.dart';
import 'package:apni_factory/screens/faq_screen.dart';

import 'package:apni_factory/screens/order_history_screen.dart';
import 'package:apni_factory/screens/pages_screen.dart';
import 'package:apni_factory/screens/policies_screen.dart';

import 'package:apni_factory/screens/sign_in.dart';

import 'package:apni_factory/screens/wishlist_screen.dart';
import 'package:apni_factory/screens/your_profile_screen.dart';
import 'package:apni_factory/shared_pref.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:html/parser.dart';

class Utils {
  static Widget commonTextFormField(BuildContext context,
      {required double height,
      required double width,
      required String labelText,
      TextEditingController? controller,
      bool obscureText = false,
      Widget? suffixIcon,
      TextInputType keyboardType = TextInputType.name,
      bool enabled = true,
      int? maxLength,
      Function(String)? onChanged,
      Function()? ontap}) {
    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(8.0),
        boxShadow: [
          BoxShadow(
            color: AppColors.greyColor.withOpacity(0.2),
            spreadRadius: 0,
            blurRadius: 32,
          ),
        ],
      ),
      child: TextFormField(
        onTap: ontap,
        onChanged: onChanged,
        maxLength: maxLength,
        enabled: enabled,
        keyboardType: keyboardType,
        obscureText: obscureText,
        controller: controller,
        style: AppTextStyle.poppinsBold11(
            context: context,
            color: AppColors.secondaryColor,
            fontWeight: FontWeight.w500),
        cursorColor: AppColors.primaryColor,
        decoration: InputDecoration(
          suffix: suffixIcon,
          labelText: labelText,
          labelStyle: AppTextStyle.poppinsBold11(
              context: context,
              color: AppColors.neutralGrey,
              fontWeight: FontWeight.w400),
          contentPadding: EdgeInsets.symmetric(
              horizontal: width / 22.5, vertical: height / 44.4),
          filled: true,
          fillColor: AppColors.whiteColor,
          border: OutlineInputBorder(
              borderSide: BorderSide.none,
              borderRadius: BorderRadius.circular(height / 66.6)),
        ),
      ),
    );
  }

  static ScaffoldFeatureController commonSnackBar(
    BuildContext context, {
    required double height,
    required String message,
  }) {
    // double height = MediaQuery.of(context).size.height;
    return ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      // padding: EdgeInsets.all(height / 23.3),
      content: Center(
        child: Text(
          message,
          style: AppTextStyle.poppinsRegular16(
              context: context, color: AppColors.whiteColor),
        ),
      ),
      backgroundColor: AppColors.primaryColor,
      shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.only(
              topRight: Radius.circular(height / 100),
              topLeft: Radius.circular(height / 100))),
      elevation: 3,
      // margin: EdgeInsets.only(bottom: height / 1.2),
    ));
  }

  static Widget appBar({required double height}) {
    return AppBar(
      automaticallyImplyLeading: false,
      leading: Icon(
        Icons.menu,
        color: AppColors.lightText,
      ),
      backgroundColor: Colors.transparent,
      centerTitle: true,
      title: Image.asset(
        AppAssetImage.logo,
        height: height / 19.5,
      ),
      actions: [
        IconButton(
            onPressed: () {},
            icon: Icon(
              Icons.notifications,
              color: AppColors.lightText,
            ))
      ],
    );
  }

  static Future commonShowModalBottomSheet(
      BuildContext context, double height, double width,
      {required cameraOnPressed, required galleryOnPressed}) {
    return showModalBottomSheet(
      backgroundColor: AppColors.primaryColor,
      context: context,
      builder: (context) {
        return SizedBox(
          height: height / 12.1,
          width: width,
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceEvenly,
            children: [
              IconButton(
                  onPressed: cameraOnPressed,
                  icon: Icon(
                    Icons.camera_outlined,
                    color: AppColors.whiteColor,
                    size: 30,
                  )),
              IconButton(
                  onPressed: galleryOnPressed,
                  icon: Icon(
                    Icons.photo_library_outlined,
                    color: AppColors.whiteColor,
                    size: 30,
                  ))
            ],
          ),
        );
      },
    );
  }

  static Future commonShowDialog(
      {required BuildContext context,
      required double height,
      required String title,
      required void Function()? cancelButton,
      required void Function()? okayButton}) {
    return showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: Text(
            title,
            style: GoogleFonts.poppins(
                fontSize: height / 48, fontWeight: FontWeight.w500),
          ),
          actions: [
            TextButton(
                onPressed: cancelButton,
                // () {
                //   Navigator.pop(context);
                // },
                child: Text("Cancel",
                    style: AppTextStyle.poppinsRegular14(
                        context: context,
                        color: AppColors.neutralGrey,
                        fontWeight: FontWeight.w500))),
            TextButton(
                onPressed: okayButton,
                // () {
                //   SharedPref.clearToken().then((value) {
                //     Navigator.pushAndRemoveUntil(context, MaterialPageRoute(
                //       builder: (context) {
                //         return SignInScreen();
                //       },
                //     ), (route) => false);
                //   });
                // },

                child: Text("Okay",
                    style: AppTextStyle.poppinsRegular14(
                        context: context,
                        color: AppColors.primaryColor,
                        fontWeight: FontWeight.w600))),
          ],
        );
      },
    );
  }

  static htmlDocument(String html) {
    return parse(html);
  }

  static Drawer drawer(
      {required double height,
      required double width,
      required BuildContext context,
      required HomeScreenProvider provider}) {
    return Drawer(
        child: ListView(
      children: [
        Padding(
          padding: EdgeInsets.all(height / 30),
          child: Row(
            children: [
              CircleAvatar(
                child: Icon(
                  Icons.person,
                  color: AppColors.primaryColor,
                ),
              ),
              SizedBox(
                width: width / 30,
              ),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(provider.username ?? "",
                        style: AppTextStyle.poppinsMedium15(
                          context: context,
                          color: AppColors.secondaryColor,
                        )),
                    Text(provider.userEmail ?? "",
                        style: AppTextStyle.poppinsBold12(
                          context: context,
                          color: AppColors.secondaryColor,
                          fontWeight: FontWeight.w400,
                        )),
                  ],
                ),
              )
            ],
          ),
        ),
       

        ListTile(
          onTap: () {
            Navigator.push(context, MaterialPageRoute(
              builder: (context) {
                return PolicyScreen(provider: provider);
              },
            ));

          },
          trailing: Icon(
            Icons.arrow_forward_ios_rounded,
            size: height / 60,
          ),
          leading: Icon(
            Icons.check_circle,

            size: height / 40,
            color: AppColors.primaryColor,
          ),
          title: Text("Policy",
              style: AppTextStyle.poppinsRegular14(
                  context: context,
                  color: AppColors.secondaryColor,
                  fontWeight: FontWeight.w500)),
        ),
        ListTile(
          onTap: () {
            Navigator.push(context, MaterialPageRoute(
              builder: (context) {
                return YourProfileScreen();
              },
            ));
          },
          trailing: Icon(
            Icons.arrow_forward_ios_rounded,
            size: height / 60,
          ),
          leading: Icon(
            Icons.person,

            size: height / 40,
            color: AppColors.primaryColor,
          ),
          title: Text("My Account",
              style: AppTextStyle.poppinsRegular14(
                  context: context,
                  color: AppColors.secondaryColor,
                  fontWeight: FontWeight.w500)),
        ),

        ListTile(
          onTap: () {
            Navigator.push(context, MaterialPageRoute(
              builder: (context) {
                return OrderHistoryScreen();
              },
            ));
          },
          trailing: Icon(
            Icons.arrow_forward_ios_rounded,
            size: height / 60,
          ),
          leading: Icon(
            Icons.shop,
            size: height / 40,
             color: AppColors.primaryColor,
          ),
          title: Text("Your Order",
              style: AppTextStyle.poppinsRegular14(
                  context: context,
                  color: AppColors.secondaryColor,
                  fontWeight: FontWeight.w500)),
        ),

        ListTile(
          onTap: () {
            Navigator.push(context, MaterialPageRoute(
              builder: (context) {
                return AddressScreen();
              },
            ));
          },
          trailing: Icon(
            Icons.arrow_forward_ios_rounded,
            size: height / 60,
          ),
          leading: Icon(
            Icons.shop,
            size: height / 40,
             color: AppColors.primaryColor,
          ),
          title: Text("Address",
              style: AppTextStyle.poppinsRegular14(
                  context: context,
                  color: AppColors.secondaryColor,
                  fontWeight: FontWeight.w500)),
        ),

        // ListTile(
        //   onTap: () {
        //     Navigator.push(context, MaterialPageRoute(
        //       builder: (context) {
        //         return HelpScreen();
        //       },
        //     ));
        //   },
        //   trailing: Icon(
        //     Icons.arrow_forward_ios_rounded,
        //     size: height / 60,
        //   ),
        //   leading: Icon(
        //     Icons.help,
        //     size: height / 40,
        //   ),
        //   title: Text("Help",
        //       style: AppTextStyle.poppinsRegular14(
        //           context: context,
        //           color: AppColors.secondaryColor,
        //           fontWeight: FontWeight.w500)),
        // ),

        ListTile(
          onTap: () {
            Navigator.push(context, MaterialPageRoute(
              builder: (context) {
                return FaqScreen();
              },
            ));
          },
          trailing: Icon(
            Icons.arrow_forward_ios_rounded,
            size: height / 60,
          ),
          leading: Icon(
            Icons.question_answer,
            size: height / 40,
             color: AppColors.primaryColor,
          ),
          title: Text("FAQ",
              style: AppTextStyle.poppinsRegular14(
                  context: context,
                  color: AppColors.secondaryColor,
                  fontWeight: FontWeight.w500)),
        ),

        ListTile(
          onTap: () {
            Navigator.push(context, MaterialPageRoute(
              builder: (context) {
                return WishlistScreen();
              },
            ));
          },
          trailing: Icon(
            Icons.arrow_forward_ios_rounded,
            size: height / 60,
          ),
          leading: Icon(
            Icons.card_giftcard_outlined,
            size: height / 40,
             color: AppColors.primaryColor,
          ),
          title: Text("Wishlist",
              style: AppTextStyle.poppinsRegular14(
                  context: context,
                  color: AppColors.secondaryColor,
                  fontWeight: FontWeight.w500)),
        ),

        // ListTile(
        //   trailing: Icon(
        //     Icons.arrow_forward_ios_rounded,
        //     size: height / 45,
        //   ),
        //   leading: Icon(Icons.person_pin_circle_outlined),
        //   title: Text("Chatbot",
        //       style: AppTextStyle.poppinsRegular16(
        //           context: context,
        //           color: AppColors.secondaryColor,
        //           fontWeight: FontWeight.w500)),
        // ),

        ListTile(
          onTap: () {
            Utils.commonShowDialog(
              context: context,
              height: height,
              title: "Are you sure you want to logout ?",
              cancelButton: () {
                Navigator.pop(context);
              },
              okayButton: () {
                SharedPref.clearToken().then((value) {
                  Navigator.pushAndRemoveUntil(context, MaterialPageRoute(
                    builder: (context) {
                      return SignInScreen();
                    },
                  ), (route) => false);
                });
              },
            );
          },
          leading: Icon(
            Icons.logout,
            size: height / 40,
             color: AppColors.primaryColor,
          ),
          title: Text("Log Out",
              style: AppTextStyle.poppinsRegular14(
                  context: context,
                  color: AppColors.secondaryColor,
                  fontWeight: FontWeight.w500)),
        )
      ],
    ));
  }
  static Widget commonDropDown(
      {required BuildContext context,
      required double height,
      required double width,
      required List list,
      required String? dropDownValue,
      required void Function(String?)? onChanged}) {
    return Container(
      padding: EdgeInsets.symmetric(horizontal: height / 43),
      height: height / 17,
      width: width,
      decoration: BoxDecoration(
        color: AppColors.whiteColor,
        borderRadius: BorderRadius.circular(8.0),
        boxShadow: [
          BoxShadow(
            color: AppColors.greyColor.withOpacity(0.2),
            spreadRadius: 0,
            blurRadius: 32,
          ),
        ],
      ),
      child: DropdownButton<String>(
        dropdownColor: AppColors.whiteColor,
        isExpanded: true,
        style: AppTextStyle.poppinsRegular14(
            context: context, color: AppColors.secondaryColor),
        value: dropDownValue,
        onChanged: onChanged,
        items: list
            .map<DropdownMenuItem<String>>((value) => DropdownMenuItem<String>(
                  value: value,
                  child: Text(
                    value!,
                    overflow: TextOverflow.ellipsis,
                    style: dropDownValue == list[0]
                        ? AppTextStyle.poppinsRegular14(
                            context: context, color: AppColors.neutralGrey)
                        : AppTextStyle.poppinsRegular14(
                            context: context, color: AppColors.neutralGrey),
                    textAlign: TextAlign.center,
                  ),
                ))
            .toList(),
        icon: const Icon(
          Icons.keyboard_arrow_down_sharp,
          color: AppColors.blackColor,
        ),
        underline: const SizedBox(),
      ),
    );
  }
}
