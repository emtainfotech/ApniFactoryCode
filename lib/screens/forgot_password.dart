import 'package:apni_factory/provider/forgot_password_provider.dart';
import 'package:apni_factory/res/widgets/back_button_widget.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:apni_factory/res/widgets/round_button.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../res/app_text.dart';
import '../res/asset_image.dart';
import '../res/colors.dart';
import '../res/textstyle.dart';
import '../utils.dart';

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {

   late ForgotPasswordProvider _provider;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    _provider = Provider.of<ForgotPasswordProvider>(context, listen: false);
  }

 @override
  void dispose() {
    _provider.disposeTimer();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(
      body: Consumer<ForgotPasswordProvider>(
        builder: (context, provider, child) {
          return provider.loadingGetOtp
              ? CircularProgressIndicatorWidget()
              : Container(
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
                              height: height / 26.6,
                            ),
                            InkWell(
                                onTap: () {
                                  Navigator.pop(context);
                                },
                                child: BackButtonWidget()),
                            SizedBox(
                              height: height / 13.1,
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
                                    text: AppText.forgotPassword,
                                      style: AppTextStyle.poppinsBold19(
                          context: context,
                          fontWeight: FontWeight.w600,
                          color: AppColors.secondaryColor),),
                              ])),
                            ),
                            SizedBox(
                              height: height / 28.5,
                            ),
                            Text(
                              "Enter register email address or mobile number",
                              textAlign: TextAlign.left,
                              style: AppTextStyle.poppinsBold13(
                                                      context: context,
                                                      fontWeight: FontWeight.w400,
                                                      color: AppColors.secondaryColor)
                            ),
                            SizedBox(
                              height: height / 36.3,
                            ),
                            Utils.commonTextFormField(context,
                                height: height,
                                width: width,
                                labelText: "Email / Mobile Number",
                                controller: provider.emailController,
                               ),
                            SizedBox(
                              height: height / 57.1,
                            ),
                            provider.isOtpReceived
                                ? Utils.commonTextFormField(context,
                                    height: height,
                                    width: width,
                                    labelText: "OTP",
                                    controller: provider.otpController)
                                : SizedBox.shrink(),
                            SizedBox(
                              height: height / 40,
                            ),
                            !provider.isOtpReceived
                                ? RoundButton(
                                    title: AppText.getAnOTP,
                                    onPressed: () {
                                      provider.getOtp(height,
                                          context: context,
                                          email: provider.emailController.text
                                              .trim());
                                    },
                                  )
                                : provider.loadingVerifyOtp
                                    ? CircularProgressIndicatorWidget()
                                    : RoundButton(
                                        title: AppText.submit,
                                        onPressed: () {
                                          provider.verifyOtp(height,
                                              context: context,
                                              email: provider
                                                  .emailController.text
                                                  .trim(),
                                              otp: provider.otpController.text
                                                  .trim());
                                        },
                                      ),
                            SizedBox(
                              height: height / 40,
                            ),
                            provider.isOtpReceived
                                ? Row(
                                    mainAxisAlignment:
                                        MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        "Didn’t receive an OTP?",
                                        style: AppTextStyle.poppinsBold13(
                                            context: context,
                                            color: AppColors.lightText,
                                            fontWeight: FontWeight.w500),
                                      ),
                                      InkWell(
                                        onTap: () {
                                          if (provider.showResend) {
                                            provider.getOtp(
                                              height,
                                              context: context,
                                              email: provider
                                                  .emailController.text
                                                  .trim(),
                                            );
                                          }
                                        },
                                        child: Text(
                                          provider.showResend
                                              ? "Resend Otp"
                                              : "Resend in ${provider.otpTimer.toString()} sec",
                                          style: AppTextStyle.poppinsRegular14(
                                              context: context,
                                              color: provider.showResend
                                                  ? AppColors.primaryColor
                                                  : AppColors.greyColor,
                                              fontWeight: FontWeight.w500),
                                        ),
                                      )
                                    ],
                                  )
                                : SizedBox.shrink()
                          ],
                        ),
                      )
                    ],
                  ),
                );
        },
      ),
    );
  }
}
