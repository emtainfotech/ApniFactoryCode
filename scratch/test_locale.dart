import 'package:intl/intl_standalone.dart' as intl_standalone;

void main() async {
  print('Calling findSystemLocale...');
  final locale = await intl_standalone.findSystemLocale();
  print('System locale: $locale');
}

