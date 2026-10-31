import 'package:apni_factory/provider/forgot_password_provider.dart';
import 'package:apni_factory/res/app_text.dart';
import 'package:apni_factory/res/asset_image.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/res/widgets/back_button_widget.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:apni_factory/res/widgets/round_button.dart';
import 'package:apni_factory/utils.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class ResetPasswordScreen extends StatefulWidget {
  const ResetPasswordScreen({super.key});

  @override
  State<ResetPasswordScreen> createState() => _ResetPasswordScreenState();
}

class _ResetPasswordScreenState extends State<ResetPasswordScreen> {
  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(
      body: Consumer<ForgotPasswordProvider>(
        builder: (context, provider, child) {
          return provider.loadingResetPassword
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
                                    text: AppText.resetPassword,
                                    style: AppTextStyle.poppinsBold24(
                                        context: context)),
                              ])),
                            ),
                            SizedBox(
                              height: height / 28.5,
                            ),
                            Center(
                              child: Text(
                                "Enter reset password",
                                textAlign: TextAlign.center,
                                style: AppTextStyle.poppinsRegular14(
                                    context: context),
                              ),
                            ),
                            SizedBox(
                              height: height / 36.3,
                            ),
                            Utils.commonTextFormField(context,
                                height: height,
                                width: width,
                                labelText: "Password",
                                controller: provider.passwordController),
                            SizedBox(
                              height: height / 40,
                            ),
                            RoundButton(
                              title: AppText.submit,
                              onPressed: () {
                                provider.resetPassword(height,
                                    context: context,
                                    id: provider.verifyOtpModel?.data?.id
                                            .toString() ??
                                        "",
                                    password:
                                        provider.passwordController.text.trim(),
                                    emailmobile:
                                        provider.emailController.text.trim());
                              },
                            ),
                            SizedBox(
                              height: height / 40,
                            ),
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
