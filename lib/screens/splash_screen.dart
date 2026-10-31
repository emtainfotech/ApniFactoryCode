import 'dart:developer';

import 'package:apni_factory/api_services/notification_services.dart';
import 'package:apni_factory/res/asset_image.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/screens/all_screen.dart';
import 'package:apni_factory/screens/sign_in.dart';
import 'package:apni_factory/shared_pref.dart';
import 'package:flutter/material.dart';

class SpalshScreen extends StatefulWidget {
  const SpalshScreen({super.key});

  @override
  State<SpalshScreen> createState() => _SpalshScreenState();
}

class _SpalshScreenState extends State<SpalshScreen> {
  @override
  void initState() {
    _initApp();
    
    // TODO: implement initState
    super.initState();
  }


   Future<void> _initApp() async {
    // 🔔 Init notification (safe)
    NotificationServices().init(context);

    // ✅ ONLY source of truth
    bool isLoggedIn = await SharedPref.getIsLoggedIn();

    log("Is Logged In => $isLoggedIn");

    await Future.delayed(const Duration(seconds: 3));

    if (!mounted) return;

    if (isLoggedIn) {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => AllScreen()),
      );
    } else {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => SignInScreen()),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
      double height = MediaQuery.of(context).size.height;
   
    return Scaffold(
      backgroundColor: AppColors.whiteColor,
      body: Center(
        child: Image.asset(AppAssetImage.logo,height: height/15,),
      ),
    );
  }
}
