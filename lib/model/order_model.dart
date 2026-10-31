class OrderModel {
  bool? status;
  int? code;
  String? msg;
  Data? data;
  String? hash;
  String? furl;
  String? surl;
  Hashparameter? hashparameter;

  OrderModel(
      {this.status,
      this.code,
      this.msg,
      this.data,
      this.hash,
      this.furl,
      this.surl,
      this.hashparameter});

  OrderModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    code = json['code'];
    msg = json['msg'];
    data = json['data'] != null ? new Data.fromJson(json['data']) : null;
    hash = json['hash'];
    furl = json['furl'];
    surl = json['surl'];
    hashparameter = json['hashparameter'] != null
        ? new Hashparameter.fromJson(json['hashparameter'])
        : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['status'] = this.status;
    data['code'] = this.code;
    data['msg'] = this.msg;
    if (this.data != null) {
      data['data'] = this.data!.toJson();
    }
    data['hash'] = this.hash;
    data['furl'] = this.furl;
    data['surl'] = this.surl;
    if (this.hashparameter != null) {
      data['hashparameter'] = this.hashparameter!.toJson();
    }
    return data;
  }
}

class Data {
  int? orderno;
  String? customerId;
  int? userId;
  String? address;
  num? sellercouponamount;
  num? admincouponamount;
  String? admincoupondetail;
  num? netamount;
  String? taxdetail;
  num? taxamount;
  num? grandtotal;

  Data(
      {this.orderno,
      this.customerId,
      this.userId,
      this.address,
      this.sellercouponamount,
      this.admincouponamount,
      this.admincoupondetail,
      this.netamount,
      this.taxdetail,
      this.taxamount,
      this.grandtotal});

  Data.fromJson(Map<String, dynamic> json) {
    orderno = json['orderno'];
    customerId = json['customer_id'];
    userId = json['user_id'];
    address = json['address'];
    sellercouponamount = json['sellercouponamount'];
    admincouponamount = json['admincouponamount'];
    admincoupondetail = json['admincoupondetail'];
    netamount = json['netamount'];
    taxdetail = json['taxdetail'];
    taxamount = json['taxamount'];
    grandtotal = json['grandtotal'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['orderno'] = this.orderno;
    data['customer_id'] = this.customerId;
    data['user_id'] = this.userId;
    data['address'] = this.address;
    data['sellercouponamount'] = this.sellercouponamount;
    data['admincouponamount'] = this.admincouponamount;
    data['admincoupondetail'] = this.admincoupondetail;
    data['netamount'] = this.netamount;
    data['taxdetail'] = this.taxdetail;
    data['taxamount'] = this.taxamount;
    data['grandtotal'] = this.grandtotal;
    return data;
  }
}

class Hashparameter {
  String? key;
  int? txnid;
  num? amount;
  String? productinfo;
  String? firstname;
  String? email;
  int? udf1;
  bool? udf2;

  Hashparameter(
      {this.key,
      this.txnid,
      this.amount,
      this.productinfo,
      this.firstname,
      this.email,
      this.udf1,
      this.udf2});

  Hashparameter.fromJson(Map<String, dynamic> json) {
    key = json['key'];
    txnid = json['txnid'];
    amount = json['amount'];
    productinfo = json['productinfo'];
    firstname = json['firstname'];
    email = json['email'];
    udf1 = json['udf1'];
    udf2 = json['udf2'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['key'] = this.key;
    data['txnid'] = this.txnid;
    data['amount'] = this.amount;
    data['productinfo'] = this.productinfo;
    data['firstname'] = this.firstname;
    data['email'] = this.email;
    data['udf1'] = this.udf1;
    data['udf2'] = this.udf2;
    return data;
  }
}