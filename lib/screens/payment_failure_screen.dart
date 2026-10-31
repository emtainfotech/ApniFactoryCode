import 'package:apni_factory/screens/all_screen.dart';
import 'package:apni_factory/screens/home_screen.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';

class PaymentFailureScreen extends StatefulWidget {
  final String? error;
  const PaymentFailureScreen({super.key, this.error});

  @override
  State<PaymentFailureScreen> createState() => _PaymentFailureScreenState();
}

class _PaymentFailureScreenState extends State<PaymentFailureScreen> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.red.shade50,
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(
                Icons.cancel,
                size: 100,
                color: Colors.red,
              ),
              const SizedBox(height: 24),
              const Text(
                "Payment Failed",
                style: TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                  color: Colors.red,
                ),
              ),
              const SizedBox(height: 16),
              if (widget.error != null)
                Text(
                  widget.error!,
                  textAlign: TextAlign.center,
                ),
              const SizedBox(height: 32),
              ElevatedButton(
                onPressed: () {
                  Navigator.pushAndRemoveUntil(
                    context,
                    MaterialPageRoute(
                      builder: (context) {
                        return AllScreen();
                      },
                    ),
                    (route) {
                      return false;
                    },
                  );
                },
                child: const Text("Try Again"),
              )
            ],
          ),
        ),
      ),
    );
  }
}
