class UpdateAddressModel {
  bool? status;
  int? code;
  String? msg;
  Data? data;

  UpdateAddressModel({this.status, this.code, this.msg, this.data});

  UpdateAddressModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    code = json['code'];
    msg = json['msg'];
    data = json['data'] != null ? new Data.fromJson(json['data']) : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['status'] = this.status;
    data['code'] = this.code;
    data['msg'] = this.msg;
    if (this.data != null) {
      data['data'] = this.data!.toJson();
    }
    return data;
  }
}

class Data {
  String? customerId;
  String? name;
  String? landmark1;
  String? landmark2;
  String? city;
  String? state;
  String? country;
  String? pincode;
  String? phoneno;
  String? location;
  String? identityname;

  Data(
      {this.customerId,
      this.name,
      this.landmark1,
      this.landmark2,
      this.city,
      this.state,
      this.country,
      this.pincode,
      this.phoneno,
      this.location,
      this.identityname});

  Data.fromJson(Map<String, dynamic> json) {
    customerId = json['customer_id'];
    name = json['name'];
    landmark1 = json['landmark1'];
    landmark2 = json['landmark2'];
    city = json['city'];
    state = json['state'];
    country = json['country'];
    pincode = json['pincode'];
    phoneno = json['phoneno'];
    location = json['location'];
    identityname = json['identityname'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['customer_id'] = this.customerId;
    data['name'] = this.name;
    data['landmark1'] = this.landmark1;
    data['landmark2'] = this.landmark2;
    data['city'] = this.city;
    data['state'] = this.state;
    data['country'] = this.country;
    data['pincode'] = this.pincode;
    data['phoneno'] = this.phoneno;
    data['location'] = this.location;
    data['identityname'] = this.identityname;
    return data;
  }
}