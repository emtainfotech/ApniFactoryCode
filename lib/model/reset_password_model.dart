class ResetPasswordModel {
  bool? status;
  int? code;
  String? msg;
  Data? data;

  ResetPasswordModel({this.status, this.code, this.msg, this.data});

  ResetPasswordModel.fromJson(Map<String, dynamic> json) {
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
  String? name;
  String? email;
  String? mobile;
  String? lastlogin;
  String? password;
  String? type;
  String? status;
  String? rememberToken;
  String? createdAt;
  String? updatedAt;
  String? deviceid;
  String? location;
  int? followers;
  int? following;
  String? image;
  int? otp;
  String? regby;

  Data(
      {this.id,
      this.name,
      this.email,
      this.mobile,
      this.lastlogin,
      this.password,
      this.type,
      this.status,
      this.rememberToken,
      this.createdAt,
      this.updatedAt,
      this.deviceid,
      this.location,
      this.followers,
      this.following,
      this.image,
      this.otp,
      this.regby});

  Data.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    email = json['email'];
    mobile = json['mobile'];
    lastlogin = json['lastlogin'];
    password = json['password'];
    type = json['type'];
    status = json['status'];
    rememberToken = json['remember_token'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    deviceid = json['deviceid'];
    location = json['location'];
    followers = json['followers'];
    following = json['following'];
    image = json['image'];
    otp = json['otp'];
    regby = json['regby'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['name'] = this.name;
    data['email'] = this.email;
    data['mobile'] = this.mobile;
    data['lastlogin'] = this.lastlogin;
    data['password'] = this.password;
    data['type'] = this.type;
    data['status'] = this.status;
    data['remember_token'] = this.rememberToken;
    data['created_at'] = this.createdAt;
    data['updated_at'] = this.updatedAt;
    data['deviceid'] = this.deviceid;
    data['location'] = this.location;
    data['followers'] = this.followers;
    data['following'] = this.following;
    data['image'] = this.image;
    data['otp'] = this.otp;
    data['regby'] = this.regby;
    return data;
  }
}