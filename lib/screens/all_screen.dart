
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/screens/cart_screen.dart';

import 'package:apni_factory/screens/home_screen.dart';
import 'package:apni_factory/screens/profile_screen.dart';
import 'package:apni_factory/screens/wishlist_screen.dart';

import 'package:flutter/material.dart';

class AllScreen extends StatefulWidget {
  const AllScreen({super.key});

  @override
  State<AllScreen> createState() => _AllScreenState();
}

class _AllScreenState extends State<AllScreen> {
  int selectedIndex = 0;
  @override
  Widget build(BuildContext context) {

    return Scaffold(
      body: _buildPage(selectedIndex),
      bottomNavigationBar: BottomNavigationBar(
          selectedItemColor: AppColors.primaryColor,
          unselectedItemColor: AppColors.lightText,
          backgroundColor: AppColors.whiteColor,
          currentIndex: selectedIndex,
          onTap: (value) {
            selectedIndex = value;
            setState(() {});
          },
          items: [
            BottomNavigationBarItem(
                icon: Icon(
                  Icons.home_filled,
                ),
                label: "Home"),
            BottomNavigationBarItem(
                icon: Icon(Icons.favorite_outline), label: "Favourite"),
          
            BottomNavigationBarItem(
                icon: Icon(Icons.add_shopping_cart), label: "Cart"),
            BottomNavigationBarItem(
                icon: Icon(Icons.person_outline), label: "Account")
          ]),
    );
  }
}

Widget _buildPage(int index) {
  switch (index) {
    case 0:
      return HomeScreen();
    case 1:
      return WishlistScreen();
   
    case 2:
      return CartScreen();
    case 3:
      return ProfileScreen();

    default:
      return Container();
  }
}
