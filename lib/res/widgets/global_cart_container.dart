import 'package:apni_factory/provider/cart_screen_provider.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:apni_factory/res/widgets/round_button.dart';
import 'package:apni_factory/screens/cart_screen.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class GlobalCartContainer extends StatelessWidget {
  final double width;
  final double height;
  const GlobalCartContainer(
      {super.key, required this.width, required this.height});

  @override
  Widget build(BuildContext context) {
    return Consumer<CartScreenProvider>(
      builder: (context, cartProvider, child) {
        if (cartProvider.userCartModel?.data != null &&
            cartProvider.userCartModel!.data!.isNotEmpty) {
          // Show the container if items exist in the cart
          return cartProvider.loadingUserCart
              ? CircularProgressIndicatorWidget()
              : Align(
                  alignment: Alignment.bottomCenter,
                  child: Container(
                    width: width,
                    height: height / 10,
                    decoration: BoxDecoration(
                      color: AppColors.whiteColor,
                      boxShadow: [BoxShadow()],
                      borderRadius: BorderRadius.circular(height / 40),
                    ),
                    child: Padding(
                      padding: EdgeInsets.symmetric(horizontal: height / 60),
                      child: Row(
                        children: [
                          Container(
                            height: height / 25,
                            width: height / 25,
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(height / 60),
                              image: DecorationImage(
                                image: NetworkImage(
                                    "https://cdn.pixabay.com/photo/2013/06/07/09/53/paint-117599_1280.png"),
                                fit: BoxFit.cover,
                              ),
                            ),
                          ),
                          SizedBox(
                            width: width / 40,
                          ),
                          Text(
                            "${cartProvider.userCartModel!.counter} items",
                            style: AppTextStyle.poppinsRegular16(
                              context: context,
                              color: AppColors.secondaryColor,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          SizedBox(
                            width: width / 3,
                          ),
                          Expanded(
                            child: RoundButton(
                              title: "Go to Cart",
                              backgroundColor: AppColors.primaryColor,
                              onPressed: () {
                                       Navigator.push(context, MaterialPageRoute(builder: (context) {
                                         return CartScreen();
                                       },));
                                                                                                                                            
                              },
                            ),
                          )
                        ],
                      ),
                    ),
                  ),
                );
        }

        return SizedBox.shrink();
      },
    );
  }
}
