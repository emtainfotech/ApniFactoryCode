import 'package:apni_factory/provider/brand_notification_screen_provider.dart';
import 'package:apni_factory/res/asset_image.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class BrandNotificationScreen extends StatefulWidget {
  final String brandid;
  const BrandNotificationScreen({super.key, required this.brandid});

  @override
  State<BrandNotificationScreen> createState() =>
      _BrandNotificationScreenState();
}

class _BrandNotificationScreenState extends State<BrandNotificationScreen> {
  @override
  void initState() {
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      await Provider.of<BrandNotificationScreenProvider>(context, listen: false)
          .brandDetail(context: context, brandid: widget.brandid);
    });
    // TODO: implement initState
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(
      appBar: AppBar(),
      body: Consumer<BrandNotificationScreenProvider>(
        builder: (context, provider, child) {
          return provider.loadingBrandDetail
              ? CircularProgressIndicatorWidget()
              : Padding(
                  padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                  child: ListView(children: [
                    Text(
                      "A new brand registered",
                      style: AppTextStyle.poppinsMedium20(
                          context: context,
                          fontWeight: FontWeight.w600,
                          color: AppColors.primaryColor),
                    ),
                    SizedBox(
                      height: height / 24,
                    ),
                    Container(
                      height: height / 5,
                      width: height / 5,
                      decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(height / 60),
                          image: DecorationImage(
                              image: NetworkImage(
                                  "${AppAssetImage.imageBaseUrl}storage/app/public/${(provider.brandDetailModel?.data?.image ?? "")}"),
                              fit: BoxFit.cover)),
                    ),
                    SizedBox(
                      height: height / 40,
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          "Name : ${provider.brandDetailModel?.data?.name ?? ""}",
                          style: AppTextStyle.poppinsMedium20(
                              context: context,
                              color: AppColors.secondaryColor,
                              fontWeight: FontWeight.w600),
                          overflow: TextOverflow.ellipsis,
                        ),
                        SizedBox(
                          height: height / 120,
                        ),
                        Text(
                          "Trademark: ${provider.brandDetailModel?.data?.trademarkno ?? ""}",
                          style: AppTextStyle.poppinsMedium15(
                            context: context,
                            color: AppColors.secondaryColor,
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                        SizedBox(
                          height: height / 40,
                        ),
                        Text(
                          "Company Details:",
                          style: AppTextStyle.poppinsRegular16(
                              context: context,
                              color: AppColors.secondaryColor,
                              fontWeight: FontWeight.w500),
                          overflow: TextOverflow.ellipsis,
                        ),
                        SizedBox(
                          height: height / 120,
                        ),
                        Text(
                          "        Name : ${provider.brandDetailModel?.data?.company?.name ?? ""}",
                          style: AppTextStyle.poppinsMedium15(
                            context: context,
                            color: AppColors.secondaryColor,
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                        SizedBox(
                          height: height / 120,
                        ),
                        Text(
                          "        City : ${provider.brandDetailModel?.data?.company?.city ?? ""}",
                          style: AppTextStyle.poppinsMedium15(
                            context: context,
                            color: AppColors.secondaryColor,
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                        SizedBox(
                          height: height / 120,
                        ),
                        Text(
                          "        State : ${provider.brandDetailModel?.data?.company?.state ?? ""}",
                          style: AppTextStyle.poppinsMedium15(
                            context: context,
                            color: AppColors.secondaryColor,
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                        SizedBox(
                          height: height / 120,
                        ),
                        Text(
                          "        Pincode : ${provider.brandDetailModel?.data?.company?.pincode ?? ""}",
                          style: AppTextStyle.poppinsMedium15(
                            context: context,
                            color: AppColors.secondaryColor,
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                    SizedBox(
                      width: width / 40,
                    ),
                  ]),
                );
        },
      ),
    );
  }
}
