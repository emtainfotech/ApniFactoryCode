class ColorScreenModel {
  bool? status;
  int? code;
  String? msg;
  List<Data>? data;

  ColorScreenModel({this.status, this.code, this.msg, this.data});

  ColorScreenModel.fromJson(Map<String, dynamic> json) {
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
  int? subcategoryid;
  String? name;
  String? hexcode;
  String? image;
  String? status;
  String? createdAt;
  String? updatedAt;

  Data(
      {this.id,
      this.subcategoryid,
      this.name,
      this.hexcode,
      this.image,
      this.status,
      this.createdAt,
      this.updatedAt});

  Data.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    subcategoryid = json['subcategoryid'];
    name = json['name'];
    hexcode = json['hexcode'];
    image = json['image'];
    status = json['status'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['subcategoryid'] = this.subcategoryid;
    data['name'] = this.name;
    data['hexcode'] = this.hexcode;
    data['image'] = this.image;
    data['status'] = this.status;
    data['created_at'] = this.createdAt;
    data['updated_at'] = this.updatedAt;
    return data;
  }
}