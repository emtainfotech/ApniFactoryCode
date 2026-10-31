import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/screens/all_screen.dart';
import 'package:apni_factory/screens/sign_in.dart';
import 'package:apni_factory/shared_pref.dart';
import 'package:flutter/material.dart';

class NetworkErrorScreen extends StatefulWidget {
  const NetworkErrorScreen({super.key});

  @override
  State<NetworkErrorScreen> createState() => _NetworkErrorScreenState();
}

class _NetworkErrorScreenState extends State<NetworkErrorScreen> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
        body: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
      const Icon(
        Icons.cloud_off_outlined,
        color: AppColors.primaryColor,
        size: 70,
      ),
      const SizedBox(
        height: 20,
      ),
      Center(
        child: Text("Something Went Wrong",
            style: AppTextStyle.poppinsRegular14(
                context: context, color: AppColors.primaryColor)),
      ),
      const SizedBox(
        height: 40,
      ),
      InkWell(
          onTap: () {
            Navigator.pushAndRemoveUntil(context, MaterialPageRoute(
              builder: (context) {
                return SignInScreen();
              },
            ), (route) => false);
            SharedPref.getUserLoginId().then((value) {
              if (value.isNotEmpty) {
                Navigator.pushAndRemoveUntil(context, MaterialPageRoute(
                  builder: (context) {
                    return AllScreen();
                  },
                ), (route) => false);
              } else {
                Navigator.pushAndRemoveUntil(context, MaterialPageRoute(
                  builder: (context) {
                    return SignInScreen();
                  },
                ), (route) => false);
              }
            });
            // SharedPref.getToken().then((value) {
            //   if (value.isNotEmpty) {
            //     Navigator.pushNamedAndRemoveUntil(
            //         context, RouteName.allScreen, (route) => false);
            //   } else {
            //     Navigator.pushNamedAndRemoveUntil(context,
            //         RouteName.createAccountScreen, (route) => false);
            //   }
            // });
          },
          child: Container(
              height: 50,
              width: 50,
              decoration: const BoxDecoration(
                shape: BoxShape.circle,
                color: AppColors.primaryColor,
              ),
              child: const Icon(
                Icons.refresh_outlined,
                color: AppColors.whiteColor,
                size: 30,
              )))
    ]));
  }
}
