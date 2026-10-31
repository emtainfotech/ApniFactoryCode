import 'dart:developer';
import 'package:apni_factory/api_services/creator_api_services.dart';
import 'package:apni_factory/model/add_address_model.dart';
import 'package:apni_factory/model/admin_app_pages_model.dart';
import 'package:apni_factory/model/city_list_model.dart';
import 'package:apni_factory/model/delete_address_model.dart';
import 'package:apni_factory/model/get_address_detail_model.dart';
import 'package:apni_factory/model/get_address_list_model.dart';
import 'package:apni_factory/model/get_pages_model.dart';
import 'package:apni_factory/model/home_screen_model.dart';
import 'package:apni_factory/model/notification_model.dart';
import 'package:apni_factory/model/pincode_list_model.dart';
import 'package:apni_factory/model/search_model.dart';
import 'package:apni_factory/model/state_model.dart';
import 'package:apni_factory/model/update_address_model.dart';
import 'package:apni_factory/res/widgets/edit_address_widget.dart';
import 'package:apni_factory/res/widgets/edit_personal_address_widget.dart';
import 'package:apni_factory/screens/search_screen.dart';
import 'package:apni_factory/shared_pref.dart';
import 'package:apni_factory/utils.dart';
import 'package:carousel_slider/carousel_controller.dart';
import 'package:flutter/material.dart';

class HomeScreenProvider extends ChangeNotifier {
  bool loadingHomeScreen = true;

  HomescreenModel? homeScreenModel;
  List? slider;
  List? advertisement;

  int current = 0;
  int slidercurrent = 0;
  final GlobalKey<ScaffoldState> key = GlobalKey();
  final CarouselSliderController carousalController =
      CarouselSliderController();

  String? username;
  String? userEmail;
  Future<void> homeScreen({
    required BuildContext context,
  }) async {
    try {
      loadingHomeScreen = true;
      notifyListeners();
      homeScreenModel = await CreatorApiServices.homeScreenDetails(context);

      if (homeScreenModel != null && homeScreenModel!.code == 100) {
        slider = homeScreenModel?.data?.slider;
        advertisement = homeScreenModel?.data?.advertisement;
        username = await SharedPref.getUserName();
        userEmail = await SharedPref.getUserEmailId();

        loadingHomeScreen = false;

        notifyListeners();
      } else {
        loadingHomeScreen = true;
        notifyListeners();
      }
    } catch (e) {
      log("homescreen error: ${e.toString()}");
    }
    notifyListeners();
  }

  Future<void> refreshHomeScreen(BuildContext context) async {
    loadingHomeScreen = true;
    notifyListeners();

    await homeScreen(context: context);

    loadingHomeScreen = false;
    notifyListeners();
  }

  bool loadingNotification = true;

  NotificationModel? notificationModel;

  Future<void> notification({
    required BuildContext context,
  }) async {
    try {
      loadingNotification = true;
      notifyListeners();
      notificationModel = await CreatorApiServices.notificationDetails(context);

      if (notificationModel != null && notificationModel!.code == 100) {
        loadingNotification = false;

        notifyListeners();
      } else {
        loadingNotification = true;
        notifyListeners();
      }
    } catch (e) {
      log("notification error: ${e.toString()}");
    }
    notifyListeners();
  }

  bool loadingAdminAppPages = true;

  AdminAppPagesModel? adminAppPagesModel;

  Future<void> adminAppPages({
    required BuildContext context,
  }) async {
    try {
      loadingAdminAppPages = true;
      notifyListeners();
      adminAppPagesModel =
          await CreatorApiServices.adminAppPagesDetails(context);

      if (adminAppPagesModel != null && adminAppPagesModel!.code == 100) {
        loadingAdminAppPages = false;

        notifyListeners();
      } else {
        loadingAdminAppPages = true;
        notifyListeners();
      }
    } catch (e) {
      log("admin app pages error: ${e.toString()}");
    }
    notifyListeners();
  }

  bool loadingGetPages = true;
  GetPagesModel? getPagesModel;
  Future<void> getPage(
      {required BuildContext context, required String slug}) async {
    try {
      loadingGetPages = true;
      getPagesModel =
          await CreatorApiServices.getPagesDetails(context, slug: slug);
      notifyListeners();
      if (getPagesModel != null && getPagesModel!.code == 100) {
        loadingGetPages = false;

        notifyListeners();
      } else {
        loadingGetPages = true;
        notifyListeners();
      }
    } catch (e) {
      log("get pages error: ${e.toString()}");
    }
    notifyListeners();
  }

  SearchModel? searchModel;
  bool loadingSearch = false;

  Future<void> search(
    double height, {
    required BuildContext context,
    String? word,
  }) async {
    try {
      if (word != null && word.trim().isNotEmpty) {
        loadingSearch = true;
        notifyListeners();

        searchModel = await CreatorApiServices.searchDetails(
          context,
          word: word.trim(),
        );

        if (searchModel != null && searchModel!.code == 100) {
          loadingSearch = false;

          notifyListeners();
        } else {
          loadingSearch = false;
          Utils.commonSnackBar(context,
              height: height, message: "Something Went Wrong");
          notifyListeners();
        }
      } else {
        searchModel = null;
        // Empty input: clear search and show home content
        loadingSearch = false;

        notifyListeners();
      }
    } catch (e) {
      log("search error: ${e.toString()}");
    }
    notifyListeners();
  }

  void onSearchChanged(
    String value,
    double height,
    BuildContext context,
  ) {
    if (value.trim().isEmpty) {
      searchModel = null;
      loadingSearch = false;
      notifyListeners();
    } else {
      search(height, context: context, word: value);
    }
  }

  final TextEditingController searchController = TextEditingController();
  void clearSearch() {
    searchController.clear();
    searchModel = null;
    loadingSearch = false;
    notifyListeners();
  }

  //   void clearSearch() {
  //   controller.clear();
  //   searchModel = null;
  //   notifyListeners();
  // }

  void resetAndReload(BuildContext context) async {
    homeScreenModel = null; // Reset the data
    loadingHomeScreen = true; // Set loading to true
    Navigator.pop(context);
    notifyListeners();
    await homeScreen(context: context); // data
  }

  bool loadingAddAddress = false;
  TextEditingController nameController = TextEditingController();
  TextEditingController addressController = TextEditingController();
  TextEditingController landmark1Controller = TextEditingController();
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

  Future<void> addAddress({
    required BuildContext context,
    required double height,
    String? name,
    String? landmark1,
    String? landmark2,
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
          landmark1 != null &&
          landmark1.isNotEmpty &&
          landmark2 != null &&
          landmark2.isNotEmpty &&
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
                landmark1: landmark1,
                landmark2: landmark2,
                country: country,
                state: state,
                city: city,
                pincode: pincode,
                phoneno: phoneno,
                identityname: identityname,
                type: "personal");

        if (addAddressModel != null && addAddressModel.code == 100) {
          // await checkAddressAvailability(
          //     context: context,
          //     addressid: addAddressModel.data?.id.toString() ?? "",
          //     height: height);

          // Clear the address list before getting new list
          // addressList?.clear();

          // Get updated address list
          await getAddressList(context: context);
          selectedAddressId = addAddressModel.data?.id?.toString();
          selectedAddressText = "${addAddressModel.data?.landmark1 ?? ""}, "
              "${addAddressModel.data?.landmark2 ?? ""}, "
              "${addAddressModel.data?.city ?? ""}, "
              "${addAddressModel.data?.state ?? ""}, "
              "${addAddressModel.data?.pincode ?? ""}";
          // Set the newly added address as selected
          // String newAddress =
          //     "${(addAddressModel.data?.landmark1 ?? "")}, ${(addAddressModel.data?.landmark2 ?? "")}, ${(addAddressModel.data?.city ?? "")}, ${(addAddressModel.data?.state ?? "")}, ${(addAddressModel.data?.pincode ?? "")}, ${(addAddressModel.data?.id.toString() ?? "")}";
          // selectedAddress = newAddress;
          // var addressParts = newAddress.split(" ");
          // selectedId = addressParts.last;

          // Clear form fields
          nameController.clear();
          addressController.clear();
          landmark1Controller.clear();
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
        // Clear existing address list
        // addressList?.clear();

        // if (getAddressListModel!.data != null) {
        //   for (var address in getAddressListModel!.data!) {
        //     String addressString =
        //         "${(address.landmark1 ?? "")}, ${(address.landmark2 ?? "")}, ${(address.city ?? "")}, ${(address.state ?? "")}, ${(address.pincode ?? "")}, ${(address.id ?? "")}";
        //     addressList?.add(addressString);
        //   }
        // }

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
  String? selectedAddressText;
  void selectAddress(
    value, {
    required BuildContext context,
    required double height,
    required String addressId,
    required String addressText,
  }) {
    selectedAddressId = addressId;
    selectedAddressText = addressText;
    // selectedAddress = value;
    // var addressParts = value.split(" ");
    // selectedId = addressParts.last;
    notifyListeners();
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
            return EditPersonalAddressWidget(addressId: addressId);
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
                type: "personal");
        notifyListeners();
        if (updateAddressModel != null && updateAddressModel.code == 100) {
          // await updateCartAddress(
          //     context: context, height: height, addressId: addressId);
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
        await getAddressList(context: context);
        loadingDeleteAddress = false;
        notifyListeners();
        Utils.commonSnackBar(context,
            height: height, message: deleteAddressModel.msg.toString());
        Navigator.pop(context);
        Navigator.pop(context);

        notifyListeners();
      } else {
        loadingDeleteAddress = false;
        notifyListeners();
      }
    } catch (e) {
      log("delete address error: ${e.toString()}");
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
}
