class BrandDetailModel {
  bool? status;
  int? code;
  String? msg;
  Data? data;

  BrandDetailModel({this.status, this.code, this.msg, this.data});

  BrandDetailModel.fromJson(Map<String, dynamic> json) {
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
  int? companyId;
  String? name;
  int? status;
  String? createdAt;
  String? updatedAt;
  int? userId;
  int? mid;
  int? categoryId;
  String? image;
  String? trademarkno;
  Null? file;
  String? type;
  String? adminresponse;
  Company? company;

  Data(
      {this.id,
      this.companyId,
      this.name,
      this.status,
      this.createdAt,
      this.updatedAt,
      this.userId,
      this.mid,
      this.categoryId,
      this.image,
      this.trademarkno,
      this.file,
      this.type,
      this.adminresponse,
      this.company});

  Data.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    companyId = json['company_id'];
    name = json['name'];
    status = json['status'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    userId = json['user_id'];
    mid = json['mid'];
    categoryId = json['category_id'];
    image = json['image'];
    trademarkno = json['trademarkno'];
    file = json['file'];
    type = json['type'];
    adminresponse = json['adminresponse'];
    company =
        json['Company'] != null ? new Company.fromJson(json['Company']) : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['company_id'] = this.companyId;
    data['name'] = this.name;
    data['status'] = this.status;
    data['created_at'] = this.createdAt;
    data['updated_at'] = this.updatedAt;
    data['user_id'] = this.userId;
    data['mid'] = this.mid;
    data['category_id'] = this.categoryId;
    data['image'] = this.image;
    data['trademarkno'] = this.trademarkno;
    data['file'] = this.file;
    data['type'] = this.type;
    data['adminresponse'] = this.adminresponse;
    if (this.company != null) {
      data['Company'] = this.company!.toJson();
    }
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