class BrandScreenModel {
  bool? status;
  int? code;
  String? msg;
  Data? data;

  BrandScreenModel({this.status, this.code, this.msg, this.data});

  BrandScreenModel.fromJson(Map<String, dynamic> json) {
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
  List<Brand>? brand;
  String? bannerimage;
  String? companyname;

  Data({this.brand, this.bannerimage, this.companyname});

  Data.fromJson(Map<String, dynamic> json) {
    if (json['Brand'] != null) {
      brand = <Brand>[];
      json['Brand'].forEach((v) {
        brand!.add(new Brand.fromJson(v));
      });
    }
    bannerimage = json['bannerimage'];
    companyname = json['companyname'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    if (this.brand != null) {
      data['Brand'] = this.brand!.map((v) => v.toJson()).toList();
    }
    data['bannerimage'] = this.bannerimage;
    data['companyname'] = this.companyname;
    return data;
  }
}

class Brand {
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

  Brand(
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
      this.adminresponse});

  Brand.fromJson(Map<String, dynamic> json) {
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
    return data;
  }
}