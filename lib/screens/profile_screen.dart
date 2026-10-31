import 'package:apni_factory/res/asset_image.dart';
import 'package:apni_factory/screens/change_password_screen.dart';
import 'package:apni_factory/screens/faq_screen.dart';
import 'package:apni_factory/screens/favourite_screen.dart';
import 'package:apni_factory/screens/pages_screen.dart';
import 'package:apni_factory/screens/send_feedback_screen.dart';
import 'package:apni_factory/screens/setting_screen.dart';
import 'package:apni_factory/screens/sign_in.dart';
import 'package:apni_factory/screens/ticket_screen.dart';
import 'package:apni_factory/screens/order_history_screen.dart';
import 'package:apni_factory/screens/web_vview_screen.dart';
import 'package:apni_factory/screens/wishlist_screen.dart';
import 'package:apni_factory/screens/your_profile_screen.dart';
import 'package:apni_factory/shared_pref.dart';
import 'package:apni_factory/utils.dart';
import 'package:flutter/material.dart';
import '../res/colors.dart';
import '../res/textstyle.dart';
import '../res/widgets/round_button.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  @override
  void initState() {
    SharedPref.getUserName().then((value) {
      userName = value;
      setState(() {});
    });

    // TODO: implement initState
    super.initState();
  }

  String? userName;
  @override
  Widget build(BuildContext globalContext) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(
      body: ListView(
        children: [
          SizedBox(
            height: height / 21,
          ),
          Padding(
            padding: EdgeInsets.symmetric(
              horizontal: width / 16.2,
            ),
            child: Card(
              child: Container(
                height: height / 5,
                width: width,
                decoration: BoxDecoration(
                    boxShadow: [
                      BoxShadow(
                          blurRadius: 32,
                          color: AppColors.blackColor.withOpacity(0.1))
                    ],
                    // border: Border.all(color: AppColors.primaryColor),
                    borderRadius: BorderRadius.circular(height / 66.6),
                    color: AppColors.whiteColor),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  // crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    Image.asset(
                      AppAssetImage.logo,
                      height: height / 30,
                    ),
                    Container(
                        height: height / 14.2,
                        width: height / 14.2,
                        decoration: BoxDecoration(
                            shape: BoxShape.circle, color: AppColors.greyColor),
                        child: Center(
                          child: Text(
                            userName?[0] ?? "",
                            style: AppTextStyle.poppinsBold24(context: context),
                          ),
                        )),
                    Text(
                      userName ?? "",
                      style: AppTextStyle.poppinsRegular16(
                          context: context,
                          fontWeight: FontWeight.w600,
                          color: AppColors.secondaryColor),
                    ),

                    // RoundButton(
                    //   title: "Join apni factory primumm",
                    //   onPressed: () {},
                    // )
                  ],
                ),
              ),
            ),
          ),
          SizedBox(
            height: height / 21,
          ),
          Padding(
            padding: EdgeInsets.symmetric(horizontal: width / 16.2),
            child: Container(
              height: height / 17.7,
              width: width,
              decoration: BoxDecoration(
                  boxShadow: [
                    BoxShadow(
                        blurRadius: 32,
                        color: AppColors.blackColor.withOpacity(0.1))
                  ], // border: Border.all(color: AppColors.primaryColor),
                  borderRadius: BorderRadius.circular(height / 66.6),
                  color: AppColors.whiteColor),
              child: Padding(
                padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                child: InkWell(
                  onTap: () {
                    Navigator.push(globalContext, MaterialPageRoute(
                      builder: (context) {
                        return YourProfileScreen();
                      },
                    ));
                  },
                  child: Row(
                    children: [
                      Icon(Icons.person),
                      SizedBox(
                        width: width / 20,
                      ),
                      Text("Your Profile",
                          style: AppTextStyle.poppinsRegular14(
                              context: context,
                              color: AppColors.secondaryColor)),
                      Spacer(),
                      Text(">",
                          style: AppTextStyle.poppinsMedium15(
                              context: context,
                              color: AppColors.secondaryColor)),
                    ],
                  ),
                ),
              ),
            ),
          ),
          SizedBox(
            height: height / 42,
          ),
          Padding(
            padding: EdgeInsets.symmetric(horizontal: width / 16.2),
            child: Container(
              height: height / 17.7,
              width: width,
              decoration: BoxDecoration(
                  boxShadow: [
                    BoxShadow(
                        blurRadius: 32,
                        color: AppColors.blackColor.withOpacity(0.1))
                  ], // border: Border.all(color: AppColors.primaryColor),
                  borderRadius: BorderRadius.circular(height / 66.6),
                  color: AppColors.whiteColor),
              child: Padding(
                padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                child: InkWell(
                  onTap: () {
                    Navigator.push(globalContext, MaterialPageRoute(
                      builder: (context) {
                        return ChangePasswordScreen();
                      },
                    ));
                  },
                  child: Row(
                    children: [
                      Icon(Icons.lock),
                      SizedBox(
                        width: width / 20,
                      ),
                      Text("Change Password",
                          style: AppTextStyle.poppinsRegular14(
                            context: context,
                          )),
                      Spacer(),
                      Text(">",
                          style: AppTextStyle.poppinsMedium15(
                              context: context,
                              color: AppColors.secondaryColor)),
                    ],
                  ),
                ),
              ),
            ),
          ),
          SizedBox(
            height: height / 42,
          ),
          // Padding(
          //   padding: EdgeInsets.symmetric(horizontal: width / 16.2),
          //   child: Container(
          //     height: height / 17.7,
          //     width: width,
          //     decoration: BoxDecoration(
          //         boxShadow: [
          //           BoxShadow(
          //               blurRadius: 32,
          //               color: AppColors.blackColor.withOpacity(0.1))
          //         ], // border: Border.all(color: AppColors.primaryColor),
          //         borderRadius: BorderRadius.circular(height / 66.6),
          //         color: AppColors.whiteColor),
          //     child: Padding(
          //       padding: EdgeInsets.symmetric(horizontal: width / 16.2),
          //       child: Row(
          //         children: [
          //           Icon(Icons.star),
          //           SizedBox(
          //             width: width / 20,
          //           ),
          //           Text("Your Ratinge",
          //               style: AppTextStyle.poppinsMedium15(
          //                   context: context, color: AppColors.secondaryColor)),
          //           Spacer(),
          //           Text(">",
          //               style: AppTextStyle.poppinsMedium15(
          //                   context: context, color: AppColors.secondaryColor)),
          //         ],
          //       ),
          //     ),
          //   ),
          // ),
          // SizedBox(
          //   height: height / 42,
          // ),
          Padding(
            padding: EdgeInsets.symmetric(
              horizontal: width / 16.2,
            ),
            child: Card(
              child: Container(
                // height: height / 5,
                width: width,
                decoration: BoxDecoration(
                    boxShadow: [
                      BoxShadow(
                          blurRadius: 32,
                          color: AppColors.blackColor.withOpacity(0.1))
                    ],
                    // border: Border.all(color: AppColors.primaryColor),
                    borderRadius: BorderRadius.circular(height / 66.6),
                    color: AppColors.whiteColor),
                child: Padding(
                  padding: EdgeInsets.symmetric(
                    horizontal: width / 16.2,
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      SizedBox(
                        height: height / 60,
                      ),
                      Text(
                        "Orders",
                        style: AppTextStyle.poppinsRegular16(
                            context: context,
                            fontWeight: FontWeight.w600,
                            color: AppColors.secondaryColor),
                      ),
                      SizedBox(
                        height: height / 60,
                      ),
                      InkWell(
                        onTap: () {
                          Navigator.push(globalContext, MaterialPageRoute(
                            builder: (context) {
                              return OrderHistoryScreen();
                            },
                          ));
                        },
                        child: Row(
                          children: [
                            Container(
                              height: height / 57.1,
                              width: height / 57.1,
                              decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: AppColors.stroke),
                              child: Icon(
                                Icons.card_travel_rounded,
                                size: height / 80,
                              ),
                            ),
                            SizedBox(
                              width: width / 50,
                            ),
                            Text(
                              "Your order",
                              style: AppTextStyle.poppinsRegular14(
                                  context: context),
                            ),
                            Spacer(),
                            Icon(
                              Icons.arrow_forward_ios,
                              size: height / 50,
                            )
                          ],
                        ),
                      ),
                      Divider(),
                      SizedBox(
                        height: height / 60,
                      ),
                      InkWell(
                        onTap: () {
                          Navigator.push(globalContext, MaterialPageRoute(
                            builder: (context) {
                              return WishlistScreen();
                            },
                          ));
                        },
                        child: Row(
                          children: [
                            Container(
                              height: height / 57.1,
                              width: height / 57.1,
                              decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: AppColors.stroke),
                              child: Icon(
                                Icons.favorite_border,
                                size: height / 80,
                              ),
                            ),
                            SizedBox(
                              width: width / 50,
                            ),
                            Text(
                              "Favourite",
                              style: AppTextStyle.poppinsRegular14(
                                  context: context),
                            ),
                            Spacer(),
                            Icon(
                              Icons.arrow_forward_ios,
                              size: height / 50,
                            )
                          ],
                        ),
                      ),
                      Divider(),
                      // SizedBox(
                      //   height: height / 60,
                      // ),
                      // InkWell(
                      //   onTap: () {},
                      //   child: Row(
                      //     children: [
                      //       Container(
                      //         height: height / 57.1,
                      //         width: height / 57.1,
                      //         decoration: BoxDecoration(
                      //             shape: BoxShape.circle,
                      //             color: AppColors.stroke),
                      //         child: Icon(
                      //           Icons.book_online,
                      //           size: height / 80,
                      //         ),
                      //       ),
                      //       SizedBox(
                      //         width: width / 50,
                      //       ),
                      //       Text(
                      //         "Online Help",
                      //         style: AppTextStyle.poppinsRegular14(
                      //             context: context),
                      //       ),
                      //       Spacer(),
                      //       Icon(
                      //         Icons.arrow_forward_ios,
                      //         size: height / 50,
                      //       )
                      //     ],
                      //   ),
                      // ),
                      // Divider(),
                    ],
                  ),
                ),
              ),
            ),
          ),
          SizedBox(
            height: height / 42,
          ),
          Padding(
            padding: EdgeInsets.symmetric(
              horizontal: width / 16.2,
            ),
            child: Card(
              child: Container(
                height: height / 4,
                width: width,
                decoration: BoxDecoration(
                    boxShadow: [
                      BoxShadow(
                          blurRadius: 32,
                          color: AppColors.blackColor.withOpacity(0.1))
                    ],
                    // border: Border.all(color: AppColors.primaryColor),
                    borderRadius: BorderRadius.circular(height / 66.6),
                    color: AppColors.whiteColor),
                child: Padding(
                  padding: EdgeInsets.symmetric(
                    horizontal: width / 16.2,
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      SizedBox(
                        height: height / 60,
                      ),
                      Text(
                        "Support",
                        style: AppTextStyle.poppinsRegular16(
                            context: context,
                            fontWeight: FontWeight.w600,
                            color: AppColors.secondaryColor),
                      ),
                      SizedBox(
                        height: height / 60,
                      ),
                      InkWell(
                        onTap: () {
                          Navigator.push(context, MaterialPageRoute(
                            builder: (context) {
                              return TicketScreen();
                            },
                          ));
                        },
                        child: Row(
                          children: [
                            Container(
                              height: height / 57.1,
                              width: height / 57.1,
                              decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: AppColors.stroke),
                              child: Icon(
                                Icons.card_travel_rounded,
                                size: height / 80,
                              ),
                            ),
                            SizedBox(
                              width: width / 50,
                            ),
                            Text(
                              "Your tickets",
                              style: AppTextStyle.poppinsRegular14(
                                  context: context),
                            ),
                            Spacer(),
                            Icon(
                              Icons.arrow_forward_ios,
                              size: height / 50,
                            )
                          ],
                        ),
                      ),
                      Divider(),
                      SizedBox(
                        height: height / 60,
                      ),
                      InkWell(
                        onTap: () {
                          Navigator.push(context, MaterialPageRoute(
                            builder: (context) {
                              return PagesScreen(slug: "helpforapp");
                            },
                          ));
                        },
                        child: Row(
                          children: [
                            Container(
                              height: height / 57.1,
                              width: height / 57.1,
                              decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: AppColors.stroke),
                              child: Icon(
                                Icons.help,
                                size: height / 80,
                              ),
                            ),
                            SizedBox(
                              width: width / 50,
                            ),
                            Text(
                              "Help",
                              style: AppTextStyle.poppinsRegular14(
                                  context: context),
                            ),
                            Spacer(),
                            Icon(
                              Icons.arrow_forward_ios,
                              size: height / 50,
                            )
                          ],
                        ),
                      ),
                      Divider(),
                      SizedBox(
                        height: height / 60,
                      ),
                      InkWell(
                        onTap: () {
                          Navigator.push(globalContext, MaterialPageRoute(
                            builder: (context) {
                              return FaqScreen();
                            },
                          ));
                        },
                        child: Row(
                          children: [
                            Container(
                              height: height / 57.1,
                              width: height / 57.1,
                              decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: AppColors.stroke),
                              child: Icon(
                                Icons.question_mark,
                                size: height / 80,
                              ),
                            ),
                            SizedBox(
                              width: width / 50,
                            ),
                            Text(
                              "Frequently asked question",
                              style: AppTextStyle.poppinsRegular14(
                                  context: context),
                            ),
                            Spacer(),
                            Icon(
                              Icons.arrow_forward_ios,
                              size: height / 50,
                            )
                          ],
                        ),
                      ),
                      Divider(),
                    ],
                  ),
                ),
              ),
            ),
          ),
          SizedBox(
            height: height / 42,
          ),
          Padding(
            padding: EdgeInsets.symmetric(
              horizontal: width / 16.2,
            ),
            child: Card(
              child: Container(
                // height: height / 3.2,
                width: width,
                decoration: BoxDecoration(
                    boxShadow: [
                      BoxShadow(
                          blurRadius: 32,
                          color: AppColors.blackColor.withOpacity(0.1))
                    ],
                    // border: Border.all(color: AppColors.primaryColor),
                    borderRadius: BorderRadius.circular(height / 66.6),
                    color: AppColors.whiteColor),
                child: Padding(
                  padding: EdgeInsets.symmetric(
                    horizontal: width / 16.2,
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      SizedBox(
                        height: height / 60,
                      ),
                      Text(
                        "More",
                        style: AppTextStyle.poppinsRegular16(
                            context: context,
                            fontWeight: FontWeight.w600,
                            color: AppColors.secondaryColor),
                      ),
                      SizedBox(
                        height: height / 60,
                      ),
                      InkWell(
                        onTap: () {
                          Navigator.push(context, MaterialPageRoute(
                            builder: (context) {
                              return PagesScreen(slug: "aboutus");
                            },
                          ));
                        },
                        child: Row(
                          children: [
                            Container(
                              height: height / 57.1,
                              width: height / 57.1,
                              decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: AppColors.stroke),
                              child: Icon(
                                Icons.info_outline,
                                size: height / 80,
                              ),
                            ),
                            SizedBox(
                              width: width / 50,
                            ),
                            Text(
                              "About Us",
                              style: AppTextStyle.poppinsRegular14(
                                  context: context),
                            ),
                            Spacer(),
                            Icon(
                              Icons.arrow_forward_ios,
                              size: height / 50,
                            )
                          ],
                        ),
                      ),
                      Divider(),
                      SizedBox(
                        height: height / 60,
                      ),
                      // InkWell(
                      //   onTap: () {
                      //     Navigator.push(globalContext, MaterialPageRoute(
                      //       builder: (context) {
                      //         return SettingScreen();
                      //       },
                      //     ));
                      //   },
                      //   child: Row(
                      //     children: [
                      //       Container(
                      //         height: height / 57.1,
                      //         width: height / 57.1,
                      //         decoration: BoxDecoration(
                      //             shape: BoxShape.circle,
                      //             color: AppColors.stroke),
                      //         child: Icon(
                      //           Icons.settings,
                      //           size: height / 80,
                      //         ),
                      //       ),
                      //       SizedBox(
                      //         width: width / 50,
                      //       ),
                      //       Text(
                      //         "Setting",
                      //         style: AppTextStyle.poppinsRegular14(
                      //             context: context),
                      //       ),
                      //       Spacer(),
                      //       Icon(
                      //         Icons.arrow_forward_ios,
                      //         size: height / 50,
                      //       )
                      //     ],
                      //   ),
                      // ),
                      // Divider(),
                      // SizedBox(
                      //   height: height / 60,
                      // ),
                      InkWell(
                        onTap: () {
                          Navigator.push(globalContext, MaterialPageRoute(
                            builder: (context) {
                              return SendFeedbackScreen();
                            },
                          ));
                        },
                        child: Row(
                          children: [
                            Container(
                              height: height / 57.1,
                              width: height / 57.1,
                              decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: AppColors.stroke),
                              child: Icon(
                                Icons.send,
                                size: height / 80,
                              ),
                            ),
                            SizedBox(
                              width: width / 50,
                            ),
                            Text(
                              "Send Feedback",
                              style: AppTextStyle.poppinsRegular14(
                                  context: context),
                            ),
                            Spacer(),
                            Icon(
                              Icons.arrow_forward_ios,
                              size: height / 50,
                            )
                          ],
                        ),
                      ),

 Divider(),
                      SizedBox(
                        height: height / 60,
                      ),
                      InkWell(
                        onTap: () {
                          Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => const DataDeletionWebView(),
      ),
    );
                         
                        },
                        child: Row(
                          children: [
                            Container(
                              height: height / 57.1,
                              width: height / 57.1,
                              decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: AppColors.stroke),
                              child: Icon(
                                Icons.delete_outline_sharp,
                                size: height / 80,
                              ),
                            ),
                            SizedBox(
                              width: width / 50,
                            ),
                            Text(
                              "Delete Account",
                              style: AppTextStyle.poppinsRegular14(
                                  context: context),
                            ),
                            Spacer(),
                            Icon(
                              Icons.arrow_forward_ios,
                              size: height / 50,
                            )
                          ],
                        ),
                      ),


                      Divider(),
                      SizedBox(
                        height: height / 60,
                      ),
                      InkWell(
                        onTap: () {
                          Utils.commonShowDialog(
                            context: context,
                            height: height,
                            title: "Are you sure you want to logout ?",
                            cancelButton: () {
                              Navigator.pop(globalContext);
                            },
                            okayButton: () async {
                              await SharedPref.setIsLoggedIn(false);
                              await SharedPref.clearToken();

                              Navigator.pushAndRemoveUntil(globalContext,
                                  MaterialPageRoute(
                                builder: (context) {
                                  return SignInScreen();
                                },
                              ), (route) => false);
                            },
                          );
                          // showDialog(
                          //   context: context,
                          //   builder: (context) {
                          //     return AlertDialog(
                          //       title: Text(
                          //         "Are you sure you want to logout ?",
                          //         style: GoogleFonts.poppins(
                          //             fontSize: height / 48,
                          //             fontWeight: FontWeight.w500),
                          //       ),
                          //       actions: [
                          //         TextButton(
                          //             onPressed: () {
                          //               Navigator.pop(context);
                          //             },
                          //             child: Text("Cancel",
                          //                 style: AppTextStyle.poppinsRegular14(
                          //                     context: context,
                          //                     color: AppColors.neutralGrey,
                          //                     fontWeight: FontWeight.w500))),
                          //         TextButton(
                          //             onPressed: () {
                          //               SharedPref.clearToken().then((value) {
                          //                 Navigator.pushAndRemoveUntil(context,
                          //                     MaterialPageRoute(
                          //                   builder: (context) {
                          //                     return SignInScreen();
                          //                   },
                          //                 ), (route) => false);
                          //               });
                          //             },
                          //             child: Text("Okay",
                          //                 style: AppTextStyle.poppinsRegular14(
                          //                     context: context,
                          //                     color: AppColors.primaryColor,
                          //                     fontWeight: FontWeight.w600))),
                          //       ],
                          //     );
                          //   },
                          // );
                        },
                        child: Row(
                          children: [
                            Container(
                              height: height / 57.1,
                              width: height / 57.1,
                              decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: AppColors.stroke),
                              child: Icon(
                                Icons.logout,
                                size: height / 80,
                              ),
                            ),
                            SizedBox(
                              width: width / 50,
                            ),
                            Text(
                              "Logout",
                              style: AppTextStyle.poppinsRegular14(
                                  context: context),
                            ),
                            Spacer(),
                            Icon(
                              Icons.arrow_forward_ios,
                              size: height / 50,
                            )
                          ],
                        ),
                      ),
                      Divider(),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
