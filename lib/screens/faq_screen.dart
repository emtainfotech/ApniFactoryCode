import 'package:apni_factory/provider/faq_provider.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:apni_factory/utils.dart';
import 'package:flutter/material.dart';
import 'package:flutter_widget_from_html/flutter_widget_from_html.dart';
import 'package:provider/provider.dart';

import '../res/colors.dart';
import '../res/textstyle.dart';
import '../res/widgets/back_button_widget.dart';

class FaqScreen extends StatefulWidget {
  const FaqScreen({super.key});

  @override
  State<FaqScreen> createState() => _FaqScreenState();
}

class _FaqScreenState extends State<FaqScreen> {
  @override
  void initState() {
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      await Provider.of<FaqProvider>(context, listen: false)
          .getFaq(context: context);
    });
    // TODO: implement initState
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(
      body: Consumer<FaqProvider>(
        builder: (context, provider, child) {
          return provider.loadingGetFaq
              ? CircularProgressIndicatorWidget()
              : ListView(
                  children: [
                    SizedBox(
                      height: height / 26.6,
                    ),
                    Padding(
                      padding: EdgeInsets.symmetric(horizontal: width / 16.2),
                      child: InkWell(
                          onTap: () {
                            Navigator.pop(context);
                          },
                          child: BackButtonWidget(
                            title: "Frequently asked \nquestion",
                          )),
                    ),
                    SizedBox(
                      height: height / 23.5,
                    ),
                    ListView.builder(
                      shrinkWrap: true,
                      physics: NeverScrollableScrollPhysics(),
                      itemCount: provider.getFaqModel?.data?.length,
                      itemBuilder: (context, index) {
                        return Padding(
                          padding:
                              EdgeInsets.symmetric(horizontal: width / 16.2),
                          child: ExpansionTile(
                            title: Text(
                                provider.getFaqModel?.data?[index].question ??
                                    "",
                                style: AppTextStyle.poppinsMedium15(
                                    context: context,
                                    color: AppColors.secondaryColor)),
                            children: <Widget>[
                              ListTile(
                                title: HtmlWidget(Utils.htmlDocument(
                                  provider.getFaqModel?.data?[index].answer ??
                                      "",
                                ).outerHtml,textStyle:
                                 AppTextStyle.poppinsMedium15(
                                    context: context,
                                    color: AppColors.neutralGrey)
                                 ),
                              
                                onTap: () {
                                  // Do something when item 1 is tapped
                                },
                              ),
                            ],
                          ),
                        );
                      },
                    )
                  ],
                );
        },
      ),
    );
  }
}
