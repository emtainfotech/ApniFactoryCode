class GetProductListShadeCardModel {
  bool? status;
  int? code;
  String? msg;
  List<List<Data>>? data;

  GetProductListShadeCardModel({this.status, this.code, this.msg, this.data});

  GetProductListShadeCardModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    code = json['code'];
    msg = json['msg'];
    if (json['data'] != null) {
      data = <List<Data>>[];
      json['data'].forEach((v) {
        List<Data> dataList = <Data>[];
        v.forEach((item) {
          dataList.add(Data.fromJson(item));
        });
        data!.add(dataList);
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['status'] = this.status;
    data['code'] = this.code;
    data['msg'] = this.msg;
    if (this.data != null) {
      data['data'] = this.data!.map((dataList) => dataList.map((v) => v.toJson()).toList()).toList();
    }
    return data;
  }
}

class Data {
  int? id;
  int? productId;
  int? mid;
  int? cid;
  int? sid;
  String? name;
  String? slug;
  String? title;
  String? image;
  String? capacity;
  String? description;
  List? multipleimages;
  String? status;
  String? createdAt;
  String? updatedAt;
  int? brand;

  Data(
      {this.id,
      this.productId,
      this.mid,
      this.cid,
      this.sid,
      this.name,
      this.slug,
      this.title,
      this.image,
      this.capacity,
      this.description,
      this.multipleimages,
      this.status,
      this.createdAt,
      this.updatedAt,
      this.brand});

  Data.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    productId = json['product_id'];
    mid = json['mid'];
    cid = json['cid'];
    sid = json['sid'];
    name = json['name'];
    slug = json['slug'];
    title = json['title'];
    image = json['image'];
    capacity = json['capacity'];
    description = json['description'];
    multipleimages = json['multipleimages'];
    status = json['status'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    brand = json['Brand'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['product_id'] = this.productId;
    data['mid'] = this.mid;
    data['cid'] = this.cid;
    data['sid'] = this.sid;
    data['name'] = this.name;
    data['slug'] = this.slug;
    data['title'] = this.title;
    data['image'] = this.image;
    data['capacity'] = this.capacity;
    data['description'] = this.description;
    data['multipleimages'] = this.multipleimages;
    data['status'] = this.status;
    data['created_at'] = this.createdAt;
    data['updated_at'] = this.updatedAt;
    data['Brand'] = this.brand;
    return data;
  }
}