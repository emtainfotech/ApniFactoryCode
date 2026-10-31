import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';

class DataDeletionWebView extends StatefulWidget {
  const DataDeletionWebView({super.key});

  @override
  State<DataDeletionWebView> createState() => _DataDeletionWebViewState();
}

class _DataDeletionWebViewState extends State<DataDeletionWebView> {
  late final WebViewController controller;

  @override
  void initState() {
    super.initState();

    controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..loadRequest(Uri.parse(
          'https://apnifactory.co.in/data-deletion-request/'));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("Delete Account"),
      ),
      body: WebViewWidget(controller: controller),
    );
  }
}