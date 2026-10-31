import 'package:apni_factory/provider/ticket_provider.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:apni_factory/screens/pages_screen.dart';

import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../res/colors.dart';
import '../res/textstyle.dart';
import '../res/widgets/back_button_widget.dart';

class TicketScreen extends StatefulWidget {
  const TicketScreen({super.key});

  @override
  State<TicketScreen> createState() => _TicketScreenState();
}

class _TicketScreenState extends State<TicketScreen> {
  @override
  void initState() {
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      await Provider.of<TicketProvider>(context, listen: false)
          .helpNoForAppDetails(context: context);
    });
    // TODO: implement initState
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(
      body: Consumer<TicketProvider>(
        builder: (context, provider, child) {
          return provider.loadingHelpNoForApp
              ? CircularProgressIndicatorWidget()
              : ListView(
                  children: [
                    SizedBox(
                      height: height / 26.6,
                    ),
                    Padding(
                      padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                      child: BackButtonWidget(
                        title: "Yours tickets",
                      ),
                    ),
                    SizedBox(
                      height: height / 23.5,
                    ),
                    Image.asset("assets/images/image 75.png"),
                    SizedBox(
                      height: height / 23.5,
                    ),
                    Center(
                      child: Text(
                        "You haven’t bought any tickets yet",
                        style: AppTextStyle.poppinsRegular16(
                            context: context,
                            color: AppColors.secondaryColor,
                            fontWeight: FontWeight.w600),
                      ),
                    ),
                    SizedBox(
                      height: height / 23.5,
                    ),
                    Padding(
                      padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                      child: InkWell(
                        onTap: () {
                          openWhatsApp(
                            provider.helpNoForAppModel?.data?.value ?? "",
                          );
                        },
                        child: Container(
                          height: height / 17.7,
                          width: width,
                          decoration: BoxDecoration(
                              boxShadow: [
                                BoxShadow(
                                    blurRadius: 32,
                                    color:
                                        AppColors.blackColor.withOpacity(0.1))
                              ], // border: Border.all(color: AppColors.primaryColor),
                              borderRadius:
                                  BorderRadius.circular(height / 66.6),
                              color: AppColors.whiteColor),
                          child: Padding(
                            padding:
                                EdgeInsets.symmetric(horizontal: width / 16.2),
                            child: Row(
                              children: [
                                Icon(Icons.help_center),
                                SizedBox(
                                  width: width / 30,
                                ),
                                Row(
                                  children: [
                                    Text("Need help  ",
                                        style: AppTextStyle.poppinsMedium15(
                                            context: context,
                                            color: AppColors.secondaryColor)),
                                    SizedBox(
                                      height: height / 28,
                                      width: height / 28,
                                      child: FloatingActionButton(
                                        mini: true,
                                        backgroundColor: Colors.green,
                                        onPressed: () {},
                                        child: Icon(
                                          FontAwesomeIcons.whatsapp,
                                          color: Colors.white,
                                          size: height / 50,
                                        ),
                                      ),
                                    ),
                                    Text("  Chat with us",
                                        style: AppTextStyle.poppinsMedium15(
                                            context: context,
                                            color: AppColors.secondaryColor)),
                                  ],
                                ),
                                Spacer(),
                                Text(">",
                                    style: AppTextStyle.poppinsMedium15(
                                        context: context,
                                        color: AppColors.secondaryColor)),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),
                    SizedBox(
                      height: height / 42,
                    ),
                    InkWell(
                      onTap: () {
                        Navigator.push(context, MaterialPageRoute(
                          builder: (context) {
                            return PagesScreen(slug: "TermsCondition");
                          },
                        ));
                      },
                      child: Padding(
                        padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                        child: Container(
                          height: height / 17.7,
                          width: width,
                          decoration: BoxDecoration(
                              boxShadow: [
                                BoxShadow(
                                    blurRadius: 32,
                                    color:
                                        AppColors.blackColor.withOpacity(0.1))
                              ], // border: Border.all(color: AppColors.primaryColor),
                              borderRadius:
                                  BorderRadius.circular(height / 66.6),
                              color: AppColors.whiteColor),
                          child: Padding(
                            padding:
                                EdgeInsets.symmetric(horizontal: width / 16.2),
                            child: Row(
                              children: [
                                Icon(Icons.info_outlined),
                                SizedBox(
                                  width: width / 30,
                                ),
                                Text("Terms and conditions",
                                    style: AppTextStyle.poppinsMedium15(
                                        context: context,
                                        color: AppColors.secondaryColor)),
                                Spacer(),
                                Text(">",
                                    style: AppTextStyle.poppinsMedium15(
                                        context: context,
                                        color: AppColors.secondaryColor)),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                );
        },
      ),
    );
  }
}

Future<void> openWhatsApp(
  String phone,
) async {
  final Uri url = Uri.parse(
    "https://wa.me/$phone",
  );

  if (!await launchUrl(url, mode: LaunchMode.externalApplication)) {
    throw 'Could not launch WhatsApp';
  }
}
