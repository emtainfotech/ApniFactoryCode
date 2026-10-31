import 'package:apni_factory/provider/brand_notification_screen_provider.dart';
import 'package:apni_factory/provider/brand_screen_provider.dart';
import 'package:apni_factory/provider/cart_screen_provider.dart';
import 'package:apni_factory/provider/category_screen_provider.dart';
import 'package:apni_factory/provider/change_password_provider.dart';

import 'package:apni_factory/provider/company_screen_provider.dart';
import 'package:apni_factory/provider/coupon_screen_provider.dart';
import 'package:apni_factory/provider/faq_provider.dart';
import 'package:apni_factory/provider/feedback_provider.dart';
import 'package:apni_factory/provider/forgot_password_provider.dart';
import 'package:apni_factory/provider/homeScreen_provider.dart';
import 'package:apni_factory/provider/login_provider.dart';
import 'package:apni_factory/provider/order_history_provider.dart';
import 'package:apni_factory/provider/pages_provider.dart';
import 'package:apni_factory/provider/product_category_list_screen_provider.dart';
import 'package:apni_factory/provider/product_detail_provider.dart';
import 'package:apni_factory/provider/sign_up_provider.dart';
import 'package:apni_factory/provider/single_order_detail_provider.dart';
import 'package:apni_factory/provider/ticket_provider.dart';
import 'package:apni_factory/provider/wishlist_screen_provider.dart';
import 'package:apni_factory/provider/your_profile_screen_provider.dart';
import 'package:provider/provider.dart';

class AppProvider {
  static notifierProvider() {
    return [
      ChangeNotifierProvider(
        create: (context) => LoginProvider(),
      ),
      ChangeNotifierProvider(
        create: (context) => SignUpProvider(),
      ),
      ChangeNotifierProvider(
        create: (context) => HomeScreenProvider(),
      ),
      ChangeNotifierProvider(
        create: (context) => CategoryScreenProvider(),
      ),
      ChangeNotifierProvider(
        create: (context) => CompanyScreenProvider(),
      ),
      ChangeNotifierProvider(
        create: (context) => BrandScreenProvider(),
      ),
      ChangeNotifierProvider(
        create: (context) => ProductCategoryListScreenProvider(),
      ),
       ChangeNotifierProvider(
        create: (context) => YourProfileScreenProvider(),
      ),
       ChangeNotifierProvider(
        create: (context) => ForgotPasswordProvider(),
      ),
       ChangeNotifierProvider(
        create: (context) => FaqProvider(),
      ),
        ChangeNotifierProvider(
        create: (context) => FeedbackProvider(),
      ),
       ChangeNotifierProvider(
        create: (context) => PagesProvider(),
      ),
      ChangeNotifierProvider(
        create: (context) => ProductDetailProvider()
      ),
         ChangeNotifierProvider(
        create: (context) => WishlistProvider()
      ),
        ChangeNotifierProvider(
        create: (context) => CartScreenProvider()
      ),
       ChangeNotifierProvider(
        create: (context) => CouponScreenProvider()
      ),
    
      ChangeNotifierProvider(
        create: (context) => ChangePasswordProvider()
      ),
       ChangeNotifierProvider(
        create: (context) => OrderHistoryProvider()
      ),
       ChangeNotifierProvider(
        create: (context) => SingleOrderDetailProvider()
      ),
      ChangeNotifierProvider(
        create: (context) => BrandNotificationScreenProvider()
      ),
       ChangeNotifierProvider(
        create: (context) => TicketProvider()
      ),
    ];
  }
}