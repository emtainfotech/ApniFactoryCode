class UserCartModel {
  final bool status;
  final int code;
  final String msg;
  final num? grandTotal; 
  final num? netamount; 
  final int counter;
  final num cgst;
  final num sgst;
  final num igst;
  final num? couponbyadmin;
  final String combinecoupon;

  final Map<String, List<List<ProductCategory>>> data;

  UserCartModel({
    required this.status,
    required this.code,
    required this.msg,
    required this.grandTotal,
    required this.netamount,
    required this.counter,
    required this.cgst,
    required this.sgst,
    required this.igst,
    required this.couponbyadmin,
    required this.combinecoupon,
    required this.data,
  });

  // Factory method to parse JSON into UserCartModel
  factory UserCartModel.fromJson(Map<String, dynamic> json) {
    return UserCartModel(
      status: json['status'],
      code: json['code'],
      msg: json['msg'],
      grandTotal: json['grandtotal'],
      netamount: json['netamount'],
      counter: json['counter'],
      cgst: json['cgst'],
      sgst: json['sgst'],
      igst: json['igst'],
      couponbyadmin: json['couponbyadmin'],
      combinecoupon: json['combinecoupon'],
      data: (json['data'] as Map<String, dynamic>).map(
        (key, value) => MapEntry(
          key,
          (value as List)
              .map((list) => (list as List)
                  .map((item) => ProductCategory.fromJson(item))
                  .toList())
              .toList(),
        ),
      ),
    );
  }

  // Method to convert UserCartModel to JSON
  Map<String, dynamic> toJson() {
    return {
      'status': status,
      'code': code,
      'msg': msg,
      'grandtotal': grandTotal,
      'netamount':netamount,
      'counter': counter,
      'cgst':cgst,
      'sgst':sgst,
      'igst':igst,
      'couponbyadmin':couponbyadmin,
      'combinecoupon':combinecoupon,
      'data': data.map(
        (key, value) => MapEntry(
          key,
          value
              .map((list) => list.map((item) => item.toJson()).toList())
              .toList(),
        ),
      ),
    };
  }
}

class ProductCategory {
  final int cartid;
  final int productid;

  final String productName;
  final String productImage;
  final List<AttributeData> attributeData;

  ProductCategory({
    required this.cartid,
    required this.productid,
    required this.productName,
    required this.productImage,
    required this.attributeData,
  });

  // Factory method to parse JSON into ProductCategory
  factory ProductCategory.fromJson(Map<String, dynamic> json) {
    return ProductCategory(
      cartid: json['cartid'],
      productid: json['productid'],
      productName: json['productname'],
      productImage: json['productimage'],
      attributeData: (json['attributedata'] as List)
          .map((item) => AttributeData.fromJson(item))
          .toList(),
    );
  }

  // Method to convert ProductCategory to JSON
  Map<String, dynamic> toJson() {
    return {
      'cartyid':cartid,
      'productid': productid,
      'productname': productName,
      'productimage': productImage,
      'attributedata': attributeData.map((item) => item.toJson()).toList(),
    };
  }
}

class AttributeData {
  final String boxPacking;
  final String color;
  final num? totalPrice;
  final int? qty;
  final int? attributeid;
  final int? prprice;
  final int? boxpcs;
  final num? unitprice;
  final int? productattributeid;
  final num? couponamount;

  AttributeData(
      {required this.boxPacking,
      required this.color,
      required this.totalPrice,
      required this.qty,
      required this.attributeid,
      required this.prprice,
      required this.boxpcs,
      required this.unitprice, 
      required this.productattributeid,
      required this.couponamount});

  // Factory method to parse JSON into AttributeData
  factory AttributeData.fromJson(Map<String, dynamic> json) {
    return AttributeData(
        boxPacking: json['boxpacking'],
        color: json['color'],
        totalPrice: json['totalprice'],
        qty: json['qty'],
        attributeid: json['attributeid'],
        prprice: json['prprice'],
        boxpcs: json['boxpcs'],
        unitprice: json['unitprice'],
        productattributeid: json['productattributeid'],
        couponamount: json['couponamount']);
  }

  // Method to convert AttributeData to JSON
  Map<String, dynamic> toJson() {
    return {
      'boxpacking': boxPacking,
      'color': color,
      'totalprice': totalPrice,
      'qty': qty,
      'attributeid': attributeid,
      'prprice': prprice,
      'boxpcs':boxpcs,
      'unitprice':unitprice,
      'productattributeid':productattributeid,
      'couponamount':couponamount
    };
  }
}



