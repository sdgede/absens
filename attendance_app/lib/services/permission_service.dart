import 'package:permission_handler/permission_handler.dart';

/// Centralized permission request helper.
class PermissionService {
  PermissionService();

  Future<bool> requestCamera() async =>
      (await Permission.camera.request()).isGranted;

  Future<bool> requestLocation() async =>
      (await Permission.location.request()).isGranted;

  Future<bool> requestNotification() async =>
      (await Permission.notification.request()).isGranted;

  Future<bool> requestStorage() async =>
      (await Permission.storage.request()).isGranted;

  Future<Map<Permission, PermissionStatus>> requestAll() =>
      [
        Permission.camera,
        Permission.location,
        Permission.notification,
        Permission.storage,
      ].request();
}
