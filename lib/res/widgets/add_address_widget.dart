import 'package:apni_factory/provider/cart_screen_provider.dart';
import 'package:apni_factory/res/colors.dart';
import 'package:apni_factory/res/textstyle.dart';
import 'package:apni_factory/res/widgets/round_button.dart';
import 'package:apni_factory/utils.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

class AddAddressWidget extends StatefulWidget {
  final CartScreenProvider provider;
  const AddAddressWidget({super.key, required this.provider});

  @override
  State<AddAddressWidget> createState() => _AddAddressWidgetState();
}

class _AddAddressWidgetState extends State<AddAddressWidget> {
  @override
  Widget build(BuildContext context) {
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
              "Enter Complete Delivery Address",
              style: AppTextStyle.poppinsMedium15(context: context,color: AppColors.secondaryColor),
            ),
            
            SizedBox(
              height: height / 42,
            ),
             Utils.commonTextFormField(context,
                height: height,
                width: width,
                labelText: "Party Shop Name",
                controller: widget.provider.identitynameController),
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
                labelText: "Landmark",
                controller: widget.provider.landmarkController),
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
                labelText: "Transport Name",
                controller: widget.provider.nameController),
            SizedBox(
              height: height / 42,
            ),
            Utils.commonTextFormField(context,
                height: height,
                width: width,
                labelText: "Transport Number",
                controller: widget.provider.phonenoController,
                keyboardType: TextInputType.phone),
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
                widget.provider.addAddress(
                  context: context,
                  height: height,
                  landmark: widget.provider.landmarkController.text.trim(),
                  city: widget.provider.cityDropDownValue != "City*"
                      ? widget.provider.cityDropDownValue
                      : null,
                  country: widget.provider.countryController.text.trim(),
                  identityname:
                      widget.provider.identitynameController.text.trim(),
                  address: widget.provider.addressController.text.trim(),
                
                  name: widget.provider.nameController.text.trim(),
                  phoneno: widget.provider.phonenoController.text.trim(),
                  pincode: widget.provider.pincodeDropDownValue != "Pincode*"
                      ? widget.provider.pincodeDropDownValue
                      : null,
                  state: widget.provider.stateDropDownValue != "State*"
                      ? widget.provider.stateDropDownValue
                      : null,
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
  }
}
