class UserWishlistModel {
  bool? status;
  int? code;
  String? msg;
  int? counter;
  List<Data>? data;

  UserWishlistModel(
      {this.status, this.code, this.msg, this.counter, this.data});

  UserWishlistModel.fromJson(Map<String, dynamic> json) {
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
  String? name;
  String? image;
  int? productid;
  int? brandid;
  String? category;
  String? company;
  String? brand;
  List<Attribute>? attribute;

  Data(
      {this.id,
      this.name,
      this.image,
      this.productid,
      this.brandid,
      this.category,
      this.company,
      this.brand,
      this.attribute});

  Data.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    image = json['image'];
    productid = json['productid'];
    brandid = json['brandid'];
    category = json['category'];
    company = json['company'];
    brand = json['brand'];
    if (json['attribute'] != null) {
      attribute = <Attribute>[];
      json['attribute'].forEach((v) {
        attribute!.add(new Attribute.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['name'] = this.name;
    data['image'] = this.image;
    data['productid'] = this.productid;
    data['brandid'] = this.brandid;
    data['category'] = this.category;
    data['company'] = this.company;
    data['brand'] = this.brand;
    if (this.attribute != null) {
      data['attribute'] = this.attribute!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class Attribute {
  int? id;
  String? productId;
  String? color;
  String? quantity;
  num? oldprice;
  num? price;
  String? createdAt;
  String? updatedAt;

  Attribute(
      {this.id,
      this.productId,
      this.color,
      this.quantity,
      this.oldprice,
      this.price,
      this.createdAt,
      this.updatedAt});

  Attribute.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    productId = json['product_id'];
    color = json['color'];
    quantity = json['quantity'];
    oldprice = json['oldprice'];
    price = json['price'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['product_id'] = this.productId;
    data['color'] = this.color;
    data['quantity'] = this.quantity;
    data['oldprice'] = this.oldprice;
    data['price'] = this.price;
    data['created_at'] = this.createdAt;
    data['updated_at'] = this.updatedAt;
    return data;
  }
}