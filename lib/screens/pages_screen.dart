import 'package:apni_factory/provider/homeScreen_provider.dart';
import 'package:apni_factory/provider/pages_provider.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/res/widgets/back_button_widget.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:apni_factory/utils.dart';
import 'package:flutter/material.dart';
import 'package:flutter_widget_from_html/flutter_widget_from_html.dart';

import 'package:provider/provider.dart';

class PagesScreen extends StatefulWidget {
  final String slug;
  const PagesScreen({super.key, required this.slug});

  @override
  State<PagesScreen> createState() => _PagesScreenState();
}

class _PagesScreenState extends State<PagesScreen> {
  @override
  void initState() {
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      await Provider.of<HomeScreenProvider>(context, listen: false)
          .getPage(context: context, slug: widget.slug);
    });
    // TODO: implement initState
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(
      body: Consumer<HomeScreenProvider>(
        builder: (context, provider, child) {
          return provider.loadingGetPages
              ? CircularProgressIndicatorWidget()
              : ListView(
                  children: [
                    // BackButtonListener(child: Text(), onBackButtonPressed: () {
                    //   Navigator.pop(context);
                    // },),
                    SizedBox(
                      height: height / 26.6,
                    ),
                    Padding(
                      padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                      child: BackButtonWidget(title:  provider.getPagesModel?.name ?? "" ,),
                    ),

                    // Padding(
                    //   padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                    //   child: InkWell(
                    //     onTap: () {
                    //       Navigator.pop(context);
                    //     },
                    //     child: BackButtonWidget(
                    //       title: provider.getPagesModel
                    //               ?.data?( widget.slug).name ??
                    //           "",
                    //     ),
                    //   ),
                    // ),
                    SizedBox(
                      height: height / 23.5,
                    ),
                    Padding(
                        padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                        child: HtmlWidget(provider.getPagesModel?.data ?? "")),
                    SizedBox(
                      height: height / 23.5,
                    ),
                  ],
                );
        },
      ),
    );
  }
}
