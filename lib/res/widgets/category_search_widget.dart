import 'package:apni_factory/provider/category_screen_provider.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/utils.dart';
import 'package:flutter/material.dart';

class CategorySearchWidget extends StatelessWidget {
    final CategoryScreenProvider provider;
  const CategorySearchWidget({super.key, required this.provider});

 @override
  Widget build(BuildContext context) {
     double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Padding(
      padding: EdgeInsets.symmetric(horizontal: width / 16.2),
      child: Utils.commonTextFormField(
        context,
        height: height,
        width: width,
        labelText: "Search",
        controller:provider.categorySearchController,
        suffixIcon: Icon(
          Icons.search,
          color: AppColors.lightText,
        ),
        onChanged: (value)  {
         provider.onSearchChanged(value, height, context);
        },
        // ontap: () {
        //   Navigator.push(context, MaterialPageRoute(builder: (context) {
        //    return SearchScreen();
        //   },));
        // },
      ),
    );
  }
}




  
