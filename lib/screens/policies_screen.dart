import 'package:apni_factory/provider/homeScreen_provider.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/res/widgets/back_button_widget.dart';
import 'package:apni_factory/screens/pages_screen.dart';
import 'package:flutter/material.dart';

class PolicyScreen extends StatefulWidget {
 final HomeScreenProvider provider;
  const PolicyScreen({super.key, required this.provider});

  @override
  State<PolicyScreen> createState() => _PolicyScreenState();
}

class _PolicyScreenState extends State<PolicyScreen> {
  @override
  Widget build(BuildContext context) {
      double height = MediaQuery.of(context).size.height;
      double width = MediaQuery.of(context).size.width;

    return Scaffold(
      body:   Padding(
        padding: EdgeInsets.symmetric(
                                        horizontal: width / 16.2),
        child: ListView(children: [
          SizedBox(height: height/24,),
          BackButtonWidget(title: "Policy",),
          SizedBox(height: height/50,),
          ListView.builder(
            physics: NeverScrollableScrollPhysics(),
            shrinkWrap: true,
            itemCount:widget. provider.adminAppPagesModel?.data?.length,
            itemBuilder: (context, index) {
              return ListTile(
                onTap: () {
                  Navigator.push(context, MaterialPageRoute(builder: (context) {
                    return PagesScreen(slug:widget. provider.adminAppPagesModel?.data?[index].slug??"");
                  },));
                },
                trailing: Icon(
                  Icons.arrow_forward_ios_rounded,
                  size: height / 60,
                ),
                leading: Icon(
                  Icons.check_circle,
                  size: height / 40,
                  color: AppColors.primaryColor,
                ),
                title: Text(widget. provider.adminAppPagesModel?.data?[index].name ?? "",
                    style: AppTextStyle.poppinsRegular14(
                        context: context,
                        color: AppColors.secondaryColor,
                        fontWeight: FontWeight.w500)),
              );
            },
          ),
            SizedBox(height: height/24,),
        ],),
      )
         

    );
  }
}