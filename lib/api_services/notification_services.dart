import 'dart:convert';
import 'dart:developer';
import 'package:apni_factory/api_services/creator_api_services.dart';
import 'package:apni_factory/main.dart';
import 'package:apni_factory/screens/brand_notification_screen.dart';
import 'package:apni_factory/screens/notification_screen.dart';
import 'package:apni_factory/screens/track_order_screen.dart';
import 'package:apni_factory/shared_pref.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

// 🔥 REQUIRED for background notifications
@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  

  log("Data: ${message.data}");
  log("📩 Background message: ${message.messageId}");
}

class NotificationServices {
  FirebaseMessaging messaging = FirebaseMessaging.instance;
  final FlutterLocalNotificationsPlugin _localNotificationsPlugin =
      FlutterLocalNotificationsPlugin();

  // ✅ Notification Channel
  final AndroidNotificationChannel channel = AndroidNotificationChannel(
      "high_importance_channel", "High Importance Notification",
      description: "This channel is used for important notification",
      importance: Importance.max);

  // 🔥 INIT (call from Splash / Home)
  Future<void> init(BuildContext context) async {
    await requestNotificationPermission();
    await firebaseInit();
    await setupInteractedMessage();
    isTokenRefresh(context);
  }

// 🔔 Permission
  Future<void> requestNotificationPermission() async {
    NotificationSettings settings = await messaging.requestPermission(
        alert: true,
        announcement: true,
        badge: true,
        carPlay: true,
        criticalAlert: true,
        provisional: true,
        sound: true);
    if (settings.authorizationStatus == AuthorizationStatus.authorized) {
      print("user accessed permission");
    } else if (settings.authorizationStatus ==
        AuthorizationStatus.provisional) {
      print("user accessed provisional permission");
    } else {
      print("user denied permission");
    }
  }

  // 🔥 Firebase + Local Notification Init
  Future<void> firebaseInit() async {
   
    const AndroidInitializationSettings androidInitializationSetting =
        AndroidInitializationSettings("@mipmap/ic_launcher");

    final InitializationSettings initializationSettings =
        InitializationSettings(android: androidInitializationSetting);
   
    await _localNotificationsPlugin.initialize(
      initializationSettings,
      onDidReceiveNotificationResponse: (response) {
        if (response.payload != null) {
          final Map<String, dynamic> data = jsonDecode(response.payload!);
           log("🔔 Local notification clicked");
          handleNotificationNavigation(data);
        }
      },
    );

    // ✅ Create Channel
    await _localNotificationsPlugin
        .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(channel);
  
// 🔥 Foreground messages
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      log("Message Received: ${message.notification?.title}");
      log("Data: ${message.data}");
      
      showNotification(message);
    });


    // 🔥 Background click
    FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
      log("📩 Notification clicked (background)");

      handleNotificationNavigation(message.data);
    });
  }

// 🔥 Terminated (killed app)
  Future<void> setupInteractedMessage() async {
    RemoteMessage? message =
        await FirebaseMessaging.instance.getInitialMessage();

    if (message != null) {
      log("Data: ${message.data}");
      log("📩 Notification clicked (terminated)");
      handleNotificationNavigation(message.data);
    }
  }

// 🔔 Show Local Notification
  Future<void> showNotification(RemoteMessage message) async {
    AndroidNotificationDetails androidNotificationDetails =
        AndroidNotificationDetails(
      channel.id,
      channel.name,
      channelDescription: channel.description,
      importance: Importance.max,
      priority: Priority.high,
      ticker: 'ticker',
    );

    final NotificationDetails notificationDetails =
        NotificationDetails(android: androidNotificationDetails);

    await _localNotificationsPlugin.show(
      DateTime.now().millisecondsSinceEpoch ~/ 1000,
      // 0, // ID of notification
      message.notification?.title,
      message.notification?.body,

      notificationDetails,
      payload: jsonEncode(message.data),
    );
  }

  // 🧭 Navigation Logic (Navigator.push)
  void handleNotificationNavigation(Map<String, dynamic> data) {
     final String? screen = data['screen'];

  if (screen == null) return;
 WidgetsBinding.instance.addPostFrameCallback((_) {
    if (screen == 'orderdetail') {
      navigatorKey.currentState?.push(
        MaterialPageRoute(
          builder: (_) => TrackOrderScreen(
            orderid: data['screenid'].toString(),
          ),
        ),
      );
    }

    if (screen == 'branddetail') {
      navigatorKey.currentState?.push(
        MaterialPageRoute(
          builder: (_) => NotificationScreen(),
        ),
      );
    }
  });
  //    Future.delayed(const Duration(milliseconds: 500), () {
  //   final String? screen = data['screen']?.toString();


  //   if (screen == null) return;

  //   if (screen == 'orderdetail') {
  //     navigatorKey.currentState?.push(
  //       MaterialPageRoute(
  //         builder: (_) => TrackOrderScreen(orderid: data['screenid']),
  //       ),
  //     );
  //   }

  //   if (screen == 'branddetail') {
  //     navigatorKey.currentState?.push(
  //       MaterialPageRoute(
  //         builder: (_) => NotificationScreen(),
  //       ),
  //     );
  //   }
  // });
    // final String? screen = data['screen'];

    // if (screen == null) return;

    // if (screen == 'orderdetail') {
    //   navigatorKey.currentState?.push(
    //     MaterialPageRoute(
    //       builder: (_) => TrackOrderScreen(
    //         orderid: data['screenid'],
    //       ),
    //     ),
    //   );
    // }
    // if(screen =='branddetail'){
    //   navigatorKey.currentState?.push(
    //     MaterialPageRoute(
    //       builder: (_) => BrandNotificationScreen(
    //         brandid: data['screenid'],
    //       ),
    //     ),
    //   );
    // }
  }

  Future<String> getDeviceToken() async {
    String? token = await messaging.getToken();
    log("FCM Token: $token");
    return token!;
  }

  void isTokenRefresh(BuildContext context) {
    messaging.onTokenRefresh.listen((newToken) async {
      log("🔥 New FCM Token: $newToken");
      String? email = await SharedPref.getUserEmailId();
      String? userId = await SharedPref.getUserLoginId();
      if (email != null && userId != null) {
        await CreatorApiServices.loginDetails(
          context,
          email: email,
          password: null,
          deviceid: newToken,
        );
      }

    });
  }
}
