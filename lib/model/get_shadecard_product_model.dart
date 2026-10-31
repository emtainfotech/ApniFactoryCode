class GetShadeCardProductModel {
  bool? status;
  int? code;
  String? msg;
  List<Data>? data;

  GetShadeCardProductModel({this.status, this.code, this.msg, this.data});

  GetShadeCardProductModel.fromJson(Map<String, dynamic> json) {
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
  int? shadeid;
  String? image;
  String? name;
  int? brandid;

  Data({this.shadeid, this.image, this.name, this.brandid});

  Data.fromJson(Map<String, dynamic> json) {
    shadeid = json['shadeid'];
    image = json['image'];
    name = json['name'];
    brandid = json['brandid'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['shadeid'] = this.shadeid;
    data['image'] = this.image;
    data['name'] = this.name;
    data['brandid'] = this.brandid;
    return data;
  }
}