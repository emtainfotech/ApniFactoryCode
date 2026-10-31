class AddToCartModel {
  bool? status;
  int? code;
  String? msg;
  num? grandtotal;
  int? counter;

  AddToCartModel(
      {this.status, this.code, this.msg, this.grandtotal, this.counter});

  AddToCartModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    code = json['code'];
    msg = json['msg'];
    grandtotal = json['grandtotal'];
    counter = json['counter'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['status'] = this.status;
    data['code'] = this.code;
    data['msg'] = this.msg;
    data['grandtotal'] = this.grandtotal;
    data['counter'] = this.counter;
    return data;
  }
}