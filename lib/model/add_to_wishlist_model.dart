class AddToWishlistModel {
  bool? status;
  int? code;
  String? msg;
  int? counter;
  List<Data>? data;

  AddToWishlistModel(
      {this.status, this.code, this.msg, this.counter, this.data});

  AddToWishlistModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    code = json['code'];
    msg = json['msg'];
    counter = json['counter'];
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
    data['counter'] = this.counter;
    if (this.data != null) {
      data['data'] = this.data!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class Data {
  int? id;
  int? userid;
  int? productid;
  String? productname;
  String? productimage;

  Data(
      {this.id,
      this.userid,
      this.productid,
      this.productname,
      this.productimage});

  Data.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    userid = json['userid'];
    productid = json['productid'];
    productname = json['productname'];
    productimage = json['productimage'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['userid'] = this.userid;
    data['productid'] = this.productid;
    data['productname'] = this.productname;
    data['productimage'] = this.productimage;
    return data;
  }
}