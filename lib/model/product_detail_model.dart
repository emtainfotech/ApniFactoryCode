class ProductDetailModel {
  bool? status;
  int? code;
  String? msg;
  Data? data;

  ProductDetailModel({this.status, this.code, this.msg, this.data});

  ProductDetailModel.fromJson(Map<String, dynamic> json) {
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
  int? productId;
  int? maincategoryId;
  int? categoryId;
  int? subcategoryId;
  String? name;
  String? slug;
  String? title;
  String? image;
  String? description;
  List<String>? multipleimages;
  int? status;
  String? createdAt;
  String? updatedAt;
  int? brandId;
  int? userId;
  String? hsncode;
  num? tax;
  List<Attributes>? attributes;
  Sellerdetail? sellerdetail;
  List<Reviewlist>? reviewlist;
  bool? postreview;
  bool? wishlist;
  List<Relatedproductlist>? relatedproductlist;

  Data(
      {this.id,
      this.productId,
      this.maincategoryId,
      this.categoryId,
      this.subcategoryId,
      this.name,
      this.slug,
      this.title,
      this.image,
      this.description,
      this.multipleimages,
      this.status,
      this.createdAt,
      this.updatedAt,
      this.brandId,
      this.userId,
      this.hsncode,
      this.tax,
      this.attributes,
      this.sellerdetail,
      this.reviewlist,
      this.postreview,
      this.wishlist,
      this.relatedproductlist});

  Data.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    productId = json['product_id'];
    maincategoryId = json['maincategory_id'];
    categoryId = json['category_id'];
    subcategoryId = json['subcategory_id'];
    name = json['name'];
    slug = json['slug'];
    title = json['title'];
    image = json['image'];
    description = json['description'];
    multipleimages = json['multipleimages'].cast<String>();
    status = json['status'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    brandId = json['brand_id'];
    userId = json['user_id'];
    hsncode = json['hsncode'];
    tax = json['tax'];
    if (json['attributes'] != null) {
      attributes = <Attributes>[];
      json['attributes'].forEach((v) {
        attributes!.add(new Attributes.fromJson(v));
      });
    }
    sellerdetail = json['sellerdetail'] != null
        ? new Sellerdetail.fromJson(json['sellerdetail'])
        : null;
    if (json['reviewlist'] != null) {
      reviewlist = <Reviewlist>[];
      json['reviewlist'].forEach((v) {
        reviewlist!.add(new Reviewlist.fromJson(v));
      });
    }
    postreview = json['postreview'];
    wishlist = json['wishlist'];
    if (json['relatedproductlist'] != null) {
      relatedproductlist = <Relatedproductlist>[];
      json['relatedproductlist'].forEach((v) {
        relatedproductlist!.add(new Relatedproductlist.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['product_id'] = this.productId;
    data['maincategory_id'] = this.maincategoryId;
    data['category_id'] = this.categoryId;
    data['subcategory_id'] = this.subcategoryId;
    data['name'] = this.name;
    data['slug'] = this.slug;
    data['title'] = this.title;
    data['image'] = this.image;
    data['description'] = this.description;
    data['multipleimages'] = this.multipleimages;
    data['status'] = this.status;
    data['created_at'] = this.createdAt;
    data['updated_at'] = this.updatedAt;
    data['brand_id'] = this.brandId;
    data['user_id'] = this.userId;
    data['hsncode'] = this.hsncode;
    data['tax'] = this.tax;
    if (this.attributes != null) {
      data['attributes'] = this.attributes!.map((v) => v.toJson()).toList();
    }
    if (this.sellerdetail != null) {
      data['sellerdetail'] = this.sellerdetail!.toJson();
    }
    if (this.reviewlist != null) {
      data['reviewlist'] = this.reviewlist!.map((v) => v.toJson()).toList();
    }
    data['postreview'] = this.postreview;
    data['wishlist'] = this.wishlist;
    if (this.relatedproductlist != null) {
      data['relatedproductlist'] =
          this.relatedproductlist!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class Attributes {
  String? color;
  String? boxpacking;
  int? quantity;
  num? price;
  num? oldprice;
  int? attributeid;

  Attributes(
      {this.color,
      this.boxpacking,
      this.quantity,
      this.price,
      this.oldprice,
      this.attributeid});

  Attributes.fromJson(Map<String, dynamic> json) {
    color = json['color'];
    boxpacking = json['boxpacking'];
    quantity = json['quantity'];
    price = json['price'];
    oldprice = json['oldprice'];
    attributeid = json['attributeid'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['color'] = this.color;
    data['boxpacking'] = this.boxpacking;
    data['quantity'] = this.quantity;
    data['price'] = this.price;
    data['oldprice'] = this.oldprice;
    data['attributeid'] = this.attributeid;
    return data;
  }
}

class Sellerdetail {
  String? name;
  String? email;
  String? mobile;
  String? city;
  String? photo;

  Sellerdetail({this.name, this.email, this.mobile, this.city, this.photo});

  Sellerdetail.fromJson(Map<String, dynamic> json) {
    name = json['name'];
    email = json['email'];
    mobile = json['mobile'];
    city = json['city'];
    photo = json['photo'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['name'] = this.name;
    data['email'] = this.email;
    data['mobile'] = this.mobile;
    data['city'] = this.city;
    data['photo'] = this.photo;
    return data;
  }
}

class Reviewlist {
  String? customername;
  String? customerimage;
  String? review;
  int? rating;
  String? date;

  Reviewlist(
      {this.customername,
      this.customerimage,
      this.review,
      this.rating,
      this.date});

  Reviewlist.fromJson(Map<String, dynamic> json) {
    customername = json['customername'];
    customerimage = json['customerimage'];
    review = json['review'];
    rating = json['rating'];
    date = json['date'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['customername'] = this.customername;
    data['customerimage'] = this.customerimage;
    data['review'] = this.review;
    data['rating'] = this.rating;
    data['date'] = this.date;
    return data;
  }
}

class Relatedproductlist {
  int? id;
  String? name;
  String? image;
  String? colorid;
  String? color;
  String? price;

  Relatedproductlist(
      {this.id, this.name, this.image, this.color, this.price, this.colorid});

  Relatedproductlist.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    image = json['image'];
    colorid = json['colorid'];
    color = json['color'];
    price = json['price'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['name'] = this.name;
    data['image'] = this.image;
    data['colorid'] = this.colorid;
    data['color'] = this.color;
    data['price'] = this.price;
    return data;
  }
}
