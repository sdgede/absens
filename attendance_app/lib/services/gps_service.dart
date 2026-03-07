import 'dart:math' as math;

import 'package:geolocator/geolocator.dart';

/// GPS service with Haversine-based radius validation.
class GpsService {
  GpsService();

  // ── Permission ────────────────────────────────────────────────────────────

  /// Request location permission. Returns true if granted.
  Future<bool> requestPermission() async {
    final status = await Geolocator.checkPermission();
    if (status == LocationPermission.deniedForever) return false;
    if (status == LocationPermission.denied) {
      final result = await Geolocator.requestPermission();
      return result == LocationPermission.always ||
          result == LocationPermission.whileInUse;
    }
    return true;
  }

  // ── Position ──────────────────────────────────────────────────────────────

  /// Get the current device GPS position.
  /// Throws [LocationServiceDisabledException] if GPS is off.
  Future<Position> getCurrentPosition() async {
    final serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      throw const LocationServiceDisabledException();
    }
    final granted = await requestPermission();
    if (!granted) {
      throw const PermissionDeniedException('Location permission denied');
    }
    return Geolocator.getCurrentPosition(
      desiredAccuracy: LocationAccuracy.high,
      timeLimit: const Duration(seconds: 15),
    );
  }

  // ── Distance & Radius ─────────────────────────────────────────────────────

  /// Haversine formula — returns distance in meters between two coordinates.
  double getDistanceInMeter(
    double lat1,
    double lng1,
    double lat2,
    double lng2,
  ) {
    const earthRadiusM = 6371000.0;
    final dLat = _toRad(lat2 - lat1);
    final dLng = _toRad(lng2 - lng1);

    final a = math.sin(dLat / 2) * math.sin(dLat / 2) +
        math.cos(_toRad(lat1)) *
            math.cos(_toRad(lat2)) *
            math.sin(dLng / 2) *
            math.sin(dLng / 2);

    final c = 2 * math.atan2(math.sqrt(a), math.sqrt(1 - a));
    return earthRadiusM * c;
  }

  /// Returns true if (lat, lng) is within [radiusMeter] of the branch.
  bool isWithinRadius(
    double lat,
    double lng,
    double branchLat,
    double branchLng,
    double radiusMeter,
  ) {
    final distance = getDistanceInMeter(lat, lng, branchLat, branchLng);
    return distance <= radiusMeter;
  }

  double _toRad(double deg) => deg * math.pi / 180;
}
