import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/res/widgets/round_button.dart';
import 'package:apni_factory/screens/all_screen.dart';
import 'package:apni_factory/screens/home_screen.dart';
import 'package:apni_factory/screens/order_history_screen.dart';
import 'package:apni_factory/screens/track_order_screen.dart';
import 'package:flutter/gestures.dart';
import 'package:flutter/material.dart';

class PaymentSuccessfulScreen extends StatefulWidget {
  const PaymentSuccessfulScreen({super.key});

  @override
  State<PaymentSuccessfulScreen> createState() =>
      _PaymentSuccessfulScreenState();
}

class _PaymentSuccessfulScreenState extends State<PaymentSuccessfulScreen> {
  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(
        body: Padding(
      padding: EdgeInsets.symmetric(horizontal: width / 20),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Image(
            image: AssetImage(
              "assets/images/check-mark.png",
            ),
            height: height / 10,
          ),
          SizedBox(
            height: height / 26.6,
          ),
          Text(
            "Order Placed",
            style: AppTextStyle.poppinsMedium20(
                context: context, fontWeight: FontWeight.w600),
          ),
          SizedBox(
            height: height / 70,
          ),
          Center(
            child: Text.rich(
              textAlign: TextAlign.center,
              TextSpan(
                text: 'Your order has been placed successfully. ',
                style: AppTextStyle.poppinsRegular14(
                    fontWeight: FontWeight.w400,
                    context: context,
                    color: AppColors.secondaryColor),
                children: [
                  TextSpan(
                    text: 'Order History',
                    style: AppTextStyle.poppinsRegular14(
                        fontWeight: FontWeight.w400,
                        context: context,
                        color: AppColors.primaryColor),
                    recognizer: TapGestureRecognizer()
                      ..onTap = () {
                        Navigator.push(context, MaterialPageRoute(
                          builder: (context) {
                            return OrderHistoryScreen();
                          },
                        ));
                      },
                  ),
                ],
              ),
            ),
          ),
          SizedBox(
            height: height / 70,
          ),
          Center(
            child: Text.rich(
              textAlign: TextAlign.center,
              TextSpan(
                text: 'Get delivery by ',
                style: AppTextStyle.poppinsRegular14(
                    fontWeight: FontWeight.w200,
                    context: context,
                    color: AppColors.secondaryColor),
                children: [
                  TextSpan(
                    text: 'Mon, 06 Feb - Thu, 09 Feb',
                    style: AppTextStyle.poppinsRegular14(
                        fontWeight: FontWeight.w400,
                        context: context,
                        color: AppColors.secondaryColor),
                    recognizer: TapGestureRecognizer()
                      ..onTap = () {
                        print('Sign up tapped');
                      },
                  ),
                ],
              ),
            ),
          ),
          SizedBox(
            height: height / 90,
          ),
          // Center(
          //   child: TextButton(
          //       onPressed: () {
          //         Navigator.push(context, MaterialPageRoute(builder: (context) {
          //           return TrackOrderScreen(orderid: orderid);
          //         },));
          //       },
          //       child: Text(
          //         "Track My Order",
          //         style: AppTextStyle.poppinsRegular14(
          //             fontWeight: FontWeight.w400,
          //             context: context,
          //             color: AppColors.primaryColor),
          //       )),
          // ),
          SizedBox(
            height: height / 20,
          ),
          Padding(
            padding: EdgeInsets.symmetric(horizontal: width / 10),
            child: RoundButton(
              title: "Continue Shopping",
              onPressed: () {
                Navigator.push(context, MaterialPageRoute(builder: (context) {
                  return AllScreen();
                },));
              },
            ),
          ),
        ],
      ),
    ));
  }
}
