class HomescreenModel {
  bool? status;
  int? code;
  String? msg;
  Data? data;

  HomescreenModel({this.status, this.code, this.msg, this.data});

  HomescreenModel.fromJson(Map<String, dynamic> json) {
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
  List<String>? slider;
  List<Maincategory>? maincategory;
  List<String>? advertisement;
  int? notification;

  Data({this.slider, this.maincategory, this.advertisement, this.notification});

  Data.fromJson(Map<String, dynamic> json) {
    slider = json['slider'].cast<String>();
    if (json['maincategory'] != null) {
      maincategory = <Maincategory>[];
      json['maincategory'].forEach((v) {
        maincategory!.add(new Maincategory.fromJson(v));
      });
    }
    advertisement = json['advertisement'].cast<String>();
    notification = json['notification'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['slider'] = this.slider;
    if (this.maincategory != null) {
      data['maincategory'] = this.maincategory!.map((v) => v.toJson()).toList();
    }
    data['advertisement'] = this.advertisement;
    data['notification'] = this.notification;
    return data;
  }
}

class Maincategory {
  int? id;
  String? name;
  String? title;
  String? image;
  int? status;
  String? createdAt;
  String? updatedAt;
  int? sequence;

  Maincategory(
      {this.id,
      this.name,
      this.title,
      this.image,
      this.status,
      this.createdAt,
      this.updatedAt,
      this.sequence});

  Maincategory.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    title = json['title'];
    image = json['image'];
    status = json['status'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    sequence = json['sequence'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['name'] = this.name;
    data['title'] = this.title;
    data['image'] = this.image;
    data['status'] = this.status;
    data['created_at'] = this.createdAt;
    data['updated_at'] = this.updatedAt;
    data['sequence'] = this.sequence;
    return data;
  }
}