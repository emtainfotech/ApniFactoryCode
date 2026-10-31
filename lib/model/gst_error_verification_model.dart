class GstErrorResponseModel {
  final bool error;
  final String message;
  final String help;

  GstErrorResponseModel({
    required this.error,
    required this.message,
    required this.help,
  });

  factory GstErrorResponseModel.fromJson(Map<String, dynamic> json) {
    return GstErrorResponseModel(
      error: json['error'] ?? false,
      message: json['message'] ?? '',
      help: json['help'] ?? '',
    );
  }
}