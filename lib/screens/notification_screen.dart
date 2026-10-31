import 'package:apni_factory/provider/homeScreen_provider.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class NotificationScreen extends StatefulWidget {
  //  final String orderId;
  const NotificationScreen({
    super.key,
  });

  @override
  State<NotificationScreen> createState() => _NotificationScreenState();
}

class _NotificationScreenState extends State<NotificationScreen> {
  @override
  void initState() {
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      await Provider.of<HomeScreenProvider>(context, listen: false)
          .notification(context: context);
    });
    // TODO: implement initState
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(appBar: AppBar(title: Text("Notifications",style: AppTextStyle.poppinsRegular16(context: context,color: AppColors.secondaryColor,fontWeight: FontWeight.w500)),),
      body: Consumer<HomeScreenProvider>(
        builder: (context, provider, child) {
          return 
          provider.loadingNotification
              ? CircularProgressIndicatorWidget()
              : 
              ListView.builder(
                shrinkWrap: true,
                itemCount: provider.notificationModel?.data?.length,
                itemBuilder: (context, index) {
                  return ListTile(
                    leading: Image.asset(
                      "assets/images/notification.png",
                      height: height / 30,
                    ),
                    title: Text(
                      provider.notificationModel?.data?[index].title ?? "",
                      style: AppTextStyle.poppinsBold13(
                          context: context,
                          color: AppColors.secondaryColor,
                          fontWeight: FontWeight.w600),
                    ),
                    subtitle: Text(
                      provider.notificationModel?.data?[index].body ?? "",
                      style: AppTextStyle.poppinsBold12(
                          context: context,
                          color: AppColors.secondaryColor,
                          fontWeight: FontWeight.w400),
                    ),
              
                  );
                 
                },
              );
        },
      ),
    );
  }
}
