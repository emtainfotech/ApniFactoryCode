import 'package:apni_factory/provider/login_provider.dart';
import 'package:apni_factory/res/asset_image.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:apni_factory/res/widgets/round_button.dart';
import 'package:apni_factory/res/widgets/widget_icon.dart';
import 'package:apni_factory/screens/forgot_password.dart';
import 'package:apni_factory/screens/sign_up.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:provider/provider.dart';
import '../res/app_text.dart';
import '../res/colors.dart';
import '../res/textstyle.dart';
import '../utils.dart';

class SignInScreen extends StatefulWidget {
  const SignInScreen({super.key});

  @override
  State<SignInScreen> createState() => _SignInScreenState();
}

class _SignInScreenState extends State<SignInScreen> {
  @override
  void initState() {
    // WidgetsBinding.instance.addPostFrameCallback((timeStamp) {
    //   Provider.of<LoginProvider>(context, listen: false).authChange();
    // });

    // TODO: implement initState
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(
      body: Consumer<LoginProvider>(
        builder: (context, provider, child) {
          return Container(
            height: height,
            width: width,
            child: Stack(
              children: [
                Align(
                    alignment: Alignment.topRight,
                    child: Image.asset(
                      AppAssetImage.signUpCurve,
                      color: AppColors.primaryColor,
                    )),
                Padding(
                  padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                  child: ListView(
                    children: [
                      SizedBox(
                        height: height / 8,
                      ),
                      Image.asset(
                        AppAssetImage.logo,
                        height: height / 19.5,
                        width: width / 2.26,
                      ),
                      SizedBox(
                        height: height / 18.6,
                      ),
                      Center(
                        child: RichText(
                            text: TextSpan(children: [
                          TextSpan(
                              text: "Welcome to ",
                              style: AppTextStyle.poppinsBold19(
                                  context: context,
                                  fontWeight: FontWeight.w600,
                                  color: AppColors.secondaryColor)),
                          TextSpan(
                              text: AppText.apniFactory,
                              style: AppTextStyle.poppinsBold19(
                                  context: context,
                                  color: AppColors.primaryColor,
                                  fontWeight: FontWeight.w600))
                        ])),
                      ),
                      SizedBox(
                        height: height / 28.5,
                      ),
                      Text(
                        "Please fill your details to continue",
                        style: TextStyle(
                          fontSize: AppTextStyle.scale(context, 13),
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      SizedBox(
                        height: height / 40,
                      ),
                      Utils.commonTextFormField(
                        context,
                        height: height,
                        width: width,
                        labelText: "Email",
                        controller: provider.emailController,
                      ),
                      SizedBox(
                        height: height / 57.1,
                      ),
                      Utils.commonTextFormField(context,
                          height: height,
                          width: width,
                          labelText: "Password",
                          controller: provider.passwordController,
                          obscureText: provider.isPasswordVisible,
                          suffixIcon: InkWell(
                            onTap: () {
                              provider.passwordVisibility();
                            },
                            child: Icon(
                              provider.isPasswordVisible
                                  ? Icons.visibility
                                  : Icons.visibility_off,
                              color: AppColors.neutralGrey,
                            ),
                          )),
                      SizedBox(
                        height: height / 57.1,
                      ),
                      Align(
                        alignment: Alignment.centerRight,
                        child: TextButton(
                            onPressed: () {
                              Navigator.push(context, MaterialPageRoute(
                                builder: (context) {
                                  return ForgotPasswordScreen();
                                },
                              ));
                            },
                            child: Text(
                              AppText.forgotPassword,
                              style: AppTextStyle.poppinsBold13(
                                  context: context,
                                  color: AppColors.primaryColor,
                                  fontWeight: FontWeight.w500),
                            )),
                      ),
                      SizedBox(
                        height: height / 40,
                      ),
                      provider.loadingLogin
                          ? CircularProgressIndicatorWidget()
                          : RoundButton(
                              title: AppText.signIn,
                              onPressed: () async {
                                await provider.login(
                                  height,
                                  context: context,
                                  email: provider.emailController.text.trim(),
                                  password:
                                      provider.passwordController.text.trim(),
                                );
                              },
                            ),
                      SizedBox(
                        height: height / 26.6,
                      ),
                      Divider(height: 1, color: AppColors.stroke),
                      // Row(
                      //   mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      //   children: [
                      //     Expanded(
                      //       child:
                      //           Divider(height: 1, color: AppColors.stroke),
                      //     ),
                      //     SizedBox(
                      //       width: width / 24,
                      //     ),
                      //     Text("or"),
                      //     SizedBox(
                      //       width: width / 24,
                      //     ),
                      //     Expanded(
                      //         child: Divider(
                      //             height: 1, color: AppColors.stroke))
                      //   ],
                      // ),
                      // SizedBox(
                      //   height: height / 26.6,
                      // ),
                      // Row(
                      //   mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                      //   children: [
                      //     InkWell(
                      //       onTap: () async {
                      //         await provider.handleGoogleSignIn(
                      //             height: height, context: context);
                      //       },
                      //       child: IconWidget(
                      //           icon: Image.asset(
                      //         AppAssetImage.googleIcon,
                      //         height: height / 30,
                      //       )),
                      //     ),
                      //     IconWidget(
                      //       icon: Icon(
                      //         FontAwesomeIcons.facebookF,
                      //         color: Color(0xff1877F2),
                      //       ),
                      //     ),
                      //   ],
                      // ),
                      // SizedBox(
                      //   height: height / 26.6,
                      // ),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            "Don’t have an account?",
                            style: AppTextStyle.poppinsBold13(
                                context: context,
                                fontWeight: FontWeight.w400,
                                color: AppColors.secondaryColor),
                          ),
                          TextButton(
                              onPressed: () {
                                Navigator.push(context, MaterialPageRoute(
                                  builder: (context) {
                                    return SignUpScreen();
                                  },
                                ));
                              },
                              child: Text(
                                AppText.signUp,
                                style: AppTextStyle.poppinsBold12(
                                    context: context,
                                    fontWeight: FontWeight.w600,
                                    color: AppColors.primaryColor),
                              ))
                        ],
                      ),
                      SizedBox(
                        height: height / 5,
                      ),
                    ],
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}
