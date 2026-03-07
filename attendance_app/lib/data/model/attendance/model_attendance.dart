/// Type of attendance record.
enum AttendanceType { checkin, checkout }

/// Attendance status.
enum AttendanceStatus { present, late, absent, leave, sick }

class ModelAttendance {
  final String id;
  final String userId;
  final String? sessionId;
  final AttendanceType type;
  final AttendanceStatus status;
  final double? lat;
  final double? lng;
  final double? faceConfidence;
  final double? livenessScore;
  final DateTime timestamp;
  final String? note;

  const ModelAttendance({
    required this.id,
    required this.userId,
    this.sessionId,
    required this.type,
    required this.status,
    this.lat,
    this.lng,
    this.faceConfidence,
    this.livenessScore,
    required this.timestamp,
    this.note,
  });

  factory ModelAttendance.fromJson(Map<String, dynamic> json) {
    return ModelAttendance(
      id: (json['id'] ?? '').toString(),
      userId: (json['user_id'] ?? '').toString(),
      sessionId: json['session_id']?.toString(),
      type: json['type'] == 'checkout'
          ? AttendanceType.checkout
          : AttendanceType.checkin,
      status: _parseStatus(json['status'] as String? ?? 'present'),
      lat: (json['lat'] ?? json['latitude'])?.toDouble(),
      lng: (json['lng'] ?? json['longitude'])?.toDouble(),
      faceConfidence: (json['face_confidence'])?.toDouble(),
      livenessScore: (json['liveness_score'])?.toDouble(),
      timestamp: DateTime.parse(
          json['timestamp'] as String? ?? DateTime.now().toIso8601String()),
      note: json['note'] as String?,
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'user_id': userId,
        'session_id': sessionId,
        'type': type.name,
        'status': status.name,
        'lat': lat,
        'lng': lng,
        'face_confidence': faceConfidence,
        'liveness_score': livenessScore,
        'timestamp': timestamp.toIso8601String(),
        'note': note,
      };

  static AttendanceStatus _parseStatus(String raw) {
    return AttendanceStatus.values.firstWhere(
      (e) => e.name == raw,
      orElse: () => AttendanceStatus.present,
    );
  }
}

/// Summary of today's attendance (both check-in and check-out combined).
class ModelAttendanceToday {
  final ModelAttendance? checkIn;
  final ModelAttendance? checkOut;
  final AttendanceStatus? status;
  final bool isCheckedIn;
  final bool isCheckedOut;

  const ModelAttendanceToday({
    this.checkIn,
    this.checkOut,
    this.status,
  })  : isCheckedIn = checkIn != null,
        isCheckedOut = checkOut != null;

  factory ModelAttendanceToday.fromJson(Map<String, dynamic> json) {
    final checkInJson = json['check_in'] as Map<String, dynamic>?;
    final checkOutJson = json['check_out'] as Map<String, dynamic>?;
    return ModelAttendanceToday(
      checkIn:
          checkInJson != null ? ModelAttendance.fromJson(checkInJson) : null,
      checkOut:
          checkOutJson != null ? ModelAttendance.fromJson(checkOutJson) : null,
      status: checkInJson != null
          ? ModelAttendance._parseStatus(
              checkInJson['status'] as String? ?? 'present')
          : null,
    );
  }
}
