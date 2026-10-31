class GetPagesModel {
  bool? status;
  int? code;
  String? msg;
  String? name;
  String? data;

  GetPagesModel({this.status, this.code, this.msg, this.name, this.data});

  GetPagesModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    code = json['code'];
    msg = json['msg'];
    name = json['name'];
    data = json['data'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['status'] = this.status;
    data['code'] = this.code;
    data['msg'] = this.msg;
    data['name'] = this.name;
    data['data'] = this.data;
    return data;
  }
}