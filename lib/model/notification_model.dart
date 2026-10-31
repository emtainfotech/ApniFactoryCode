class NotificationModel {
  bool? status;
  int? code;
  String? msg;
  List<Data>? data;

  NotificationModel({this.status, this.code, this.msg, this.data});

  NotificationModel.fromJson(Map<String, dynamic> json) {
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
  int? customerId;
  int? msgread;
  String? createdAt;
  String? updatedAt;
  String? title;
  String? body;

  Data(
      {this.id,
      this.customerId,
      this.msgread,
      this.createdAt,
      this.updatedAt,
      this.title,
      this.body});

  Data.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    customerId = json['customer_id'];
    msgread = json['msgread'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    title = json['title'];
    body = json['body'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['customer_id'] = this.customerId;
    data['msgread'] = this.msgread;
    data['created_at'] = this.createdAt;
    data['updated_at'] = this.updatedAt;
    data['title'] = this.title;
    data['body'] = this.body;
    return data;
  }
}