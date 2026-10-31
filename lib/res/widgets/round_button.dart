import 'package:flutter/material.dart';

import '../colors.dart';
import '../textstyle.dart';

class RoundButton extends StatefulWidget {
  final String title;
  final void Function()? onPressed;
  final Color backgroundColor ;
  const RoundButton({super.key,  required this.title, this.onPressed, this.backgroundColor =AppColors.primaryColor});

  @override
  State<RoundButton> createState() => _RoundButtonState();
}

class _RoundButtonState extends State<RoundButton> {
  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return ConstrainedBox(
      constraints: BoxConstraints.tightFor(
        height: height / 20,
        width: width,
      ),
      child: ElevatedButton(
          style: ElevatedButton.styleFrom(
              shape: RoundedRectangleBorder(
                  borderRadius:
                  BorderRadius.circular(height / 66.6)),
              backgroundColor: widget.backgroundColor),
          onPressed: widget.onPressed,
          child: Text(
            widget.title,textAlign: TextAlign.center,
            style:
            AppTextStyle.poppinsBold13(context: context,color: AppColors.whiteColor,fontWeight: FontWeight.w500),
          )),
    );
  }
}
