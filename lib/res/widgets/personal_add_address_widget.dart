import 'package:apni_factory/provider/homeScreen_provider.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/res/widgets/round_button.dart';
import 'package:apni_factory/utils.dart';
import 'package:flutter/material.dart';

class PersonalAddAddressWidget extends StatefulWidget {
  final HomeScreenProvider provider;
  const PersonalAddAddressWidget({super.key, required this.provider});

  @override
  State<PersonalAddAddressWidget> createState() =>
      _PersonalAddAddressWidgetState();
}

class _PersonalAddAddressWidgetState extends State<PersonalAddAddressWidget> {
  @override
  Widget build(BuildContext context) {
    final bool addressExists =
    widget.provider.getAddressListModel?.data?.isNotEmpty == true;
    double height = MediaQuery.of(context).size.height;
    double width = MediaQuery.of(context).size.width;
    return Container(
      height: height / 1.5,
      child: Padding(
        padding: EdgeInsets.symmetric(horizontal: width / 16.2),
        child: ListView(
          children: [
            SizedBox(
              height: height / 21,
            ),
            Text(
              "Enter Complete Communication Address",
              style: AppTextStyle.poppinsMedium15(
                  context: context, color: AppColors.secondaryColor),
            ),
            SizedBox(
              height: height / 42,
            ),
            Utils.commonTextFormField(context,
                height: height,
                width: width,
                labelText: "Name",
                controller: widget.provider.nameController),
            SizedBox(
              height: height / 42,
            ),
            Utils.commonTextFormField(context,
                height: height,
                width: width,
                labelText: "Address",
                controller: widget.provider.addressController),
            SizedBox(
              height: height / 42,
            ),
            Utils.commonTextFormField(context,
                height: height,
                width: width,
                labelText: "Landmark 1",
                controller: widget.provider.landmark1Controller),
            SizedBox(
              height: height / 42,
            ),
            Row(
              children: [
                Expanded(
                    child: Utils.commonDropDown(
                  context: context,
                  height: height,
                  width: width,
                  list: widget.provider.stateList,
                  dropDownValue: widget.provider.stateDropDownValue,
                  onChanged: (value) async {
                    widget.provider.stateDropDownValue = value!;
                    await widget.provider.cityDropDown(
                        context: context,
                        state: widget.provider.stateDropDownValue);
                    setState(() {});
                  },
                )),
                SizedBox(
                  width: width / 30,
                ),
                Expanded(
                    child: Utils.commonDropDown(
                        context: context,
                        height: height,
                        width: width,
                        list: widget.provider.cityList,
                        dropDownValue: widget.provider.cityDropDownValue,
                        onChanged: (value) async {
                          widget.provider.cityDropDownValue = value!;
                          await widget.provider.pincodeDropDown(
                              context: context,
                              city: widget.provider.cityDropDownValue);
                          setState(() {});
                        })),
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
                      controller: widget.provider.countryController),
                ),
                SizedBox(
                  width: width / 30,
                ),
                Expanded(
                    child: Utils.commonDropDown(
                        context: context,
                        height: height,
                        width: width,
                        list: widget.provider.pincodeList,
                        dropDownValue: widget.provider.pincodeDropDownValue,
                        onChanged: (value) async {
                          widget.provider.pincodeDropDownValue = value!;

                          setState(() {});
                        })),
              ],
            ),
            SizedBox(
              height: height / 42,
            ),
            Utils.commonTextFormField(context,
                height: height,
                width: width,
                labelText: "Contact Number",
                controller: widget.provider.phonenoController,
                keyboardType: TextInputType.phone),
            SizedBox(
              height: height / 42,
            ),
            Utils.commonTextFormField(context,
                height: height,
                width: width,
                labelText: "Identity Name",
                controller: widget.provider.identitynameController),
            SizedBox(
              height: height / 42,
            ),
            // Utils.commonTextFormField(context,
            //     height: height,
            //     width: width,
            //     labelText: "Location",
            //     // suffixIcon: InkWell(
            //     //     onTap: () async{
            //     //     // await  widget.provider.determinePosition();
            //     //     },
            //     //     child: Icon(
            //     //       Icons.add,
            //     //       color: AppColors.primaryColor,
            //     //     )),
            //     controller: widget.provider.locationController),
            SizedBox(
              height: height / 42,
            ),
            RoundButton(
              title: "Save",
              onPressed: () {
                // if (widget.provider.getAddressListModel?.data != null &&
                //     widget.provider.getAddressListModel!.data!.isNotEmpty) {
                //   Utils.commonSnackBar(
                //     context,
                //     height: height,
                //     message:
                //         "Only one communication address is allowed. Please edit the existing address.",
                //   );
                //   return;
                // }

                /// ✅ Allow adding only if no address exists
                widget.provider.addAddress(
                  context: context,
                  height: height,
                  landmark2: widget.provider.landmark1Controller.text.trim(),
                  city: widget.provider.cityDropDownValue != "City*"
                      ? widget.provider.cityDropDownValue
                      : null,
                  country: widget.provider.countryController.text.trim(),
                  identityname:
                      widget.provider.identitynameController.text.trim(),
                  landmark1: widget.provider.addressController.text.trim(),
                  name: widget.provider.nameController.text.trim(),
                  phoneno: widget.provider.phonenoController.text.trim(),
                  pincode: widget.provider.pincodeDropDownValue != "Pincode*"
                      ? widget.provider.pincodeDropDownValue
                      : null,
                  state: widget.provider.stateDropDownValue != "State*"
                      ? widget.provider.stateDropDownValue
                      : null,
                );
                // widget.provider.addAddress(
                //   context: context,
                //   height: height,
                //   landmark2: widget.provider.landmark1Controller.text.trim(),
                //   city: widget.provider.cityDropDownValue != "City*"
                //       ? widget.provider.cityDropDownValue
                //       : null,
                //   country: widget.provider.countryController.text.trim(),
                //   identityname:
                //       widget.provider.identitynameController.text.trim(),
                //   landmark1: widget.provider.addressController.text.trim(),

                //   name: widget.provider.nameController.text.trim(),
                //   phoneno: widget.provider.phonenoController.text.trim(),
                //   pincode: widget.provider.pincodeDropDownValue != "Pincode*"
                //       ? widget.provider.pincodeDropDownValue
                //       : null,
                //   state: widget.provider.stateDropDownValue != "State*"
                //       ? widget.provider.stateDropDownValue
                //       : null,
                // );
              },
            ),
            SizedBox(
              height: height / 2,
            ),
          ],
        ),
      ),
    );
  }
}
