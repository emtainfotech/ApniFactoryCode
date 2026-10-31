import 'package:apni_factory/provider/change_password_provider.dart';
import 'package:apni_factory/res/asset_image.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/res/widgets/back_button_widget.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:apni_factory/res/widgets/round_button.dart';
import 'package:apni_factory/utils.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class ChangePasswordScreen extends StatefulWidget {
  const ChangePasswordScreen({super.key});

  @override
  State<ChangePasswordScreen> createState() => _ChangePasswordScreenState();
}

class _ChangePasswordScreenState extends State<ChangePasswordScreen> {
  TextEditingController controller = TextEditingController();
  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(body: Consumer<ChangePasswordProvider>(
      builder: (context, provider, child) {
        return provider.loadingChangePassword
            ? CircularProgressIndicatorWidget()
            : Padding(
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
                            text: "Change Password",
                            style:
                                AppTextStyle.poppinsBold24(context: context)),
                      ])),
                    ),
                    SizedBox(
                      height: height / 28.5,
                    ),
                    Utils.commonTextFormField(context,
                        height: height,
                        width: width,
                        labelText: "Current Password",
                        controller: provider.currentPasswordController),
                    SizedBox(
                      height: height / 36.3,
                    ),
                    Utils.commonTextFormField(context,
                        height: height,
                        width: width,
                        labelText: "New Password",
                        controller: provider.newPasswordController),
                    SizedBox(
                      height: height / 18.6,
                    ),
                    RoundButton(
                      title: "Submit",
                      onPressed: () {
                        provider.changePassword(height,
                            context: context,
                            currentPassword:
                                provider.currentPasswordController.text.trim(),
                            newPassword:
                                provider.newPasswordController.text.trim());
                      },
                    ),
                  ],
                ),
              );
      },
    ));
  }
}
