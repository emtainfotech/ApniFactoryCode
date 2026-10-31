class OrderHistoryModel {
  bool? status;
  int? code;
  String? msg;
  List<Data>? data;

  OrderHistoryModel({this.status, this.code, this.msg, this.data});

  OrderHistoryModel.fromJson(Map<String, dynamic> json) {
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
  String? orderno;
  int? customerId;
  int? userId;
  String? address;
 num? sellercouponamount;
 num? admincouponamount;
  String? admincoupondetail;
 num? netamount;
  String? taxdetail;
 num? taxamount;
  num? grandtotal;
  String? createdAt;
  String? updatedAt;
  List<Orderdetail>? orderdetail;
  Ordertrack? ordertrack;
  List<Orderstatus>? orderstatus;

  Data(
      {this.id,
      this.orderno,
      this.customerId,
      this.userId,
      this.address,
      this.sellercouponamount,
      this.admincouponamount,
      this.admincoupondetail,
      this.netamount,
      this.taxdetail,
      this.taxamount,
      this.grandtotal,
      this.createdAt,
      this.updatedAt,
      this.orderdetail,
      this.ordertrack,
      this.orderstatus});

  Data.fromJson(Map<String, dynamic> json) {
    id = json['id'];
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
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    if (json['orderdetail'] != null) {
      orderdetail = <Orderdetail>[];
      json['orderdetail'].forEach((v) {
        orderdetail!.add(new Orderdetail.fromJson(v));
      });
    }
    ordertrack = json['ordertrack'] != null
        ? new Ordertrack.fromJson(json['ordertrack'])
        : null;
    if (json['orderstatus'] != null) {
      orderstatus = <Orderstatus>[];
      json['orderstatus'].forEach((v) {
        orderstatus!.add(new Orderstatus.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
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
    data['created_at'] = this.createdAt;
    data['updated_at'] = this.updatedAt;
    if (this.orderdetail != null) {
      data['orderdetail'] = this.orderdetail!.map((v) => v.toJson()).toList();
    }
    if (this.ordertrack != null) {
      data['ordertrack'] = this.ordertrack!.toJson();
    }
    if (this.orderstatus != null) {
      data['orderstatus'] = this.orderstatus!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class Orderdetail {
  int? id;
  int? orderId;
  String? orderno;
  int? customerId;
  int? productId;
  String? productname;
  String? hsn;
  String? brdcmpcat;
  String? attribute;
  String? coupondetail;
  String? createdAt;
  String? updatedAt;
  String? productimage;

  Orderdetail(
      {this.id,
      this.orderId,
      this.orderno,
      this.customerId,
      this.productId,
      this.productname,
      this.hsn,
      this.brdcmpcat,
      this.attribute,
      this.coupondetail,
      this.createdAt,
      this.updatedAt,
       this.productimage});

  Orderdetail.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    orderId = json['order_id'];
    orderno = json['orderno'];
    customerId = json['customer_id'];
    productId = json['product_id'];
    productname = json['productname'];
    hsn = json['hsn'];
    brdcmpcat = json['brdcmpcat'];
    attribute = json['attribute'];
    coupondetail = json['coupondetail'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    productimage = json['productimage'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['order_id'] = this.orderId;
    data['orderno'] = this.orderno;
    data['customer_id'] = this.customerId;
    data['product_id'] = this.productId;
    data['productname'] = this.productname;
    data['hsn'] = this.hsn;
    data['brdcmpcat'] = this.brdcmpcat;
    data['attribute'] = this.attribute;
    data['coupondetail'] = this.coupondetail;
    data['created_at'] = this.createdAt;
    data['updated_at'] = this.updatedAt;
    data['productimage'] = this.productimage;
    return data;
  }
}

class Ordertrack {
  int? id;
  int? orderId;
  String? transname;
  String? text;
  String? createdAt;
  String? updatedAt;
  String? transcontact;
  String? lrno;
  int? status;
  String? orderno;
 num? creditamnt;
  String? invoiceno;
  String? billty;
  String? invoice;

  Ordertrack(
      {this.id,
      this.orderId,
      this.transname,
      this.text,
      this.createdAt,
      this.updatedAt,
      this.transcontact,
      this.lrno,
      this.status,
      this.orderno,
      this.creditamnt,
      this.invoiceno,
      this.billty,
      this.invoice});

  Ordertrack.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    orderId = json['order_id'];
    transname = json['transname'];
    text = json['text'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    transcontact = json['transcontact'];
    lrno = json['lrno'];
    status = json['status'];
    orderno = json['orderno'];
    creditamnt = json['creditamnt'];
    invoiceno = json['invoiceno'];
    billty = json['billty'];
    invoice = json['invoice'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['order_id'] = this.orderId;
    data['transname'] = this.transname;
    data['text'] = this.text;
    data['created_at'] = this.createdAt;
    data['updated_at'] = this.updatedAt;
    data['transcontact'] = this.transcontact;
    data['lrno'] = this.lrno;
    data['status'] = this.status;
    data['orderno'] = this.orderno;
    data['creditamnt'] = this.creditamnt;
    data['invoiceno'] = this.invoiceno;
    data['billty'] = this.billty;
    data['invoice'] = this.invoice;
    return data;
  }
}

class Orderstatus {
  int? id;
  int? orderId;
  String? orderNo;
  String? status;
  String? msg;
  int? userId;
  String? createdAt;
  String? updatedAt;

  Orderstatus(
      {this.id,
      this.orderId,
      this.orderNo,
      this.status,
      this.msg,
      this.userId,
      this.createdAt,
      this.updatedAt});

  Orderstatus.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    orderId = json['order_id'];
    orderNo = json['order_no'];
    status = json['status'];
    msg = json['msg'];
    userId = json['user_id'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['order_id'] = this.orderId;
    data['order_no'] = this.orderNo;
    data['status'] = this.status;
    data['msg'] = this.msg;
    data['user_id'] = this.userId;
    data['created_at'] = this.createdAt;
    data['updated_at'] = this.updatedAt;
    return data;
  }
}