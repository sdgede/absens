import 'dart:convert';

import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../data/model/auth/model_login.dart';

/// Manages authentication tokens and user session.
///
/// Sensitive data (tokens)     → [FlutterSecureStorage]
/// Non-sensitive data (userId, name) → [SharedPreferences]
class AuthService {
  final FlutterSecureStorage _secureStorage;
  final SharedPreferences _prefs;

  // ── SecureStorage Keys ───────────────────────────────────────────────────
  static const _kAccessToken = 'access_token';
  static const _kRefreshToken = 'refresh_token';
  static const _kUserJson = 'user_data_secure'; // full user JSON

  // ── SharedPreferences Keys ───────────────────────────────────────────────
  static const _kUserId = 'user_id';
  static const _kUserName = 'user_name';
  static const _kIsLoggedIn = 'is_logged_in';

  AuthService({
    required FlutterSecureStorage secureStorage,
    required SharedPreferences prefs,
  })  : _secureStorage = secureStorage,
        _prefs = prefs;

  // ── Token Management ─────────────────────────────────────────────────────

  Future<void> saveToken(String accessToken, String refreshToken) async {
    await _secureStorage.write(key: _kAccessToken, value: accessToken);
    await _secureStorage.write(key: _kRefreshToken, value: refreshToken);
  }

  Future<String?> getAccessToken() =>
      _secureStorage.read(key: _kAccessToken);

  Future<String?> getRefreshToken() =>
      _secureStorage.read(key: _kRefreshToken);

  Future<void> clearTokens() async {
    await _secureStorage.delete(key: _kAccessToken);
    await _secureStorage.delete(key: _kRefreshToken);
  }

  // ── Session ──────────────────────────────────────────────────────────────

  bool isLoggedIn() => _prefs.getBool(_kIsLoggedIn) ?? false;

  // ── User Data ─────────────────────────────────────────────────────────────

  /// Persist full [ModelLogin] — sensitive fields in SecureStorage,
  /// display fields in SharedPreferences.
  Future<void> saveUserData(ModelLogin user) async {
    // Tokens
    await saveToken(user.accessToken, user.refreshToken);

    // Full JSON in SecureStorage (includes role, tenantId, branchId)
    await _secureStorage.write(
      key: _kUserJson,
      value: jsonEncode(user.toJson()),
    );

    // Non-sensitive → SharedPreferences for quick UI reads
    await _prefs.setString(_kUserId, user.userId);
    await _prefs.setString(_kUserName, user.name);
    await _prefs.setBool(_kIsLoggedIn, true);
  }

  /// Retrieve cached [ModelLogin] from SecureStorage.
  Future<ModelLogin?> getUserData() async {
    try {
      final raw = await _secureStorage.read(key: _kUserJson);
      if (raw == null) return null;
      return ModelLogin.fromJson(jsonDecode(raw) as Map<String, dynamic>);
    } catch (_) {
      return null;
    }
  }

  /// Clears all tokens and user data.
  Future<void> logout() async {
    await _secureStorage.deleteAll();
    await _prefs.remove(_kUserId);
    await _prefs.remove(_kUserName);
    await _prefs.setBool(_kIsLoggedIn, false);
  }
}
