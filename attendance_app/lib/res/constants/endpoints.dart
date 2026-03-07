class Endpoints {
  Endpoints._();

  // ─── Auth ────────────────────────────────────────────────────────────────
  static const String login = '/auth/login';
  static const String logout = '/auth/logout';
  static const String refreshToken = '/auth/refresh';
  static const String me = '/auth/me';

  // ─── Face ────────────────────────────────────────────────────────────────
  static const String faceRegister = '/face/register';
  static const String faceUpdate = '/face/update';
  static const String faceStatus = '/face/status';
  static const String faceSync = '/face/sync';

  // ─── Attendance ──────────────────────────────────────────────────────────
  static const String attendanceCheckIn = '/attendance/check-in';
  static const String attendanceCheckOut = '/attendance/check-out';
  static const String attendanceToday = '/attendance/today';
  static const String attendanceHistory = '/attendance/history';
  static const String attendanceSessions = '/attendance/sessions';

  // ─── Leave ───────────────────────────────────────────────────────────────
  static const String leaveList = '/leave';
  static const String leaveRequest = '/leave/request';
  static const String leaveApprove = '/leave/{id}/approve';
  static const String leaveReject = '/leave/{id}/reject';
  static const String leaveBalance = '/leave/balance';
  static const String leaveTypes = '/leave/types';

  // ─── Reports ─────────────────────────────────────────────────────────────
  static const String reportDaily = '/reports/daily';
  static const String reportMonthly = '/reports/monthly';
  static const String reportSummary = '/reports/summary';
  static const String reportExportPdf = '/reports/export/pdf';
  static const String reportExportExcel = '/reports/export/excel';

  // ─── Branch ──────────────────────────────────────────────────────────────
  static const String branchList = '/branches';
  static const String branchDetail = '/branches/{id}';
}
