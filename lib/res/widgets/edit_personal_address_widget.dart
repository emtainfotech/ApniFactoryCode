import 'package:apni_factory/provider/homeScreen_provider.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/res/widgets/round_button.dart';
import 'package:apni_factory/utils.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class EditPersonalAddressWidget extends StatefulWidget {
  final String addressId;
  const EditPersonalAddressWidget({super.key, required this.addressId});

  @override
  State<EditPersonalAddressWidget> createState() =>
      _EditPersonalAddressWidgetState();
}

class _EditPersonalAddressWidgetState extends State<EditPersonalAddressWidget> {
  @override
  Widget build(BuildContext dialogContext) {
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Consumer<HomeScreenProvider>(
      builder: (context, provider, child) {
        return Container(
          height: height / 1.5,
          child: Padding(
            padding: EdgeInsets.symmetric(horizontal: width / 16.2),
            child: ListView(
              children: [
                SizedBox(
                  height: height / 21,
                ),
                Row(
                  children: [
                    Text(
                      "Edit Address",
                      style: AppTextStyle.poppinsMedium20(context: context),
                    ),
                    Spacer(),
                    InkWell(
                        onTap: () {
                          Utils.commonShowDialog(
                            context: context,
                            height: height,
                            title:
                                "Are you sure you want to remove this address?",
                            cancelButton: () {
                              Navigator.pop(context);
                            },
                            okayButton: () {
                              provider.deleteAddress(
                                  context: context,
                                  dialogContext: dialogContext,
                                  height: height,
                                  addressid: widget.addressId);
                            },
                          );
                        },
                        child: Icon(Icons.delete))
                  ],
                ),
                SizedBox(
                  height: height / 42,
                ),
                Utils.commonTextFormField(context,
                    height: height,
                    width: width,
                    labelText: "Identity Name",
                    controller: provider.editIdentitynameController),
                SizedBox(
                  height: height / 42,
                ),
                Utils.commonTextFormField(context,
                    height: height,
                    width: width,
                    labelText: "Name",
                    controller: provider.editNameController),
                SizedBox(
                  height: height / 42,
                ),
                Utils.commonTextFormField(context,
                    height: height,
                    width: width,
                    labelText: "Contact Number",
                    controller: provider.editPhonenoController,
                    keyboardType: TextInputType.phone),
                SizedBox(
                  height: height / 42,
                ),
                Utils.commonTextFormField(context,
                    height: height,
                    width: width,
                    labelText: "Address",
                    controller: provider.editAddressController),
                SizedBox(
                  height: height / 42,
                ),
                Utils.commonTextFormField(context,
                    height: height,
                    width: width,
                    labelText: "Landmark",
                    controller: provider.editLandmarkController),
                SizedBox(
                  height: height / 42,
                ),
                Row(
                  children: [
                    Expanded(
                      child: Utils.commonTextFormField(context,
                          height: height,
                          width: width,
                          labelText: "City",
                          controller: provider.editCityController),
                    ),
                    SizedBox(
                      width: width / 30,
                    ),
                    Expanded(
                      child: Utils.commonTextFormField(context,
                          height: height,
                          width: width,
                          labelText: "State",
                          controller: provider.editStateController),
                    ),
                  ],
                ),
                SizedBox(
                  height: height / 42,
                ),
                Row(
                  children: [
                    Expanded(
                      child: Utils.commonTextFormField(context,
                          height: height,
                          width: width,
                          labelText: "Country",
                          controller: provider.editCountryController),
                    ),
                    SizedBox(
                      width: width / 30,
                    ),
                    Expanded(
                      child: Utils.commonTextFormField(context,
                          height: height,
                          width: width,
                          labelText: "Pin Code",
                          controller: provider.editPincodeController,
                          keyboardType: TextInputType.number),
                    ),
                  ],
                ),
                SizedBox(
                  height: height / 42,
                ),
                RoundButton(
                  title: "Edit",
                  onPressed: () {
                    provider.updateAddress(
                      context: context,
                      height: height,
                      address: provider.editAddressController.text.trim(),
                      city: provider.editCityController.text.trim(),
                      country: provider.editCountryController.text.trim(),
                      identityname:
                          provider.editIdentitynameController.text.trim(),
                      landmark: provider.editLandmarkController.text.trim(),
                      name: provider.editNameController.text.trim(),
                      phoneno: provider.editPhonenoController.text.trim(),
                      pincode: provider.editPincodeController.text.trim(),
                      state: provider.editStateController.text.trim(),
                      addressId: widget.addressId,
                    );
                  },
                ),
                SizedBox(
                  height: height / 2,
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
