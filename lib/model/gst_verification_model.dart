class GstVerificationModel {
  bool? status;
  int? code;
  String? msg;
  GstDataWrapper? data;

  GstVerificationModel({this.status, this.code, this.msg, this.data});

  GstVerificationModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    code = json['code'];
    msg = json['msg'];
    data = json['data'] != null
        ? GstDataWrapper.fromJson(json['data'])
        : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = {};
    data['status'] = status;
    data['code'] = code;
    data['msg'] = msg;
    if (this.data != null) {
      data['data'] = this.data!.toJson();
    }
    return data;
  }
}

class GstDataWrapper {
  bool? status;
  String? message;
  GstDetails? data;

  GstDataWrapper({this.status, this.message, this.data});

  GstDataWrapper.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    message = json['message'];
    data =
        json['data'] != null ? GstDetails.fromJson(json['data']) : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = {};
    data['status'] = status;
    data['message'] = message;
    if (this.data != null) {
      data['data'] = this.data!.toJson();
    }
    return data;
  }
}

class GstDetails {
  String? gstin;
  String? legalName;
  String? businessName;
  String? status;
  String? taxpayerType;
  String? registrationDate;
  Address? address;

  GstDetails({
    this.gstin,
    this.legalName,
    this.businessName,
    this.status,
    this.taxpayerType,
    this.registrationDate,
    this.address,
  });

  GstDetails.fromJson(Map<String, dynamic> json) {
    gstin = json['gstin'];
    legalName = json['legal_name'];
    businessName = json['business_name'];
    status = json['status'];
    taxpayerType = json['taxpayer_type'];
    registrationDate = json['registration_date'];
    address =
        json['address'] != null ? Address.fromJson(json['address']) : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = {};
    data['gstin'] = gstin;
    data['legal_name'] = legalName;
    data['business_name'] = businessName;
    data['status'] = status;
    data['taxpayer_type'] = taxpayerType;
    data['registration_date'] = registrationDate;
    if (address != null) {
      data['address'] = address!.toJson();
    }
    return data;
  }
}

class Address {
  String? full;
  String? city;
  String? state;
  String? pincode;

  Address({this.full, this.city, this.state, this.pincode});

  Address.fromJson(Map<String, dynamic> json) {
    full = json['full'];
    city = json['city'];
    state = json['state'];
    pincode = json['pincode'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = {};
    data['full'] = full;
    data['city'] = city;
    data['state'] = state;
    data['pincode'] = pincode;
    return data;
  }
}