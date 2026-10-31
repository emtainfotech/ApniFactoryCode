class DeleteCartAttributeModel {
  bool? status;
  int? code;
  String? msg;

  DeleteCartAttributeModel({this.status, this.code, this.msg});

  DeleteCartAttributeModel.fromJson(Map<String, dynamic> json) {
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
