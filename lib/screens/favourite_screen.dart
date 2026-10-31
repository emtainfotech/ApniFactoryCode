import 'package:flutter/material.dart';

import '../res/colors.dart';
import '../res/textstyle.dart';
import '../res/widgets/back_button_widget.dart';
import '../utils.dart';

class FavouriteScreen extends StatefulWidget {
  const FavouriteScreen({super.key});

  @override
  State<FavouriteScreen> createState() => _FavouriteScreenState();
}

class _FavouriteScreenState extends State<FavouriteScreen> {
  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(
      body: ListView(
        children: [
          SizedBox(
            height: height / 26.6,
          ),
          Padding(
            padding: EdgeInsets.symmetric(horizontal: width / 16.2),
            child: InkWell(onTap: () {
              Navigator.pop(context);
            },
              child: BackButtonWidget(title: "Favorite Orders",)),
          ),
          SizedBox(
            height: height / 23.5,
          ),
          Padding(
            padding: EdgeInsets.symmetric(horizontal: width / 16.2),
            child: Utils.commonTextFormField(context,
                height: height,
                width: width,
                labelText: "Search",
                suffixIcon: Icon(
                  Icons.search,
                  color: AppColors.lightText,
                )),
          ),
          SizedBox(
            height: height / 23.5,
          ),
          ListView.builder(
            itemCount: 3,
            shrinkWrap: true,
            physics: NeverScrollableScrollPhysics(),
            itemBuilder: (context, index) {
              return Padding(
                padding: EdgeInsets.symmetric(
                    horizontal: width / 16.2, vertical: width / 70),
                child: Card(
                  child: Container(
                    height: height / 3.1,
                    width: width,
                    decoration: BoxDecoration(
                        boxShadow: [
                          BoxShadow(
                              blurRadius: 32,
                              color: AppColors.blackColor.withOpacity(0.1))
                        ],
                        // border: Border.all(color: AppColors.primaryColor),
                        borderRadius: BorderRadius.circular(height / 66.6),
                        color: AppColors.whiteColor),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          height: height / 5.5,
                          width: width,
                          decoration: BoxDecoration(
                              image: DecorationImage(
                                  image: NetworkImage(
                                      "https://img.freepik.com/free-photo/modern-bathroom-with-toilet-bathtub_181624-40037.jpg?size=626&ext=jpg&ga=GA1.1.1375282485.1702704299&semt=ais"),
                                  fit: BoxFit.cover)),
                        ),
                        SizedBox(
                          height: height / 60,
                        ),
                        Padding(
                          padding: EdgeInsets.symmetric(
                              horizontal: width / 16.2, ),
                          child: Row(crossAxisAlignment: CrossAxisAlignment.start,
                            children: [

                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    "Sanitary Solutions",
                                    style: AppTextStyle.poppinsRegular16(
                                        context: context,
                                        fontWeight: FontWeight.w600,
                                        color: AppColors.secondaryColor),
                                  ),
                                  Text(
                                    "Parryware . 12.00 for more",
                                    style: AppTextStyle.poppinsSemibold10(
                                        context: context,
                                        color: AppColors.lightText,
                                        fontWeight: FontWeight.w500),
                                  ),
                                  Row(
                                    children: [
                                      Icon(Icons.watch_later_outlined,size: height/60,),
                                      SizedBox(width: width/80,),
                                      Text(
                                        "2-4 days ",
                                        style: AppTextStyle.poppinsSemibold10(
                                            context: context,
                                            color: AppColors.lightText,
                                            fontWeight: FontWeight.w500),
                                      ),
                                    ],
                                  ),
                                  SizedBox(
                                    height: height / 90,
                                  ),

                                  Row(
                                    children: [
                                      Icon(Icons.percent,size: height/40,color: AppColors.primaryColor,),
                                      SizedBox(width: width/50,),
                                      Text(
                                        "Flat 50% off",
                                        style: AppTextStyle.poppinsRegular14(
                                            context: context,color: AppColors.primaryColor),
                                      ),

                                    ],
                                  ),
                                ],
                              ),
                              Spacer(),
                              Container(
                                height: height / 47,
                                width: width / 10,
                                decoration:
                                    BoxDecoration(color: Colors.green),
                                child: Center(
                                    child: Text(
                                  "5.0",
                                  style: AppTextStyle.poppinsSemibold10(
                                      context: context,
                                      color: AppColors.whiteColor,
                                      fontWeight: FontWeight.w500),
                                )),
                              )
                            ],
                          ),
                        ),


                      ],
                    ),
                  ),
                ),
              );
            },
          ),
          SizedBox(
            height: height / 23.3,
          ),
        ],
      ),
    );
  }
}
