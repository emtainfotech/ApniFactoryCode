import 'package:apni_factory/provider/sign_up_provider.dart';
import 'package:apni_factory/res/app_text.dart';
import 'package:apni_factory/res/asset_image.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:apni_factory/res/widgets/widget_icon.dart';
import 'package:apni_factory/res/widgets/round_button.dart';
import 'package:apni_factory/screens/sign_in.dart';
import 'package:apni_factory/utils.dart';
import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:provider/provider.dart';

class SignUpScreen extends StatefulWidget {
  const SignUpScreen({super.key});

  @override
  State<SignUpScreen> createState() => _SignUpScreenState();
}

class _SignUpScreenState extends State<SignUpScreen> {
  @override
  void initState() {
    final provider = Provider.of<SignUpProvider>(context, listen: false);

    provider.mobileNumberController.addListener(() {
      provider.onMobileChanged();
    });
    // WidgetsBinding.instance.addPostFrameCallback((timeStamp) {
    //   Provider.of<SignUpProvider>(context, listen: false).authChange();
    // });

    // TODO: implement initState
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(
      body: Container(
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
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  SizedBox(
                    height: height / 8,
                  ),
                  Center(
                      child: Image.asset(
                    AppAssetImage.logo,
                    height: height / 19.5,
                    width: width / 2.26,
                  )),
                  SizedBox(
                    height: height / 30,
                  ),
                  Center(
                    child: Text(
                      AppText.signUp,
                      style: AppTextStyle.poppinsBold19(
                          context: context,
                          fontWeight: FontWeight.w600,
                          color: AppColors.secondaryColor),
                    ),
                  ),
                  SizedBox(
                    height: height / 28.5,
                  ),
                  Text("Please fill your details to create new account",
                      style: AppTextStyle.poppinsBold13(
                          context: context,
                          fontWeight: FontWeight.w400,
                          color: AppColors.secondaryColor)),
                  Expanded(child: Consumer<SignUpProvider>(
                    builder: (context, provider, child) {
                      return ListView(
                        shrinkWrap: true,
                        children: [
                          SizedBox(
                            height: height / 150,
                          ),
                          Utils.commonTextFormField(
                            context,
                            height: height,
                            width: width,
                            labelText: "Name",
                            controller: provider.nameController,
                          ),
                          SizedBox(
                            height: height / 57.1,
                          ),
                          Row(
                            children: [
                              Expanded(
                                child: Utils.commonTextFormField(context,
                                    height: height,
                                    width: width,
                                    labelText: "Email",
                                    controller: provider.emailController),
                              ),
                              SizedBox(
                                width: width / 20,
                              ),
                              provider.loadingGetEmailOtp
                                  ? Expanded(
                                      child: Center(
                                      child: CircularProgressIndicator(),
                                    ))
                                  : provider.isEmailOtpvVerified
                                      ? SizedBox.shrink()
                                      : TextButton(
                                          child: Text(
                                            "Verify",
                                            style:
                                                AppTextStyle.poppinsRegular14(
                                                    context: context,
                                                    fontWeight: FontWeight.w600,
                                                    color:
                                                        AppColors.primaryColor),
                                          ),
                                          onPressed: () {
                                            provider.getEmailOtp(height,
                                                context: context,
                                                email: provider
                                                    .emailController.text
                                                    .toString());
                                          },
                                        ),
                            ],
                          ),
                          SizedBox(
                            height: height / 57.1,
                          ),
                          provider.isEmailOtpReceived
                              ? Row(
                                  children: [
                                    Expanded(
                                      child: Utils.commonTextFormField(context,
                                          height: height,
                                          width: width,
                                          labelText: "OTP",
                                          controller:
                                              provider.emailOtpController,
                                          keyboardType: TextInputType.number),
                                    ),
                                    SizedBox(
                                      width: width / 20,
                                    ),
                                    provider.loadingVerifyEmailOtp
                                        ? Expanded(
                                            child: Center(
                                            child: CircularProgressIndicator(),
                                          ))
                                        : TextButton(
                                            child: Text(
                                              "Verify",
                                              style:
                                                  AppTextStyle.poppinsRegular14(
                                                      context: context,
                                                      fontWeight:
                                                          FontWeight.w600,
                                                      color: AppColors
                                                          .primaryColor),
                                            ),
                                            onPressed: () {
                                              provider.verifyEmailOtp(height,
                                                  context: context,
                                                  email: provider
                                                      .emailController.text
                                                      .trim(),
                                                  otp: provider
                                                      .emailOtpController.text
                                                      .trim());
                                            },
                                          ),
                                  ],
                                )
                              : SizedBox.shrink(),
                          SizedBox(
                            height: height / 57.1,
                          ),
                          Row(
                            children: [
                              Expanded(
                                child: Utils.commonTextFormField(context,
                                    height: height,
                                    width: width,
                                    labelText: "Mobile Number",
                                    controller: provider.mobileNumberController,
                                    keyboardType: TextInputType.number,
                                    maxLength: 10),
                              ),
                              SizedBox(
                                width: width / 20,
                              ),
                              provider.loadingGetMobileOtp
                                  ? Expanded(
                                      child: Center(
                                      child: CircularProgressIndicator(),
                                    ))
                                  : (provider.mobileNumberController.text
                                                  .length ==
                                              10 &&
                                          !provider.isMobileOtpVerified)
                                      ? TextButton(
                                          child: Text(
                                            "Verify",
                                            style:
                                                AppTextStyle.poppinsRegular14(
                                                    context: context,
                                                    fontWeight: FontWeight.w600,
                                                    color:
                                                        AppColors.primaryColor),
                                          ),
                                          onPressed: () {
                                            provider.getMobileOtp(height,
                                                context: context,
                                                mobile: provider
                                                    .mobileNumberController.text
                                                    .toString());
                                          },
                                        )
                                      : SizedBox.shrink(),
                            ],
                          ),
                          SizedBox(
                            height: height / 57.1,
                          ),
                          provider.isMobileOtpReceived
                              ? Row(
                                  children: [
                                    Expanded(
                                      child: Utils.commonTextFormField(context,
                                          height: height,
                                          width: width,
                                          labelText: "OTP",
                                          controller:
                                              provider.mobileOtpController,
                                          keyboardType: TextInputType.number),
                                    ),
                                    SizedBox(
                                      width: width / 20,
                                    ),
                                    provider.loadingVerifyMobileOtp
                                        ? Expanded(
                                            child: Center(
                                            child: CircularProgressIndicator(),
                                          ))
                                        : TextButton(
                                            child: Text(
                                              "Verify",
                                              style:
                                                  AppTextStyle.poppinsRegular14(
                                                      context: context,
                                                      fontWeight:
                                                          FontWeight.w600,
                                                      color: AppColors
                                                          .primaryColor),
                                            ),
                                            onPressed: () {
                                              provider.verifyMobileOtp(height,
                                                  context: context,
                                                  mobile: provider
                                                      .mobileNumberController
                                                      .text
                                                      .trim(),
                                                  otp: provider
                                                      .mobileOtpController.text
                                                      .trim());
                                            },
                                          ),
                                  ],
                                )
                              : SizedBox.shrink(),
                          SizedBox(
                            height: height / 57.1,
                          ),

                          Utils.commonTextFormField(context,
                              height: height,
                              width: width,
                              labelText: "Password",
                              suffixIcon: InkWell(
                                onTap: () {
                                  provider.passwordVisibility();
                                },
                                child: provider.isPasswordVisible
                                    ? Icon(
                                        Icons.visibility,
                                        color: AppColors.neutralGrey,
                                      )
                                    : Icon(
                                        Icons.visibility_off,
                                        color: AppColors.neutralGrey,
                                      ),
                              ),
                              controller: provider.passwordController,
                              obscureText: provider.isPasswordVisible),
                          SizedBox(
                            height: height / 57.1,
                          ),
                          Utils.commonTextFormField(context,
                              height: height,
                              width: width,
                              labelText: "Confirm Password",
                              suffixIcon: InkWell(
                                onTap: () {
                                  provider.confirmPasswordVisibility();
                                },
                                child: provider.isConfirmPasswordVisible
                                    ? Icon(
                                        Icons.visibility,
                                        color: AppColors.neutralGrey,
                                      )
                                    : Icon(
                                        Icons.visibility_off,
                                        color: AppColors.neutralGrey,
                                      ),
                              ),
                              controller: provider.confirmPasswordController,
                              obscureText: provider.isConfirmPasswordVisible),
                          SizedBox(
                            height: height / 40,
                          ),
                          provider.loadingSignUp
                              ? CircularProgressIndicatorWidget()
                              : RoundButton(
                                  title: AppText.signUp,
                                  onPressed: () {
                                    provider.signUp(
                                      height,
                                      context: context,
                                      name: provider.nameController.text.trim(),
                                      email:
                                          provider.emailController.text.trim(),
                                      mobileNumber: provider
                                          .mobileNumberController.text
                                          .trim(),
                                      password: provider.passwordController.text
                                          .trim(),
                                      confirmPassword: provider
                                          .confirmPasswordController.text
                                          .trim(),
                                      regby: "app",
                                      // gstPan: provider.gstPanController.text
                                      //     .trim(),
                                    );
                                  },
                                ),
                          SizedBox(
                            height: height / 26.6,
                          ),
                          Divider(height: 1, color: AppColors.stroke),
                          // SizedBox(
                          //   height: height / 26.6,
                          // ),

                          // Row(
                          //   mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                          //   children: [
                          //     InkWell(
                          //       onTap: () async {
                          //         // await provider.handleGoogleSignUp(
                          //         //     height: height, context: context);
                          //       },
                          //       child: IconWidget(
                          //         icon: Image.asset(
                          //           AppAssetImage.googleIcon,
                          //           height: height / 30,
                          //         ),
                          //       ),
                          //     ),
                          //     IconWidget(
                          //         icon: Icon(
                          //       FontAwesomeIcons.facebookF,
                          //       color: Color(0xff1877F2),
                          //     )),
                          //   ],
                          // ),
                          // SizedBox(
                          //   height: height / 25,
                          // ),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Text(
                                "Already have an account?",
                                style: AppTextStyle.poppinsBold13(
                                    context: context,
                                    fontWeight: FontWeight.w400,
                                    color: AppColors.secondaryColor),
                              ),
                              TextButton(
                                  onPressed: () {
                                    Navigator.push(context, MaterialPageRoute(
                                      builder: (context) {
                                        return SignInScreen();
                                      },
                                    ));
                                  },
                                  child: Text(
                                    AppText.signIn,
                                    style: AppTextStyle.poppinsBold12(
                                        context: context,
                                        fontWeight: FontWeight.w600,
                                        color: AppColors.primaryColor),
                                  )),
                              SizedBox(
                                height: height / 16,
                              ),
                            ],
                          ),
                          SizedBox(
                            height: height / 5,
                          )
                        ],
                      );
                    },
                  )),
                ],
              ),
            )
          ],
        ),
      ),
    );
  }
}
