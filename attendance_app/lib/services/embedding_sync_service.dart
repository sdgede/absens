import 'dart:convert';

import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../data/model/face/user_embedding.dart';
import '../data/remote/face_api.dart';
import 'encryption_service.dart';

/// Manages local caching of face embeddings synced from the server.
///
/// - All embeddings are stored AES-encrypted in [FlutterSecureStorage].
/// - Sync is considered stale after 24 hours.
class EmbeddingSyncService {
  static const _keyEmbeddings = 'face_embeddings_encrypted';
  static const _keyLastSync = 'face_last_sync_ms';
  static const _syncIntervalHours = 24;

  final FlutterSecureStorage _secureStorage;
  final SharedPreferences _prefs;
  final FaceApi _faceApi;
  final EncryptionService _encryption;

  EmbeddingSyncService({
    required FlutterSecureStorage secureStorage,
    required SharedPreferences prefs,
    required FaceApi faceApi,
    required EncryptionService encryption,
  })  : _secureStorage = secureStorage,
        _prefs = prefs,
        _faceApi = faceApi,
        _encryption = encryption;

  // ── Public API ────────────────────────────────────────────────────────────

  /// Download all tenant embeddings from the server and cache locally.
  Future<List<UserEmbedding>> syncFromServer(String tenantId) async {
    final result = await _faceApi.syncEmbeddings(tenantId);
    final embeddings = result.fold((_) => <UserEmbedding>[], (e) => e);

    if (embeddings.isNotEmpty) {
      await _saveEncrypted(embeddings);
      await saveLastSyncTime();
    }
    return embeddings;
  }

  /// Load cached embeddings from secure storage.
  Future<List<UserEmbedding>> loadLocalEmbeddings() async {
    try {
      final encrypted = await _secureStorage.read(key: _keyEmbeddings);
      if (encrypted == null) return [];
      final json = await _encryption.decrypt(encrypted);
      final list = jsonDecode(json) as List<dynamic>;
      return list
          .map((e) => UserEmbedding.fromJson(e as Map<String, dynamic>))
          .toList();
    } catch (_) {
      return [];
    }
  }

  /// Returns true if no sync has happened in the last 24 hours.
  bool needsSync() {
    final lastMs = _prefs.getInt(_keyLastSync);
    if (lastMs == null) return true;
    final lastSync = DateTime.fromMillisecondsSinceEpoch(lastMs);
    return DateTime.now().difference(lastSync).inHours >= _syncIntervalHours;
  }

  /// Record the current time as the last sync time.
  Future<void> saveLastSyncTime() async {
    await _prefs.setInt(_keyLastSync, DateTime.now().millisecondsSinceEpoch);
  }

  /// Remove all cached embeddings and sync timestamp.
  Future<void> clearEmbeddings() async {
    await _secureStorage.delete(key: _keyEmbeddings);
    await _prefs.remove(_keyLastSync);
  }

  // ── Helpers ───────────────────────────────────────────────────────────────

  Future<void> _saveEncrypted(List<UserEmbedding> embeddings) async {
    final json = jsonEncode(embeddings.map((e) => e.toJson()).toList());
    final encrypted = await _encryption.encrypt(json);
    await _secureStorage.write(key: _keyEmbeddings, value: encrypted);
  }
}
