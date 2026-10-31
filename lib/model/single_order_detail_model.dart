class SingleOrderDetailModel {
  bool? status;
  int? code;
  String? msg;
  Data? data;

  SingleOrderDetailModel({this.status, this.code, this.msg, this.data});

  SingleOrderDetailModel.fromJson(Map<String, dynamic> json) {
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
  Transection? transection;
  Track? track;
  List<Status>? status;
  String? invoiceurl;

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
      this.transection,
      this.track,
      this.status,
      this.invoiceurl});

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

    transection = json['transection'] != null
        ? new Transection.fromJson(json['transection'])
        : null;
    track = json['track'] != null 
    ? new Track.fromJson(json['track']) : null;


    if (json['status'] != null) {
      status = <Status>[];
      json['status'].forEach((v) {
        status!.add(new Status.fromJson(v));
      });
    }
    invoiceurl = json['invoiceurl'];
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
    if (this.transection != null) {
      data['transection'] = this.transection!.toJson();
    }
    if (this.track != null) {
      data['track'] = this.track!.toJson();
    }
    if (this.status != null) {
      data['status'] = this.status!.map((v) => v.toJson()).toList();
    }
    data['invoiceurl'] = this.invoiceurl;
    return data;
  }
}

class Orderdetail {
  String? productname;
  String? productimage;
  String? hsn;
  String? brdcmpcat;
  List<Attribute>?  attribute;
  String? coupondetail;

  Orderdetail(
      {this.productname,
      this.productimage,
      this.hsn,
      this.brdcmpcat,
      this.attribute,
      this.coupondetail});

  Orderdetail.fromJson(Map<String, dynamic> json) {
    productname = json['productname'];
    productimage = json['productimage'];
    hsn = json['hsn'];
    brdcmpcat = json['brdcmpcat'];
   if (json['attribute'] != null) {
  attribute = <Attribute>[];
  json['attribute'].forEach((v) {
    attribute!.add(Attribute.fromJson(v));
  });
}
    coupondetail = json['coupondetail'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['productname'] = this.productname;
    data['productimage'] = this.productimage;
    data['hsn'] = this.hsn;
    data['brdcmpcat'] = this.brdcmpcat;
   if (attribute != null) {
  data['attribute'] = attribute!.map((e) => e.toJson()).toList();
}
    data['coupondetail'] = this.coupondetail;
    return data;
  }
}

class Attribute {
  String? boxpacking;
  String? color;
  int? qty;
  int? boxpcs;
  int? prprice;
  int? coupon;
  int? amntaftrcoupn;
  int? unitprice;
  int? totalprice;
  int? tax;
  num? taxamount;

  Attribute(
      {this.boxpacking,
      this.color,
      this.qty,
      this.boxpcs,
      this.prprice,
      this.coupon,
      this.amntaftrcoupn,
      this.unitprice,
      this.totalprice,
      this.tax,
      this.taxamount});

  Attribute.fromJson(Map<String, dynamic> json) {
    boxpacking = json['boxpacking'];
    color = json['color'];
    qty = json['qty'];
    boxpcs = json['boxpcs'];
    prprice = json['prprice'];
    coupon = json['coupon'];
    amntaftrcoupn = json['amntaftrcoupn'];
    unitprice = json['unitprice'];
    totalprice = json['totalprice'];
    tax = json['tax'];
    taxamount = json['taxamount'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['boxpacking'] = this.boxpacking;
    data['color'] = this.color;
    data['qty'] = this.qty;
    data['boxpcs'] = this.boxpcs;
    data['prprice'] = this.prprice;
    data['coupon'] = this.coupon;
    data['amntaftrcoupn'] = this.amntaftrcoupn;
    data['unitprice'] = this.unitprice;
    data['totalprice'] = this.totalprice;
    data['tax'] = this.tax;
    data['taxamount'] = this.taxamount;
    return data;
  }
}

class Transection {
  String? txnid;
  String? status;
  String? txndetail;

  Transection({this.txnid, this.status, this.txndetail});

  Transection.fromJson(Map<String, dynamic> json) {
    txnid = json['txnid'];
    status = json['status'];
    txndetail = json['txndetail'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['txnid'] = this.txnid;
    data['status'] = this.status;
    data['txndetail'] = this.txndetail;
    return data;
  }
}

class Track {
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

  Track(
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

  Track.fromJson(Map<String, dynamic> json) {
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

class Status {
  String? msg;
  String? status;
  String? createdAt;

  Status({this.msg, this.status, this.createdAt});

  Status.fromJson(Map<String, dynamic> json) {
    msg = json['msg'];
    status = json['status'];
    createdAt = json['created_at'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['msg'] = this.msg;
    data['status'] = this.status;
    data['created_at'] = this.createdAt;
    return data;
  }
}
