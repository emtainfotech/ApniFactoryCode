import 'dart:convert';
import 'dart:developer';
import 'package:apni_factory/model/add_address_model.dart';
import 'package:apni_factory/model/check_availability_model.dart';
import 'package:apni_factory/model/city_list_model.dart';
import 'package:apni_factory/model/delete_address_model.dart';
import 'package:apni_factory/model/delete_cart_attribute_model.dart';
import 'package:apni_factory/model/delete_cart_model.dart';
import 'package:apni_factory/model/empty_cart_model.dart';
import 'package:apni_factory/model/get_address_detail_model.dart';
import 'package:apni_factory/model/get_address_list_model.dart';
import 'package:apni_factory/model/increase_cart_quantity_model.dart';
import 'package:apni_factory/model/order_model.dart';
import 'package:apni_factory/model/pincode_list_model.dart';
import 'package:apni_factory/model/related_product_model.dart';
import 'package:apni_factory/model/state_model.dart';
import 'package:apni_factory/model/update_address_model.dart';
import 'package:apni_factory/model/update_cart_address_model.dart';
import 'package:apni_factory/model/user_cart_model.dart';
import 'package:apni_factory/res/widgets/edit_address_widget.dart';
import 'package:apni_factory/screens/payment_successful_screen.dart';
import 'package:flutter/material.dart';
import 'package:payu_checkoutpro_flutter/PayUConstantKeys.dart';
import 'package:payu_checkoutpro_flutter/payu_checkoutpro_flutter.dart';
import '../api_services/creator_api_services.dart';
import '../utils.dart';

class CartScreenProvider extends ChangeNotifier {
  bool loadingAddAddress = false;
  TextEditingController nameController = TextEditingController();
  TextEditingController addressController = TextEditingController();
  TextEditingController landmarkController = TextEditingController();
  TextEditingController countryController = TextEditingController();
  TextEditingController phonenoController = TextEditingController();
  TextEditingController locationController = TextEditingController();
  TextEditingController identitynameController = TextEditingController();
  TextEditingController editNameController = TextEditingController();
  TextEditingController editLandmarkController = TextEditingController();
  TextEditingController editAddressController = TextEditingController();
  TextEditingController editCountryController = TextEditingController();
  TextEditingController editStateController = TextEditingController();
  TextEditingController editCityController = TextEditingController();
  TextEditingController editPincodeController = TextEditingController();
  TextEditingController editPhonenoController = TextEditingController();
  TextEditingController editLocationController = TextEditingController();
  TextEditingController editIdentitynameController = TextEditingController();
  String? selectedAddressText;
  Future<void> addAddress({
    required BuildContext context,
    required double height,
    String? name,
    String? address,
    String? landmark,
    String? country,
    String? state,
    String? city,
    String? pincode,
    String? phoneno,
    String? identityname,
  }) async {
    try {
      if (name != null &&
          name.isNotEmpty &&
          address != null &&
          address.isNotEmpty &&
          landmark != null &&
          landmark.isNotEmpty &&
          country != null &&
          country.isNotEmpty &&
          state != null &&
          state.isNotEmpty &&
          city != null &&
          city.isNotEmpty &&
          pincode != null &&
          pincode.isNotEmpty &&
          phoneno != null &&
          phoneno.isNotEmpty &&
          identityname != null &&
          identityname.isNotEmpty) {
        loadingAddAddress = true;
        notifyListeners();

        AddAddressModel? addAddressModel =
            await CreatorApiServices.addAddressDetails(context,
                name: name,
                landmark1: address,
                landmark2: landmark,
                country: country,
                state: state,
                city: city,
                pincode: pincode,
                phoneno: phoneno,
                identityname: identityname,
                type: "transport");

        if (addAddressModel != null && addAddressModel.code == 100) {
          await updateCartAddress(
              context: context,
              height: height,
              addressId: addAddressModel.data?.id.toString() ?? "");
          await checkAddressAvailability(
              context: context,
              addressid: addAddressModel.data?.id.toString() ?? "",
              height: height);

          await getAddressList(context: context);
          selectedAddressId = addAddressModel.data?.id?.toString();
          selectedAddressText = "${addAddressModel.data?.landmark1 ?? ""}, "
              "${addAddressModel.data?.landmark2 ?? ""}, "
              "${addAddressModel.data?.city ?? ""}, "
              "${addAddressModel.data?.state ?? ""}, "
              "${addAddressModel.data?.pincode ?? ""}";

          nameController.clear();
          addressController.clear();
          landmarkController.clear();
          countryController.clear();
          stateDropDownValue = "State*";
          cityDropDownValue = "City*";
          pincodeDropDownValue = "Pincode*";

          phonenoController.clear();

          identitynameController.clear();
          loadingAddAddress = false;
          notifyListeners();

          Navigator.pop(context);
          Navigator.pop(context);

          Utils.commonSnackBar(context,
              height: height, message: addAddressModel.msg.toString());
        } else {
          loadingAddAddress = false;
          notifyListeners();
        }
      } else {
        Navigator.pop(context);
        Navigator.pop(context);
        Utils.commonSnackBar(context,
            height: height, message: "Enter all details");
      }
    } catch (e) {
      log("add address error: ${e.toString()}");
      loadingAddAddress = false;
      notifyListeners();
    }
  }

  bool loadingGetAddressList = true;
  GetAddressListModel? getAddressListModel;
  List? addressList = [];
  Future<void> getAddressList({
    required BuildContext context,
  }) async {
    try {
      loadingGetAddressList = true;
      notifyListeners();

      getAddressListModel =
          await CreatorApiServices.getAddressListDetails(context);

      if (getAddressListModel != null && getAddressListModel!.code == 100) {
        loadingGetAddressList = false;
        notifyListeners();
      } else {
        loadingGetAddressList = false;
        notifyListeners();
      }
    } catch (e) {
      log("get address list error: ${e.toString()}");
      loadingGetAddressList = false;
      notifyListeners();
    }
  }

  String? selectedAddressId;

  void selectAddress(
    value, {
    required BuildContext context,
    required double height,
    required String addressId,
    required String addressText,
  }) async {
    selectedAddressId = addressId;
    selectedAddressText = addressText;
    // selectedId = addressId.split(" ").last;
    notifyListeners(); // UI ONLY
    await updateCartAddress(
      context: context,
      height: height,
      addressId: addressId,
    );
    Navigator.pop(context);
  }

  bool loadingGetAddressDetail = false;
  GetAddressDetailModel? getAddressDetailModel;
  Future<void> getIdAddress(
      {required BuildContext context, required String addressId}) async {
    try {
      loadingGetAddressDetail = true;
      getAddressDetailModel = await CreatorApiServices.getIdAddressDetails(
          context,
          addressId: addressId);
      notifyListeners();
      if (getAddressDetailModel != null && getAddressDetailModel!.code == 100) {
        loadingGetAddressDetail = false;
        editNameController.text = getAddressDetailModel?.data?.name ?? "";
        editLandmarkController.text =
            getAddressDetailModel?.data?.landmark2 ?? "";
        editAddressController.text =
            getAddressDetailModel?.data?.landmark1 ?? "";
        editCountryController.text = getAddressDetailModel?.data?.country ?? "";
        editStateController.text = getAddressDetailModel?.data?.state ?? "";
        editCityController.text = getAddressDetailModel?.data?.city ?? "";
        editPincodeController.text = getAddressDetailModel?.data?.pincode ?? "";
        editPhonenoController.text = getAddressDetailModel?.data?.phoneno ?? "";
        editLocationController.text =
            getAddressDetailModel?.data?.location ?? "";
        editIdentitynameController.text =
            getAddressDetailModel?.data?.identityname ?? "";
        showModalBottomSheet(
          context: context,
          builder: (context) {
            return EditAddressWidget(
              addressId: addressId,
            );
          },
        );

        notifyListeners();
      } else {
        loadingGetAddressDetail = true;
        notifyListeners();
      }
    } catch (e) {
      log("get id address error: ${e.toString()}");
    }
    notifyListeners();
  }

  bool loadingUpdateAddress = false;
  Future<void> updateAddress(
      {required BuildContext context,
      required double height,
      String? name,
      String? landmark,
      String? address,
      String? country,
      String? state,
      String? city,
      String? pincode,
      String? phoneno,
      String? identityname,
      required String addressId}) async {
    try {
      if (name != null &&
          name.isNotEmpty &&
          landmark != null &&
          landmark.isNotEmpty &&
          address != null &&
          address.isNotEmpty &&
          country != null &&
          country.isNotEmpty &&
          state != null &&
          state.isNotEmpty &&
          city != null &&
          city.isNotEmpty &&
          pincode != null &&
          pincode.isNotEmpty &&
          phoneno != null &&
          phoneno.isNotEmpty &&
          identityname != null &&
          identityname.isNotEmpty) {
        loadingUpdateAddress = true;
        UpdateAddressModel? updateAddressModel =
            await CreatorApiServices.updateAddressDetails(context,
                name: name,
                landmark: landmark,
                address: address,
                country: country,
                state: state,
                city: city,
                pincode: pincode,
                phoneno: phoneno,
                identityname: identityname,
                addressId: addressId,
                type: "transport");
        notifyListeners();
        if (updateAddressModel != null && updateAddressModel.code == 100) {
          await updateCartAddress(
              context: context, height: height, addressId: addressId);
          await getAddressList(context: context);
          if (selectedAddressId == addressId) {
            selectedAddressText = "${editAddressController.text}, "
                "${editLandmarkController.text}, "
                "${editCityController.text}, "
                "${editStateController.text} "
                "${editPincodeController.text} $addressId";
            // selectedAddress = newAddress;
            // selectedId = addressId;
          }

          Navigator.pop(context);
          Navigator.pop(context);
          loadingUpdateAddress = false;
          Utils.commonSnackBar(context,
              height: height, message: updateAddressModel.msg.toString());

          editNameController.clear();
          editLandmarkController.clear();
          editAddressController.clear();
          editCountryController.clear();
          editStateController.clear();
          editCityController.clear();
          editPincodeController.clear();
          editPhonenoController.clear();

          editIdentitynameController.clear();

          notifyListeners();
        } else {
          loadingUpdateAddress = true;
          notifyListeners();
        }
      } else {
        Navigator.pop(context);
        Utils.commonSnackBar(context,
            height: height, message: "Enter all details");
      }
    } catch (e) {
      log("update address error: ${e.toString()}");
    }
    notifyListeners();
  }

  bool loadingDeleteAddress = false;
  Future<void> deleteAddress(
      {required BuildContext context,
      required BuildContext dialogContext,
      required double height,
      required String addressid}) async {
    try {
      loadingDeleteAddress = true;
      notifyListeners();

      DeleteAddressModel? deleteAddressModel =
          await CreatorApiServices.deleteAddressDetails(context,
              addressid: addressid);
      notifyListeners();

      if (deleteAddressModel != null && deleteAddressModel.code == 100) {
        if (selectedAddressId == addressid) {
          selectedAddressId = null;
          selectedAddressText = null;
        }
        Navigator.pop(dialogContext);

        Navigator.pop(context);
        Navigator.pop(context);

        await getAddressList(context: context);
        loadingDeleteAddress = false;
        Utils.commonSnackBar(context,
            height: height, message: deleteAddressModel.msg.toString());
        notifyListeners();
      } else {
        loadingDeleteAddress = true;
        notifyListeners();
      }
    } catch (e) {
      log("delete address error: ${e.toString()}");
    }
    notifyListeners();
  }

  bool loadingUpdateCartAddress = false;
  Future<void> updateCartAddress(
      {required BuildContext context,
      required double height,
      required String addressId}) async {
    try {
      if (addressId != null && addressId.isNotEmpty) {
        loadingUpdateCartAddress = true;
        UpdateCartAddressModel? updateCartAddressModel =
            await CreatorApiServices.updateCartAddressDetail(context,
                addressid: addressId);
        notifyListeners();
        if (updateCartAddressModel != null &&
            updateCartAddressModel.code == 100) {
          await userCart(context: context);

          loadingUpdateCartAddress = false;

          if (context.mounted) {
            Utils.commonSnackBar(context,
                height: height, message: updateCartAddressModel.msg.toString());
          }

          notifyListeners();
        } else {
          loadingUpdateCartAddress = true;
          notifyListeners();
        }
      } else {
        if (context.mounted) {
          Utils.commonSnackBar(context,
              height: height, message: "Select Address");
        }
      }
    } catch (e) {
      log("update cart address error: ${e.toString()}");
    }
    notifyListeners();
  }

  bool loadingStateDropDown = true;
  StateModel? stateModel;
  List<String> stateList = [];
  String stateDropDownValue = "State*";
  Future<void> stateDropDown({required BuildContext context}) async {
    try {
      stateList = [];
      loadingStateDropDown = true;
      notifyListeners();
      stateModel = await CreatorApiServices.stateModelDetails(context);

      if (stateModel != null && stateModel!.code == 100) {
        List? dataList = stateModel!.data;
        if (dataList != null) {
          for (var item in dataList) {
            stateList.add(item ?? "");

            loadingStateDropDown = false;
            notifyListeners();
          }
        }
        stateList.insert(0, "State*");
        notifyListeners();
      }
    } catch (e) {
      log("state drop down error: ${e.toString()}");
    }
    notifyListeners();
  }

  bool loadingCityDropDown = false;
  CityListModel? cityListModel;
  List<String> cityList = ["City*"];
  String cityDropDownValue = "City*";

  Future<void> cityDropDown(
      {required BuildContext context, required String state}) async {
    try {
      cityList = [];

      cityDropDownValue = "City*";
      loadingCityDropDown = true;
      notifyListeners();
      cityListModel =
          await CreatorApiServices.cityModelDetails(context, state: state);

      if (cityListModel != null && cityListModel!.code == 100) {
        List? dataList = cityListModel!.data;
        if (dataList != null) {
          // cityList.removeRange(0, cityList.length-1);
          for (var item in dataList) {
            cityList.add(item ?? "");

            loadingCityDropDown = false;
            notifyListeners();
          }
        }
        cityList.insert(0, "City*");

        notifyListeners();
      }
    } catch (e) {
      log("city drop down error: ${e.toString()}");
    }
    notifyListeners();
  }

  bool loadingPincodeDropDown = false;
  PincodeListModel? pincodeListModel;
  List<String> pincodeList = ["Pincode*"];
  String pincodeDropDownValue = "Pincode*";

  Future<void> pincodeDropDown(
      {required BuildContext context, required String city}) async {
    try {
      pincodeList = [];

      pincodeDropDownValue = "Pincode*";
      loadingPincodeDropDown = true;
      notifyListeners();
      pincodeListModel =
          await CreatorApiServices.pincodeModelDetails(context, city: city);

      if (pincodeListModel != null && pincodeListModel!.code == 100) {
        List? dataList = pincodeListModel!.data;
        if (dataList != null) {
          // cityList.removeRange(0, cityList.length-1);
          for (var item in dataList) {
            pincodeList.add(item ?? "");

            loadingPincodeDropDown = false;
            notifyListeners();
          }
        }
        pincodeList.insert(0, "Pincode*");

        notifyListeners();
      }
    } catch (e) {
      log("pincode drop down error: ${e.toString()}");
    }
    notifyListeners();
  }

  bool loadingCheckAvailability = false;
  CheckAddressAvailabilityModel? checkAddressAvailabilityModel;

  Future<void> checkAddressAvailability(
      {required BuildContext context,
      required String addressid,
      required double height}) async {
    try {
      loadingCheckAvailability = true;
      notifyListeners();
      checkAddressAvailabilityModel =
          await CreatorApiServices.checkAddressAvailabilityDetails(
              context: context, addressid: addressid);

      if (checkAddressAvailabilityModel != null &&
          checkAddressAvailabilityModel!.code == 100) {
        loadingCheckAvailability = false;
        // updateCartAddress(
        //     context: context, height: height, addressId: addressid);
        Utils.commonSnackBar(context,
            height: height,
            message: checkAddressAvailabilityModel!.msg.toString());
        notifyListeners();
      } else {
        loadingCheckAvailability = false;
        Utils.commonSnackBar(context,
            height: height,
            message: checkAddressAvailabilityModel!.msg.toString());
      }
    } catch (e) {
      log("check availability drop down error: ${e.toString()}");
    }
    notifyListeners();
  }

  bool loadingUserCart = true;
  UserCartModel? userCartModel;

  Future<void> userCart({
    required BuildContext context,
  }) async {
    try {
      loadingUserCart = true;
      notifyListeners();

      userCartModel = await CreatorApiServices.userCartDetails(context);
      notifyListeners();

      if (userCartModel != null && userCartModel!.code == 100) {
        loadingUserCart = false;

        notifyListeners();
      } else {
        loadingUserCart = true;

        notifyListeners();
      }
    } catch (e) {
      log("user cart error: ${e.toString()}");
    }
    notifyListeners();
  }

  bool loadingDeleteCart = false;

  Future<void> deleteCart(
      {required BuildContext context,
      required BuildContext globalcontext,
      required double height,
      required String cartid}) async {
    try {
      loadingDeleteCart = true;
      notifyListeners();

      DeleteCartModel? deleteCartModel =
          await CreatorApiServices.deleteCartDetails(context, cartid: cartid);
      notifyListeners();

      if (deleteCartModel != null && deleteCartModel.code == 100) {
        userCart(context: context);
        loadingDeleteCart = false;

        Utils.commonSnackBar(globalcontext,
            height: height, message: deleteCartModel.msg.toString());

        notifyListeners();
      } else {
        loadingDeleteCart = true;
        notifyListeners();
      }
    } catch (e) {
      log("delete cart error: ${e.toString()}");
    }
    notifyListeners();
  }

  bool loadingDeleteCartAttribute = false;

  Future<void> deleteCartAttribute(
      {required BuildContext context,
      required BuildContext globalcontext,
      required double height,
      required String cartattributeid}) async {
    try {
      loadingDeleteCartAttribute = true;
      notifyListeners();

      DeleteCartAttributeModel? deleteCartAttributeModel =
          await CreatorApiServices.deleteCartAttributeDetails(context,
              cartattributeid: cartattributeid);
      notifyListeners();

      if (deleteCartAttributeModel != null &&
          deleteCartAttributeModel.code == 100) {
        userCart(context: context);
        loadingDeleteCartAttribute = false;

        Utils.commonSnackBar(globalcontext,
            height: height, message: deleteCartAttributeModel.msg.toString());

        notifyListeners();
      } else {
        loadingDeleteCartAttribute = true;
        notifyListeners();
      }
    } catch (e) {
      log("delete cart attribute error: ${e.toString()}");
    }
    notifyListeners();
  }

  bool loadingEmptyCart = false;

  Future<void> emptyCart({
    required BuildContext context,
    required BuildContext globalcontext,
    required double height,
  }) async {
    try {
      loadingEmptyCart = true;
      notifyListeners();

      EmptyCartModel? emptyCartModel =
          await CreatorApiServices.emptyCartDetails(
        context,
      );
      notifyListeners();

      if (emptyCartModel != null && emptyCartModel.code == 100) {
        userCart(context: context);
        notifyListeners();
        loadingEmptyCart = false;

        notifyListeners();
        Utils.commonSnackBar(globalcontext,
            height: height, message: emptyCartModel.msg.toString());

        notifyListeners();
      } else {
        loadingEmptyCart = true;
        notifyListeners();
      }
    } catch (e) {
      log("empty cart error: ${e.toString()}");
    }
    notifyListeners();
  }

  RelatedProductModel? relatedProductModel;
  bool loadingRelatedProduct = true;
  Future<void> relatedProduct({
    required BuildContext context,
  }) async {
    try {
      loadingRelatedProduct = true;
      notifyListeners();

      relatedProductModel =
          await CreatorApiServices.relatedProductDetails(context);
      notifyListeners();

      if (relatedProductModel != null && relatedProductModel!.code == 100) {
        loadingRelatedProduct = false;

        notifyListeners();
      } else {
        loadingRelatedProduct = true;
        notifyListeners();
      }
    } catch (e) {
      log("related product error: ${e.toString()}");
    }
    notifyListeners();
  }

  // void increaseQty(int tqy) {
  //   qty = ++tqy;
  //   log(qty.toString());
  //   notifyListeners();
  // }

  bool loadingIncreaseCartQuantity = false;
  Future<void> increaseCartQuantity(
      {required BuildContext context,
      required double height,
      required String cartid,
      required String qty}) async {
    try {
      int finalqty = int.parse(qty);
      int lastqty = ++finalqty;
      loadingIncreaseCartQuantity = true;
      notifyListeners();

      IncreaseCartQtyModel? increaseCartQtyModel =
          await CreatorApiServices.increaseCartQuantityDetails(context,
              cartattributeid: cartid, qty: lastqty.toString());
      notifyListeners();

      if (increaseCartQtyModel != null && increaseCartQtyModel.code == 100) {
        await userCart(context: context);

        notifyListeners();
        loadingIncreaseCartQuantity = false;

        notifyListeners();
      } else {
        loadingIncreaseCartQuantity = true;
        notifyListeners();
      }
    } catch (e) {
      log("increase cart quantity error: ${e.toString()}");
    }
    notifyListeners();
  }

  Future<void> decreaseCartQuantity(
      {required BuildContext context,
      required double height,
      required String cartid,
      required String qty}) async {
    try {
      int finalqty = int.parse(qty);
      int lastqty = --finalqty;
      loadingIncreaseCartQuantity = true;
      notifyListeners();

      IncreaseCartQtyModel? increaseCartQtyModel =
          await CreatorApiServices.increaseCartQuantityDetails(context,
              cartattributeid: cartid, qty: lastqty.toString());
      notifyListeners();

      if (increaseCartQtyModel != null && increaseCartQtyModel.code == 100) {
        await userCart(context: context);

        notifyListeners();
        loadingIncreaseCartQuantity = false;

        notifyListeners();
      } else {
        loadingIncreaseCartQuantity = true;
        notifyListeners();
      }
    } catch (e) {
      log("increase cart quantity error: ${e.toString()}");
    }
    notifyListeners();
  }

  String selectedPayment = "PayU Money";
  List paymentList = [
    "PayU Money",
  ];
  void selectPayment(value, {required BuildContext context}) {
    selectedPayment = value;

    notifyListeners();
  }

  bool loadingorder = false;
  OrderModel? orderModel;
  late PayUCheckoutProFlutter checkoutPro;
  Future<void> order({
    required BuildContext context,
    required double height,
    required String addressId,
  }) async {
    try {
      if (addressId.isNotEmpty) {
        loadingorder = true;
        orderModel = await CreatorApiServices.orderDetails(
          context,
          addressid: addressId,
        );
        notifyListeners();
        if (orderModel != null && orderModel!.code == 100) {
          loadingorder = false;
          Utils.commonSnackBar(context,
              height: height, message: orderModel!.msg.toString());
          // Navigator.push(context, MaterialPageRoute(
          //   builder: (context) {
          //     return PaymentSuccessfulScreen();
          //   },
          // ));
          notifyListeners();
        } else {
          loadingorder = false;
          // if (orderModel == null ||
          //     orderModel.data == null) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
                content: Text(
                    orderModel?.msg.toString() ?? "Order creation failed")),
          );
          //   return;
          // }
          notifyListeners();
        }
      } else {
        Utils.commonSnackBar(context,
            height: height, message: "Select payment");
      }
    } catch (e) {
      log("order error: ${e.toString()}");
    }
    notifyListeners();
  }
}
