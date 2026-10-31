class CompanyScreenModel {
  bool? status;
  int? code;
  String? msg;
  Data? data;

  CompanyScreenModel({this.status, this.code, this.msg, this.data});

  CompanyScreenModel.fromJson(Map<String, dynamic> json) {
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
  List<Company>? company;
  List<String>? slider;
  List<String>? advertisement;
  String? categoryname;

  Data({this.company, this.slider, this.advertisement, this.categoryname});

  Data.fromJson(Map<String, dynamic> json) {
    if (json['company'] != null) {
      company = <Company>[];
      json['company'].forEach((v) {
        company!.add(new Company.fromJson(v));
      });
    }
    slider = json['slider'].cast<String>();
    advertisement = json['advertisement'].cast<String>();
    categoryname = json['categoryname'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    if (this.company != null) {
      data['company'] = this.company!.map((v) => v.toJson()).toList();
    }
    data['slider'] = this.slider;
    data['advertisement'] = this.advertisement;
    data['categoryname'] = this.categoryname;
    return data;
  }
}

class Company {
  int? id;
  String? name;
  int? status;
  String? createdAt;
  String? updatedAt;
  int? userId;
  String? email;
  String? mobile;
  int? maincategoryId;
  String? gst;
  String? crn;
  int? minordervalue;
  String? photo;
  String? city;
  String? state;
  int? pincode;
  int? comission;
  String? restrictedCity;

  Company(
      {this.id,
      this.name,
      this.status,
      this.createdAt,
      this.updatedAt,
      this.userId,
      this.email,
      this.mobile,
      this.maincategoryId,
      this.gst,
      this.crn,
      this.minordervalue,
      this.photo,
      this.city,
      this.state,
      this.pincode,
      this.comission,
      this.restrictedCity});

  Company.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    status = json['status'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    userId = json['user_id'];
    email = json['email'];
    mobile = json['mobile'];
    maincategoryId = json['maincategory_id'];
    gst = json['gst'];
    crn = json['crn'];
    minordervalue = json['minordervalue'];
    photo = json['photo'];
    city = json['city'];
    state = json['state'];
    pincode = json['pincode'];
    comission = json['comission'];
    restrictedCity = json['restricted_city'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['name'] = this.name;
    data['status'] = this.status;
    data['created_at'] = this.createdAt;
    data['updated_at'] = this.updatedAt;
    data['user_id'] = this.userId;
    data['email'] = this.email;
    data['mobile'] = this.mobile;
    data['maincategory_id'] = this.maincategoryId;
    data['gst'] = this.gst;
    data['crn'] = this.crn;
    data['minordervalue'] = this.minordervalue;
    data['photo'] = this.photo;
    data['city'] = this.city;
    data['state'] = this.state;
    data['pincode'] = this.pincode;
    data['comission'] = this.comission;
    data['restricted_city'] = this.restrictedCity;
    return data;
  }
}