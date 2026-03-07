class ModelAttendanceSession {
  final String id;
  final String tenantId;
  final String branchId;
  final DateTime date;
  final DateTime? checkInStart;
  final DateTime? checkInEnd;
  final DateTime? lateAfter;
  final DateTime? checkOutTime;

  const ModelAttendanceSession({
    required this.id,
    required this.tenantId,
    required this.branchId,
    required this.date,
    this.checkInStart,
    this.checkInEnd,
    this.lateAfter,
    this.checkOutTime,
  });

  factory ModelAttendanceSession.fromJson(Map<String, dynamic> json) {
    DateTime? _parse(String? s) =>
        s != null ? DateTime.tryParse(s) : null;

    return ModelAttendanceSession(
      id: (json['id'] ?? '').toString(),
      tenantId: (json['tenant_id'] ?? '').toString(),
      branchId: (json['branch_id'] ?? '').toString(),
      date: DateTime.parse(
          json['date'] as String? ?? DateTime.now().toIso8601String()),
      checkInStart: _parse(json['check_in_start'] as String?),
      checkInEnd: _parse(json['check_in_end'] as String?),
      lateAfter: _parse(json['late_after'] as String?),
      checkOutTime: _parse(json['check_out_time'] as String?),
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'tenant_id': tenantId,
        'branch_id': branchId,
        'date': date.toIso8601String(),
        'check_in_start': checkInStart?.toIso8601String(),
        'check_in_end': checkInEnd?.toIso8601String(),
        'late_after': lateAfter?.toIso8601String(),
        'check_out_time': checkOutTime?.toIso8601String(),
      };

  /// Is the current time within the check-in window?
  bool get isCheckInWindowOpen {
    final now = DateTime.now();
    if (checkInStart == null || checkInEnd == null) return false;
    return now.isAfter(checkInStart!) && now.isBefore(checkInEnd!);
  }

  /// Is the current check-in considered late?
  bool get isLateNow {
    if (lateAfter == null) return false;
    return DateTime.now().isAfter(lateAfter!);
  }
}
