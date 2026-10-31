import 'package:apni_factory/res/colors.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class AppTextStyle {
static const double designHeight = 812; // iPhone X / Figma height

  static double scale(BuildContext context, double fontSize) {
    final screenHeight = MediaQuery.of(context).size.height;
    return fontSize * (screenHeight / designHeight);
  }

  static poppinsSemibold10(
      {required BuildContext context,
      Color color = AppColors.whiteColor,
      FontWeight fontWeight = FontWeight.w600}) {

    return GoogleFonts.poppins(
        fontSize: scale(context, 10), color: color, fontWeight: fontWeight);
  }

  static poppinsBold11(
      {required BuildContext context,
      Color color = AppColors.whiteColor,
      FontWeight fontWeight = FontWeight.bold}) {
    double height = MediaQuery.of(context).size.height;
    return GoogleFonts.poppins(
        fontSize: scale(context, 11), color: color, fontWeight: fontWeight);
  }

    static poppinsBold12(
      {required BuildContext context,
      Color color = AppColors.whiteColor,
      FontWeight fontWeight = FontWeight.bold}) {
    double height = MediaQuery.of(context).size.height;
    return GoogleFonts.poppins(
        fontSize: scale(context, 12), color: color, fontWeight: fontWeight);
  }

  static poppinsBold13(
      {required BuildContext context,
      Color color = AppColors.whiteColor,
      FontWeight fontWeight = FontWeight.bold}) {
    double height = MediaQuery.of(context).size.height;
    return GoogleFonts.poppins(
        fontSize: scale(context, 13), color: color, fontWeight: fontWeight);
  }

  static poppinsRegular14(
      {required BuildContext context,
      Color color = AppColors.secondaryColor,
      FontWeight fontWeight = FontWeight.w400}) {
    double height = MediaQuery.of(context).size.height;
    return GoogleFonts.poppins(
        fontSize: scale(context, 14), color: color, fontWeight: fontWeight);
  }

  static poppinsMedium15(
      {required BuildContext context, Color color = AppColors.whiteColor}) {
    double height = MediaQuery.of(context).size.height;
    return GoogleFonts.poppins(
        fontSize: scale(context, 15), color: color, fontWeight: FontWeight.w500);
  }

  static poppinsRegular16(
      {required BuildContext context,
      Color color = AppColors.whiteColor,
      FontWeight fontWeight = FontWeight.w400}) {
    double height = MediaQuery.of(context).size.height;
    return GoogleFonts.poppins(fontSize: 
     scale(context, 16),
      color: color,
      fontWeight: fontWeight,
    );
  }

  static poppinsBold17(
      {required BuildContext context,
      Color color = AppColors.whiteColor,
      FontWeight fontWeight = FontWeight.bold}) {
    double height = MediaQuery.of(context).size.height;
    return GoogleFonts.poppins(
        fontSize: scale(context, 17), color: color, fontWeight: fontWeight);
  }

  static poppinsBold18(
      {required BuildContext context,
      Color color = AppColors.whiteColor,
      FontWeight fontWeight = FontWeight.bold}) {
    double height = MediaQuery.of(context).size.height;
    return GoogleFonts.poppins(
        fontSize: scale(context, 18), color: color, fontWeight: fontWeight);
  }

  static poppinsBold19(
      {required BuildContext context,
      Color color = AppColors.whiteColor,
      FontWeight fontWeight = FontWeight.bold}) {
    double height = MediaQuery.of(context).size.height;
    return GoogleFonts.poppins(
        fontSize: scale(context, 19), color: color, fontWeight: fontWeight);
  }

  static poppinsMedium20(
      {required BuildContext context,
      Color color = AppColors.secondaryColor,
      FontWeight fontWeight = FontWeight.w500}) {
    double height = MediaQuery.of(context).size.height;
    return GoogleFonts.poppins(
        fontSize: scale(context, 20), color: color, fontWeight: fontWeight);
  }

    static poppinsMedium21(
      {required BuildContext context,
      Color color = AppColors.secondaryColor,
      FontWeight fontWeight = FontWeight.w500}) {
    double height = MediaQuery.of(context).size.height;
    return GoogleFonts.poppins(
        fontSize: scale(context, 21), color: color, fontWeight: fontWeight);
  }

  static poppinsMedium22(
      {required BuildContext context,
      Color color = AppColors.secondaryColor,
      FontWeight fontWeight = FontWeight.w500}) {
    double height = MediaQuery.of(context).size.height;
    return GoogleFonts.poppins(
        fontSize: scale(context, 22), color: color, fontWeight: fontWeight);
  }


  static poppinsMedium23(
      {required BuildContext context,
      Color color = AppColors.secondaryColor,
      FontWeight fontWeight = FontWeight.w500}) {
    double height = MediaQuery.of(context).size.height;
    return GoogleFonts.poppins(
        fontSize: scale(context, 23), color: color, fontWeight: fontWeight);
  }
  static poppinsBold24(
      {required BuildContext context,
      Color color = AppColors.secondaryColor,
      FontWeight fontWeight = FontWeight.w700}) {
    double height = MediaQuery.of(context).size.height;
    return GoogleFonts.poppins(
        fontSize: scale(context, 24), color: color, fontWeight: fontWeight);
  }
}
