class ProductReviewModel {
  bool? status;
  int? code;
  String? msg;
  Data? data;

  ProductReviewModel({this.status, this.code, this.msg, this.data});

  ProductReviewModel.fromJson(Map<String, dynamic> json) {
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
  String? productId;
  String? customerId;
  String? rating;
  String? review;
  String? status;

  Data(
      {this.productId, this.customerId, this.rating, this.review, this.status});

  Data.fromJson(Map<String, dynamic> json) {
    productId = json['product_id'];
    customerId = json['customer_id'];
    rating = json['rating'];
    review = json['review'];
    status = json['status'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['product_id'] = this.productId;
    data['customer_id'] = this.customerId;
    data['rating'] = this.rating;
    data['review'] = this.review;
    data['status'] = this.status;
    return data;
  }
}