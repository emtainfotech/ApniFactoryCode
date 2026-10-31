import 'dart:io';
import 'package:apni_factory/provider/your_profile_screen_provider.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:apni_factory/res/widgets/round_button.dart';
import 'package:apni_factory/utils.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class YourProfileScreen extends StatefulWidget {
  const YourProfileScreen({super.key});

  @override
  State<YourProfileScreen> createState() => _YourProfileScreenState();
}

class _YourProfileScreenState extends State<YourProfileScreen> {
  @override
  void initState() {
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      await Provider.of<YourProfileScreenProvider>(context, listen: false)
          .getProfile(context: context);
    });
    // TODO: implement initState
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(
      body: Consumer<YourProfileScreenProvider>(
        builder: (context, provider, child) {
          return provider.loadingGetProfile
              ? CircularProgressIndicatorWidget()
              : ListView(
                  children: [
                    SizedBox(
                      height: height / 20,
                    ),
                    Align(
                      alignment: Alignment.bottomCenter,
                      child: Container(
                        height: height / 13.2,
                        width: height / 13.2,
                        decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            border: Border.all(
                                color: AppColors.whiteColor, width: 2),
                            color: AppColors.goldenColor),
                        child: Center(
                            child: Text(
                          provider.getProfileModel?.data?.name?[0] ?? "",
                          style: AppTextStyle.poppinsBold24(context: context),
                        )),
                      ),
                    ),
                    SizedBox(
                      height: height / 50,
                    ),
                    Center(
                      child: Text(
                        provider.getProfileModel?.data?.name ?? "",
                        style: AppTextStyle.poppinsMedium20(
                            context: context,
                            color: AppColors.secondaryColor,
                            fontWeight: FontWeight.w600),
                      ),
                    ),
                    SizedBox(
                      height: height / 50,
                    ),
                    Padding(
                      padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                      child: Row(
                        children: [
                          Text(
                            "Edit Profile",
                            style: AppTextStyle.poppinsRegular16(
                                context: context,
                                fontWeight: FontWeight.w500,
                                color: AppColors.primaryColor),
                          ),
                          Spacer(),
                          IconButton(
                              onPressed: () {
                                provider.enableTextField();
                              },
                              icon: Icon(
                                Icons.edit,
                                color: AppColors.primaryColor,
                              ))
                        ],
                      ),
                    ),
                    SizedBox(
                      height: height / 57.1,
                    ),
                    Padding(
                      padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                      child: Utils.commonTextFormField(context,
                          height: height,
                          width: width,
                          labelText: "Email",
                          controller: provider.emailController,
                          enabled: provider.isYourProfileEnable),
                    ),
                    SizedBox(
                      height: height / 57.1,
                    ),
                    Padding(
                      padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                      child: Utils.commonTextFormField(context,
                          height: height,
                          width: width,
                          labelText: "Name",
                          controller: provider.nameController,
                          enabled: provider.isYourProfileEnable),
                    ),
                    SizedBox(
                      height: height / 57.1,
                    ),
                    Padding(
                      padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                      child: Utils.commonTextFormField(context,
                          height: height,
                          width: width,
                          labelText: "Mobile",
                          controller: provider.mobileController,
                          keyboardType: TextInputType.phone,
                          enabled: provider.isYourProfileEnable),
                    ),
                    SizedBox(
                      height: height / 57.1,
                    ),
                    Padding(
                      padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                      child: Utils.commonTextFormField(
                        context,
                        height: height,
                        width: width,
                        labelText: "Whatsapp Number",
                        controller: provider.whatsappnoController,
                        enabled: provider.isYourProfileEnable,
                      ),
                    ),
                    SizedBox(
                      height: height / 57.1,
                    ),
                    Padding(
                      padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                      child: Row(
                        children: [
                          Expanded(
                            child: Utils.commonTextFormField(
                              context,
                              height: height,
                              width: width,
                              labelText: "Gst Or Pan Number",
                              controller: provider.gstorpanController,
                              enabled: provider.isYourProfileEnable,
                            ),
                          ),
                          SizedBox(
                            width: width / 15,
                          ),
                          provider.gstverified
                              ? SizedBox.shrink()
                              : Expanded(
                                  child: provider.loadingGstVerification
                                      ? CircularProgressIndicatorWidget()
                                      : RoundButton(
                                          title: "Verify",
                                          onPressed: () async {
                                            await provider.gstVerification(
                                                height,
                                                context: context,
                                                gstorpan: provider
                                                    .gstorpanController.text
                                                    .trim());
                                          },
                                        ),
                                )
                        ],
                      ),
                    ),
                    SizedBox(
                      height: height / 20,
                    ),
                    Padding(
                      padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                      child: provider.loadingUpdateProfile
                          ? CircularProgressIndicatorWidget()
                          : RoundButton(
                              backgroundColor: provider.isYourProfileEnable
                                  ? AppColors.primaryColor
                                  : AppColors.greyColor,
                              title: "Update",
                              onPressed: () {
                                if (provider.isYourProfileEnable) {
                                  provider.updateProfile(
                                    height,
                                    context: context,
                                    email: provider.emailController.text.trim(),
                                    name: provider.nameController.text.trim(),
                                    mobile:
                                        provider.mobileController.text.trim(),
                                    whatsappNo: provider
                                        .whatsappnoController.text
                                        .trim(),
                                    gstorpan:
                                        provider.gstorpanController.text.trim(),
                                  );
                                }
                              },
                            ),
                    ),
                    SizedBox(
                      height: height / 26,
                    ),
                  ],
                );
        },
      ),
    );
  }
}
