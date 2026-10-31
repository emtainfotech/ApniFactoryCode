
import 'package:shared_preferences/shared_preferences.dart';

class SharedPref {
  static String userLoginIdKey = 'userLoginIdKey';

  static Future<String> getUserLoginId() async {
    SharedPreferences sharedPreferences = await SharedPreferences.getInstance();
    return sharedPreferences.getString(userLoginIdKey) ?? "";
  }

  static Future<bool> setUserLoginId(String userLoginId) async {
    SharedPreferences sharedPreferences = await SharedPreferences.getInstance();
    return sharedPreferences.setString(userLoginIdKey, userLoginId);
  }

  static String userEmailIdKey = 'userEmailIdKey';

  static Future<String> getUserEmailId() async {
    SharedPreferences sharedPreferences = await SharedPreferences.getInstance();
    return sharedPreferences.getString(userEmailIdKey) ?? "";
  }

  static Future<bool> setUserEmailId(String userEmailId) async {
    SharedPreferences sharedPreferences = await SharedPreferences.getInstance();
    return sharedPreferences.setString(userEmailIdKey, userEmailId);
  }

  static String userNameKey = 'userNameKey';

  static Future<String> getUserName() async {
    SharedPreferences sharedPreferences = await SharedPreferences.getInstance();
    return sharedPreferences.getString(userNameKey) ?? "";
  }

  static Future<bool> setUserName(String userName) async {
    SharedPreferences sharedPreferences = await SharedPreferences.getInstance();
    return sharedPreferences.setString(userNameKey, userName);
  }


  static String isLoggedInKey = 'isLoggedInKey';
    static Future<bool> setIsLoggedIn(bool value) async {
    SharedPreferences sharedPreferences =
        await SharedPreferences.getInstance();
    return sharedPreferences.setBool(isLoggedInKey, value);
  }

  static Future<bool> getIsLoggedIn() async {
    SharedPreferences sharedPreferences =
        await SharedPreferences.getInstance();
    return sharedPreferences.getBool(isLoggedInKey) ?? false;
  }


  static Future<bool> clearToken() async {
    SharedPreferences sharedPreferences = await SharedPreferences.getInstance();
    return sharedPreferences.clear();
  }

  
}
