class AppRoutes {
  AppRoutes._();

  // ─── Auth ─────────────────────────────────────────────────────────────────
  static const String splash = '/';
  static const String login = '/login';
  static const String biometric = '/biometric';

  // ─── Home / Dashboard ─────────────────────────────────────────────────────
  static const String home = '/home';
  static const String dashboard = '/dashboard';

  // ─── Attendance ───────────────────────────────────────────────────────────
  static const String attendance = '/attendance';
  static const String checkIn = '/attendance/check-in';
  static const String checkOut = '/attendance/check-out';
  static const String attendanceHistory = '/attendance/history';
  static const String attendanceDetail = '/attendance/detail/:id';

  // ─── Face Recognition ─────────────────────────────────────────────────────
  static const String faceRegistration = '/face/registration';
  static const String faceVerification = '/face/verification';

  // ─── Leave ────────────────────────────────────────────────────────────────
  static const String leaveList = '/leave';
  static const String leaveRequest = '/leave/request';
  static const String leaveDetail = '/leave/detail/:id';
  static const String leaveApproval = '/leave/approval';

  // ─── Reports ──────────────────────────────────────────────────────────────
  static const String reports = '/reports';
  static const String reportDaily = '/reports/daily';
  static const String reportMonthly = '/reports/monthly';

  // ─── Profile ──────────────────────────────────────────────────────────────
  static const String profile = '/profile';
  static const String profileEdit = '/profile/edit';
  static const String changePassword = '/profile/change-password';

  // ─── Settings ─────────────────────────────────────────────────────────────
  static const String settings = '/settings';
  static const String notifications = '/settings/notifications';

  // ─── Misc ─────────────────────────────────────────────────────────────────
  static const String notFound = '/404';
}
