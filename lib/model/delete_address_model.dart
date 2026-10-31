class DeleteAddressModel {
  bool? status;
  int? code;
  String? msg;

  DeleteAddressModel({this.status, this.code, this.msg});

  DeleteAddressModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    code = json['code'];
    msg = json['msg'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['status'] = this.status;
    data['code'] = this.code;
    data['msg'] = this.msg;
    return data;
  }
}