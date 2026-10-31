class IncreaseCartQtyModel {
  bool? status;
  int? code;
  String? msg;
  int? grandtotal;

  IncreaseCartQtyModel({this.status, this.code, this.msg, this.grandtotal});

  IncreaseCartQtyModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    code = json['code'];
    msg = json['msg'];
    grandtotal = json['grandtotal'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['status'] = this.status;
    data['code'] = this.code;
    data['msg'] = this.msg;
    data['grandtotal'] = this.grandtotal;
    return data;
  }
}