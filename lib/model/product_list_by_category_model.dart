class ProductListByCategoryScreenModel {
  bool? status;
  int? code; 
  String? msg;
  Data? data;    

  ProductListByCategoryScreenModel(
      {this.status, this.code, this.msg, this.data});

  ProductListByCategoryScreenModel.fromJson(Map<String, dynamic> json) {
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
  List<Product>? product;
  List<Subcategory>? subcategory;
  String? bannerimage;
  String? brandname;

  Data({this.product, this.subcategory, this.bannerimage, this.brandname});

  Data.fromJson(Map<String, dynamic> json) {
    if (json['product'] != null) {
      product = <Product>[];
      json['product'].forEach((v) {
        product!.add(new Product.fromJson(v));
      });
    }
    if (json['subcategory'] != null) {
      subcategory = <Subcategory>[];
      json['subcategory'].forEach((v) { 
        subcategory!.add(new Subcategory.fromJson(v));
      });
    }
    bannerimage = json['bannerimage'];
    brandname = json['brandname'];
  } 
  
  Map<String, dynamic> toJson() { 
    final Map<String, dynamic> data = new Map<String, dynamic>();
    if (this.product != null) {
      data['product'] = this.product!.map((v) => v.toJson()).toList();
    }
    if (this.subcategory != null) {
      data['subcategory'] = this.subcategory!.map((v) => v.toJson()).toList();
    }
    data['bannerimage'] = this.bannerimage;
    data['brandname'] = this.brandname;
    return data;
  }
}

class Product {
  String? name;
  String? image;
  num? price;
  int? cid;
  String? categoryname;
  int? pid;
  String? color;
  String? hexcode;
  String? hexcodeImage;
  String? colorid;

  Product(
      {this.name,
      this.image,
      this.price,
      this.cid,
      this.categoryname,
      this.pid,
      this.color,
      this.hexcode,
      this.hexcodeImage,
      this.colorid});

  Product.fromJson(Map<String, dynamic> json) {
    name = json['name'];
    image = json['image'];
    price = json['price'];
    cid = json['cid'];
    categoryname = json['categoryname'];
    pid = json['pid'];
    color = json['color'];
    hexcode = json['hexcode'];
    hexcodeImage = json['hexcode_image'];
    colorid = json['colorid'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['name'] = this.name;
    data['image'] = this.image;
    data['price'] = this.price;
    data['cid'] = this.cid;
    data['categoryname'] = this.categoryname;
    data['pid'] = this.pid;
    data['color'] = this.color;
    data['hexcode'] = this.hexcode;
    data['hexcode_image'] = this.hexcodeImage;
    data['colorid'] = this.colorid;
    return data;
  }
}

class Subcategory {
  bool? highlight;
  String? name;
  String? image;
  int? cid;
  int? bid;

  Subcategory({this.highlight, this.name, this.image, this.cid, this.bid});

  Subcategory.fromJson(Map<String, dynamic> json) {
    highlight = json['highlight'];
    name = json['name'];
    image = json['image'];
    cid = json['cid'];
    bid = json['bid'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['highlight'] = this.highlight;
    data['name'] = this.name;
    data['image'] = this.image;
    data['cid'] = this.cid;
    data['bid'] = this.bid;
    return data;
  }
} 