class AddAddressModel {
  bool? status;
  int? code;
  String? msg;
  Data? data;

  AddAddressModel({this.status, this.code, this.msg, this.data});

  AddAddressModel.fromJson(Map<String, dynamic> json) {
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
  int? id;
  int? customerId;
  String? landmark1;
  String? landmark2;
  String? city;
  String? state;
  String? pincode;
  String? country;
  String? phoneno;
  String? location;
  String? name;
  String? identityname;
  String? createdAt;
  String? updatedAt;
  String? type;

  Data(
      {this.id,
      this.customerId,
      this.landmark1,
      this.landmark2,
      this.city,
      this.state,
      this.pincode,
      this.country,
      this.phoneno,
      this.location,
      this.name,
      this.identityname,
      this.createdAt,
      this.updatedAt,
      this.type});

  Data.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    customerId = json['customer_id'];
    landmark1 = json['landmark1'];
    landmark2 = json['landmark2'];
    city = json['city'];
    state = json['state'];
    pincode = json['pincode'];
    country = json['country'];
    phoneno = json['phoneno'];
    location = json['location'];
    name = json['name'];
    identityname = json['identityname'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    type = json['type'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['customer_id'] = this.customerId;
    data['landmark1'] = this.landmark1;
    data['landmark2'] = this.landmark2;
    data['city'] = this.city;
    data['state'] = this.state;
    data['pincode'] = this.pincode;
    data['country'] = this.country;
    data['phoneno'] = this.phoneno;
    data['location'] = this.location;
    data['name'] = this.name;
    data['identityname'] = this.identityname;
    data['created_at'] = this.createdAt;
    data['updated_at'] = this.updatedAt;
    data['type'] = this.type;
    return data;
  }
}