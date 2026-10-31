import 'package:apni_factory/res/textstyle.dart';
import 'package:flutter/material.dart';

import '../colors.dart';

class BackButtonWidget extends StatefulWidget {
  final String? title;
  const BackButtonWidget({super.key,  this.title});

  @override
  State<BackButtonWidget> createState() => _BackButtonWidgetState();
}

class _BackButtonWidgetState extends State<BackButtonWidget> {
  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Row(
      children: [
        Align(
          alignment: Alignment.centerLeft,
          child: Container(
            height: height / 23,
            width: height / 23,
            decoration: BoxDecoration(
                boxShadow: [
                  BoxShadow(
                      blurRadius: 32,
                      color: AppColors.blackColor.withOpacity(0.2))
                ],
                color: AppColors.whiteColor,
                borderRadius: BorderRadius.circular(height / 66.6)),
            child: Center(
                child: InkWell(onTap: () {
                  Navigator.pop(context);
                },
                  child: Icon(
                    Icons.arrow_back_ios,
                    color: AppColors.lightText,size: height/50,
                  ),
                )),
          ),
        ),
        SizedBox(width: width/20,),
        Expanded(child: Text(widget.title??"",style: AppTextStyle.poppinsBold17(context: context,color: AppColors.secondaryColor,fontWeight: FontWeight.w500)))
      ],
    );
  }
}
