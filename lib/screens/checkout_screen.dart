import 'dart:convert';
import 'dart:developer';
import 'package:apni_factory/api_services/hash_service';
import 'package:apni_factory/provider/cart_screen_provider.dart';
import 'package:apni_factory/res/widgets/circular_progress_indicator_widget.dart';
import 'package:apni_factory/res/widgets/round_button.dart';
import 'package:apni_factory/screens/payment_failure_screen.dart';
import 'package:apni_factory/screens/payment_successful_screen.dart';
import 'package:flutter/material.dart';
import 'package:payu_checkoutpro_flutter/PayUConstantKeys.dart';
import 'package:payu_checkoutpro_flutter/payu_checkoutpro_flutter.dart';
import 'package:provider/provider.dart';
import '../res/asset_image.dart';
import '../res/colors.dart';
import '../res/textstyle.dart';
import '../res/widgets/back_button_widget.dart';

class CheckOutScreen extends StatefulWidget {
  
  const CheckOutScreen({super.key});

  @override
  State<CheckOutScreen> createState() => _CheckOutScreenState();
}

class _CheckOutScreenState extends State<CheckOutScreen>
    implements PayUCheckoutProProtocol {
  String? serverHash;
  @override
  void initState() {
    WidgetsBinding.instance.addPostFrameCallback((timeStamp) async {
      Provider.of<CartScreenProvider>(context, listen: false).checkoutPro =
          PayUCheckoutProFlutter(this);
      // Provider.of<CheckoutProvider>(context, listen: false)
      //     .getAddress(context: context);
    });
    // TODO: implement initState
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Scaffold(
      body: Consumer<CartScreenProvider>(
        builder: (context, provider, child) {
          return
              // provider.loadingGetAddress ||
              provider.loadingAddAddress ||
                      provider.loadingGetAddressDetail ||
                      provider.loadingDeleteAddress ||
                      provider.loadingorder
                  ? CircularProgressIndicatorWidget()
                  : Stack(
                      children: [
                        Align(
                            alignment: Alignment.topRight,
                            child: Image.asset(
                              AppAssetImage.signUpCurve,
                              color: AppColors.primaryColor,
                            )),
                        ListView(
                          children: [
                            SizedBox(
                              height: height / 26.6,
                            ),
                            Padding(
                              padding: EdgeInsets.symmetric(
                                  horizontal: width / 16.2),
                              child: InkWell(
                                  onTap: () {
                                    Navigator.pop(context);
                                  },
                                  child: BackButtonWidget()),
                            ),
                            SizedBox(
                              height: height / 26.6,
                            ),
                            Padding(
                              padding: EdgeInsets.symmetric(
                                  horizontal: width / 16.2),
                              child: Text(
                                "Select payment method",
                                style: AppTextStyle.poppinsRegular16(
                                    context: context,color: AppColors.secondaryColor,
                                    fontWeight: FontWeight.w600),
                              ),
                            ),
                            SizedBox(
                              height: height / 22,
                            ),
                          
                            Padding(
                              padding: EdgeInsets.symmetric(
                                  horizontal: width / 16.2),
                              child: Text(
                                "Payment method",
                                style: AppTextStyle.poppinsRegular14(
                                    context: context,
                                    fontWeight: FontWeight.w600),
                              ),
                            ),
                            SizedBox(
                              height: height / 42,
                            ),
                            ListView.builder(
                              physics: NeverScrollableScrollPhysics(),
                              shrinkWrap: true,
                              itemCount: provider.paymentList.length,
                              itemBuilder: (context, index) {
                                return Padding(
                                  padding: EdgeInsets.symmetric(
                                      horizontal: width / 16.2,
                                      vertical: height / 80),
                                  child: Container(
                                    height: height / 17.7,
                                    width: width,
                                    decoration: BoxDecoration(
                                        boxShadow: [
                                          BoxShadow(
                                              blurRadius: 32,
                                              color: AppColors.blackColor
                                                  .withOpacity(0.1))
                                        ], // border: Border.all(color: AppColors.primaryColor),
                                        borderRadius: BorderRadius.circular(
                                            height / 66.6),
                                        color: AppColors.whiteColor),
                                    child: Padding(
                                      padding: EdgeInsets.symmetric(
                                          horizontal: width / 16.2),
                                      child: Row(
                                        children: [
                                          Image.network(
                                            "https://upload.wikimedia.org/wikipedia/commons/thumb/b/b7/MasterCard_Logo.svg/1280px-MasterCard_Logo.svg.png",
                                            height: height / 40,
                                          ),
                                          SizedBox(
                                            width: width / 20,
                                          ),
                                          Text(
                                            provider.paymentList[index],
                                            style: AppTextStyle.poppinsBold13(
                                                context: context,
                                                color:
                                                    AppColors.secondaryColor),
                                          ),
                                          Spacer(),
                                          Radio(
                                            activeColor: AppColors.primaryColor,
                                            value: provider.paymentList[index],
                                            groupValue:
                                                provider.selectedPayment,
                                            onChanged: (value) {
                                              provider.selectPayment(value,
                                                  context: context);
                                            },
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),
                                );
                              },
                            ),
                            SizedBox(
                              height: height / 21,
                            ),
                            Padding(
                              padding: EdgeInsets.symmetric(
                                  horizontal: width / 16.2),
                              child: RoundButton(
                                title: "Pay",
                                onPressed: () async {
                                  provider
                                      .order(
                                    context: context,
                                    height: height,
                                    addressId: provider.selectedAddressId ?? "",
                                  )
                                      .then(
                                    (value) {
                                      var orderModel = provider.orderModel;
                                      if (orderModel == null ||
                                          orderModel.data == null) {
                                        ScaffoldMessenger.of(context)
                                            .showSnackBar(
                                          const SnackBar(
                                              content: Text(
                                                  "Order creation failed")),
                                        );
                                        return;
                                      }
                                      var orderData = orderModel.data!;
                                      var hashParameter =
                                          orderModel.hashparameter!;
                                      // serverHash = orderModel.hash;
                                      String phone = "";
                                      if (orderData.address != null) {
                                        try {
                                          final addressJson =
                                              orderData.address is String
                                                  ? jsonDecode(
                                                      orderData.address ?? "")
                                                  : orderData.address;
                                          phone = addressJson['phoneno'] ?? "";
                                        } catch (e) {
                                          log(
                                              "Address parsing failed: $e");
                                        }
                                      }
                                      String txnId =
                                          hashParameter.txnid.toString();
                                      String amount =
                                          hashParameter.amount.toString();
                                      String productInfo =
                                          hashParameter.productinfo.toString();
                                      String firstName = hashParameter.firstname
                                          .toString(); // Replace
                                      String email = hashParameter.email
                                          .toString(); // Replace

                                      provider.checkoutPro.openCheckoutScreen(
                                        payUPaymentParams: {
                                          PayUPaymentParamKey.key:
                                              orderModel.hashparameter?.key,
                                          PayUPaymentParamKey.userCredential:
                                              "default",
                                          PayUPaymentParamKey.transactionId:
                                              txnId,
                                          PayUPaymentParamKey.amount: amount,
                                          PayUPaymentParamKey.productInfo:
                                              productInfo,
                                          PayUPaymentParamKey.firstName:
                                              firstName,
                                          PayUPaymentParamKey.email: email,
                                          PayUPaymentParamKey.phone: phone,
                                          PayUPaymentParamKey.ios_surl:
                                              orderModel.surl,
                                          PayUPaymentParamKey.ios_furl:
                                              orderModel.furl,
                                          PayUPaymentParamKey.android_surl:
                                              orderModel.surl,
                                          PayUPaymentParamKey.android_furl:
                                              orderModel.furl,
                                          PayUPaymentParamKey.environment: "0",
                                        },
                                        payUCheckoutProConfig: {
                                          "showExitConfirmationOnPaymentScreen":
                                              true,
                                        },
                                      );
                                    },
                                  );
                                },
                              ),
                            ),
                            SizedBox(
                              height: height / 21,
                            ),
                          ],
                        )
                      ],
                    );
        },
      ),
    );
  }

  @override
  generateHash(Map response) {
    Map hashResponse = HashService.generateHash(response);
    log(response.toString());

    Provider.of<CartScreenProvider>(context,listen: false).checkoutPro.hashGenerated(hash: hashResponse);

    // log("⚡ PayU asked for hash: $response");
    // var hashName = response[PayUHashConstantsKeys.hashName];
    // var hashStringWithoutSalt = response[PayUHashConstantsKeys.hashString];
    // var hashType = response[PayUHashConstantsKeys.hashType];
    // var postSalt = response[PayUHashConstantsKeys.postSalt];
    // var hash = serverHash!;
    // _checkoutPro.hashGenerated(hash:
    // {
    //   "hashName": response['hashName'],
    //   "hashString": hash,
    // }
    // );

//  <Get Hash Backend with < hashString, merchantSalt , postSalt >
// Call hashGenerated with HashResponse< hashName , Hash>
// _checkoutPro.hashGenerated(hash: hashResponse);

    // Pass response param to your backend server
    // Backend will generate the hash which you need to pass to SDK
    // hashResponse: is the response which you get from your server

    // if (serverHash != null) {
    //   log(response['hashName']);
    //   _checkoutPro.hashGenerated(hash: {
    //     "hashName": response['hashName'],
    //     "hashString": serverHash!,
    //   });
    // }
    // else {
    //  log("❌ No hash available from backend");
    // }

    // TODO: implement generateHash
  }

  @override
  onError(Map? response) {
    log("⚡ PayU Error: $response");
    // TODO: implement onError
  }

  @override
  onPaymentCancel(Map? response) {
    log("⚠️ Payment Cancelled by User");

WidgetsBinding.instance.addPostFrameCallback((_) {
    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(
        builder: (_) => const PaymentFailureScreen(
          error: "Payment cancelled by user",
        ),
      ),
      (route) => false,
    );
  });

  // Navigator.pushReplacement(
  //   context,
  //   MaterialPageRoute(
  //     builder: (_) => const PaymentFailureScreen(
  //       error: "Payment cancelled by user",
  //     ),
  //   ),
  // );
    // TODO: implement onPaymentCancel
  }

  @override
  onPaymentFailure(response) {
    log("❌ Payment Failure: $response");

     WidgetsBinding.instance.addPostFrameCallback((_) {
    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(
        builder: (_) => PaymentFailureScreen(
          error: response?['error_Message']?.toString() ?? "Payment Failed",
        ),
      ),
      (route) => false,
    );
  });

// Navigator.pushAndRemoveUntil(
//     context,
//     MaterialPageRoute(
//       builder: (_) => PaymentFailureScreen(
//         error: response?['error_Message']?.toString() ??
//             "Payment Failed",
//       ),
//     ),(route) => false,
//   );

    // ScaffoldMessenger.of(context).showSnackBar(
    //   const SnackBar(content: Text("Payment Failed")),
    // );
    // TODO: implement onPaymentFailure
  }

  @override
  onPaymentSuccess(response) {
  log("Payment Success : ${response}");

  WidgetsBinding.instance.addPostFrameCallback((_) {
    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(
        builder: (_) => const PaymentSuccessfulScreen(),
      ),
      (route) => false, // 🔥 clears PayU + checkout screen
    );
  });



  //     Navigator.pushAndRemoveUntil(
  //   context,
  //   MaterialPageRoute(
  //     builder: (_) => PaymentSuccessfulScreen(
  //       // txnId: response['txnid']?.toString(),
  //       // amount: response['amount']?.toString(),
  //     ),
  //   ),(route) => false,
  // );

    // ScaffoldMessenger.of(context).showSnackBar(
    //   const SnackBar(content: Text("Payment Successful")),
    // );
    // TODO: implement onPaymentSuccess
  }
}
