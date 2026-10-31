import 'package:flutter/material.dart';
import '../res/colors.dart';
import '../res/textstyle.dart';
import '../res/widgets/back_button_widget.dart';

class SettingScreen extends StatefulWidget {
  const SettingScreen({super.key});

  @override
  State<SettingScreen> createState() => _SettingScreenState();
}

class _SettingScreenState extends State<SettingScreen> {
  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(
      body: ListView(
        children: [
          SizedBox(
            height: height / 26.6,
          ),
          Padding(
            padding: EdgeInsets.symmetric(horizontal: width / 16.2),
            child: BackButtonWidget(
              title: "Settings",
            ),
          ),
          SizedBox(
            height: height / 23.5,
          ),
          Padding(
            padding: EdgeInsets.symmetric(horizontal: width / 16.2),
            child: Column(
              children: [
                Row(
                  children: [
                    Expanded(
                        child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          "Edit Profile",
                          style: AppTextStyle.poppinsRegular16(
                              context: context,
                              fontWeight: FontWeight.w600,
                              color: AppColors.secondaryColor),
                        ),
                        Text(
                          "Change your name, description and photo",
                          style:
                              AppTextStyle.poppinsRegular14(context: context),
                        )
                      ],
                    )),
                    Icon(Icons.keyboard_arrow_down_sharp)
                  ],
                ),
                SizedBox(
                  height: height / 80,
                ),
                Divider(),
                SizedBox(
                  height: height / 80,
                ),
                Row(
                  children: [
                    Expanded(
                        child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          "Notification Settings",
                          style: AppTextStyle.poppinsRegular16(
                              context: context,
                              fontWeight: FontWeight.w600,
                              color: AppColors.secondaryColor),
                        ),
                        Text(
                          "define what alerts and notification you want to see",
                          style:
                              AppTextStyle.poppinsRegular14(context: context),
                        )
                      ],
                    )),
                    Icon(Icons.keyboard_arrow_down_sharp)
                  ],
                ),
                SizedBox(
                  height: height / 80,
                ),
                Divider(),
                SizedBox(
                  height: height / 80,
                ),
                Row(
                  children: [
                    Expanded(
                        child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          "Account Settings",
                          style: AppTextStyle.poppinsRegular16(
                              context: context,
                              fontWeight: FontWeight.w600,
                              color: AppColors.secondaryColor),
                        ),
                        Text(
                          "Delete your Account",
                          style:
                              AppTextStyle.poppinsRegular14(context: context),
                        )
                      ],
                    )),
                    Icon(Icons.keyboard_arrow_down_sharp)
                  ],
                ),
                SizedBox(
                  height: height / 80,
                ),
                Divider(),
                SizedBox(
                  height: height / 80,
                ),
              ],
            ),
          )
        ],
      ),
    );
  }
}
