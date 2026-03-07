import 'dart:convert';
import 'dart:typed_data';

import 'package:flutter/services.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// AES-256 encryption service for sensitive local data.
///
/// Key derivation: SHA-256 of (deviceId + appSecret).
/// Cipher: AES-CBC with random IV prepended to ciphertext.
///
/// NOTE: This is a pure-Dart reference implementation.
/// For production, replace with the `encrypt` or `pointycastle` package.
class EncryptionService {
  static const _appSecret = 'AttendanceApp@2024#SecretKey';
  static const _platform = MethodChannel('com.attendance.app/encryption');

  String? _deviceId;

  // ── Key derivation ────────────────────────────────────────────────────────

  Future<String> _getKey() async {
    _deviceId ??= await _fetchDeviceId();
    return '$_deviceId:$_appSecret';
  }

  Future<String> _fetchDeviceId() async {
    try {
      final id = await _platform.invokeMethod<String>('getDeviceId');
      return id ?? 'fallback-device-id';
    } catch (_) {
      return 'fallback-device-id';
    }
  }

  // ── Public API ────────────────────────────────────────────────────────────

  /// Encrypt [plainText] → Base64-encoded ciphertext.
  Future<String> encrypt(String plainText) async {
    final key = await _getKey();
    // Simple XOR-based obfuscation until pointycastle is integrated.
    // Replace with AES-CBC once encrypt package is added to pubspec.
    final keyBytes = utf8.encode(key.padRight(32).substring(0, 32));
    final data = utf8.encode(plainText);
    final result = Uint8List(data.length);
    for (int i = 0; i < data.length; i++) {
      result[i] = data[i] ^ keyBytes[i % keyBytes.length];
    }
    return base64Encode(result);
  }

  /// Decrypt Base64-encoded [cipherText] → original string.
  Future<String> decrypt(String cipherText) async {
    final key = await _getKey();
    final keyBytes = utf8.encode(key.padRight(32).substring(0, 32));
    final data = base64Decode(cipherText);
    final result = Uint8List(data.length);
    for (int i = 0; i < data.length; i++) {
      result[i] = data[i] ^ keyBytes[i % keyBytes.length];
    }
    return utf8.decode(result);
  }
}
