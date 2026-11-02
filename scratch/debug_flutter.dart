import 'dart:io';
import 'package:flutter_tools/src/base/context.dart';
import 'package:flutter_tools/src/base/platform.dart';
import 'package:flutter_tools/src/cache.dart';
import 'package:flutter_tools/src/globals.dart' as globals;

void main(List<String> args) async {
  print('Step 1: Setting flutterRoot...');
  Cache.flutterRoot = 'C:\\Users\\EPC001\\flutter';
  print('Step 2: globals check...');
  print('Step 3: Done!');
}

