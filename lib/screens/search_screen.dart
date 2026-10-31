// import 'package:apni_factory/provider/homeScreen_provider.dart';
// import 'package:apni_factory/res/colors.dart';
// import 'package:apni_factory/res/textstyle.dart';
// import 'package:apni_factory/res/widgets/back_button_widget.dart';
// import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
// import 'package:apni_factory/res/widgets/search_widget.dart';
// import 'package:apni_factory/utils.dart';
// import 'package:flutter/material.dart';
// import 'package:provider/provider.dart';

// class SearchScreen extends StatefulWidget {
//   const SearchScreen({super.key});

//   @override
//   State<SearchScreen> createState() => _SearchScreenState();
// }

// class _SearchScreenState extends State<SearchScreen> {
//   @override
//   Widget build(BuildContext context) {
//     double height = MediaQuery.of(context).size.height;
//     double width = MediaQuery.of(context).size.width;
//     return Scaffold(
//       body: Consumer<HomeScreenProvider>(
//         builder: (context, provider, child) {
//           return provider.loadingSearch
//               ? CircularProgressIndicatorWidget()
//               : ListView(
//                   children: [
//                     SizedBox(
//                       height: height / 26.6,
//                     ),
//                     SearchWidget(provider: provider),
                   
//                     SizedBox(
//                       height: height / 26.6,
//                     ),
//                     ListView.builder(
//                       shrinkWrap: true,
//                       physics: NeverScrollableScrollPhysics(),
//                       itemCount: provider.searchModel?.data?.length,
//                       itemBuilder: (context, index) {
//                         return Padding(
//                           padding:
//                               EdgeInsets.symmetric(horizontal: width / 16.2),
//                           child: Text(
//                               provider.searchModel?.data?[index].name ?? "",
//                               style: AppTextStyle.poppinsMedium15(
//                                   context: context,
//                                   color: AppColors.secondaryColor)),
//                         );
//                       },
//                     )
//                   ],
//                 );
//         },
//       ),
//     );
//   }
// }
