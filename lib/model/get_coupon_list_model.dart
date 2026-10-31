class GetCouponListModel {
  bool? status;
  int? code;
  String? msg;
  List<Data>? data;

  GetCouponListModel({this.status, this.code, this.msg, this.data});

  GetCouponListModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    code = json['code'];
    msg = json['msg'];
    if (json['data'] != null) {
      data = <Data>[];
      json['data'].forEach((v) {
        data!.add(new Data.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['status'] = this.status;
    data['code'] = this.code;
    data['msg'] = this.msg;
    if (this.data != null) {
      data['data'] = this.data!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class Data {
  int? id;
  String? code;
  String? type;
  int? price;
  String? description;
  String? startdate;
  String? expiry;
  int? status;
  String? name;
  String? title;
  String? createdAt;
  String? updatedAt;
  String? image;
  String? couponon;
  int? userId;
  String? couponapplyon;

  Data(
      {this.id,
      this.code,
      this.type,
      this.price,
      this.description,
      this.startdate,
      this.expiry,
      this.status,
      this.name,
      this.title,
      this.createdAt,
      this.updatedAt,
      this.image,
      this.couponon,
      this.userId,
      this.couponapplyon});

  Data.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    code = json['code'];
    type = json['type'];
    price = json['price'];
    description = json['description'];
    startdate = json['startdate'];
    expiry = json['expiry'];
    status = json['status'];
    name = json['name'];
    title = json['title'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    image = json['image'];
    couponon = json['couponon'];
    userId = json['user_id'];
    couponapplyon = json['couponapplyon'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['code'] = this.code;
    data['type'] = this.type;
    data['price'] = this.price;
    data['description'] = this.description;
    data['startdate'] = this.startdate;
    data['expiry'] = this.expiry;
    data['status'] = this.status;
    data['name'] = this.name;
    data['title'] = this.title;
    data['created_at'] = this.createdAt;
    data['updated_at'] = this.updatedAt;
    data['image'] = this.image;
    data['couponon'] = this.couponon;
    data['user_id'] = this.userId;
    data['couponapplyon'] = this.couponapplyon;
    return data;
  }
}