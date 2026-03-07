import 'dart:async';

import 'auth_service.dart';

/// Automatically logs the user out after 15 minutes of inactivity.
class AutoLogoutService {
  final AuthService authService;
  static const _timeoutDuration = Duration(minutes: 15);

  Timer? _timer;

  AutoLogoutService({required this.authService});

  void reset() {
    _timer?.cancel();
    _timer = Timer(_timeoutDuration, _logout);
  }

  void _logout() {
    authService.logout();
  }

  void cancel() {
    _timer?.cancel();
    _timer = null;
  }
}
