import 'package:flutter/material.dart';
import '../colors.dart';

class IconWidget extends StatefulWidget {
  final Widget icon;
  const IconWidget({super.key, required this.icon});

  @override
  State<IconWidget> createState() => _IconWidgetState();
}

class _IconWidgetState extends State<IconWidget> {
  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
   
    return Container(
      height: height / 16.6,
      width: height / 16.6,
      decoration: BoxDecoration(
          color: AppColors.whiteColor,
          boxShadow: [
            BoxShadow(
                blurRadius: 32,
                color: AppColors.blackColor.withOpacity(0.2))
          ],
          borderRadius: BorderRadius.circular(height / 66.6)),
      child: Center(
          child: widget.icon),
    );
  }
}
