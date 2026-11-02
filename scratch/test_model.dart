import 'dart:convert';
import 'dart:io';
import 'file:///c:/Users/EPC001/Desktop/apni_factory/apni_factory/lib/model/home_screen_model.dart';

void main() async {
  final client = HttpClient();
  final request = await client.getUrl(Uri.parse('http://127.0.0.1:8000/api/homescreen?userid=1'));
  final response = await request.close();
  final body = await response.transform(utf8.decoder).join();
  print('HTTP status: ${response.statusCode}');
  try {
    final Map<String, dynamic> jsonMap = json.decode(body);
    final model = HomescreenModel.fromJson(jsonMap);
    print('SUCCESS parsing HomescreenModel!');
    print('Slider: ${model.data?.slider?.length}');
    print('Category carousels: ${model.data?.categoryCarousels?.length}');
    print('More products: ${model.data?.moreProducts?.length}');
    for (var c in model.data?.categoryCarousels ?? []) {
      print('Carousel: ${c.title} -> ${c.products?.length} products');
    }
  } catch (e, st) {
    print('ERROR PARSING: $e');
    print(st);
  }
}
