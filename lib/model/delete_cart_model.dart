class DeleteCartModel {
  bool? status;
  int? code;
  String? msg;

  DeleteCartModel({this.status, this.code, this.msg});

  DeleteCartModel.fromJson(Map<String, dynamic> json) {
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