import 'package:equatable/equatable.dart';

sealed class AttendanceEvent extends Equatable {
  const AttendanceEvent();

  @override
  List<Object?> get props => [];
}

class LoadTodayStatus extends AttendanceEvent {
  final String userId;
  const LoadTodayStatus(this.userId);

  @override
  List<Object?> get props => [userId];
}

class CheckInRequested extends AttendanceEvent {
  final String userId;
  final double confidence;
  final double livenessScore;
  final double lat;
  final double lng;
  final String sessionId;

  const CheckInRequested({
    required this.userId,
    required this.confidence,
    required this.livenessScore,
    required this.lat,
    required this.lng,
    required this.sessionId,
  });

  @override
  List<Object?> get props =>
      [userId, confidence, livenessScore, lat, lng, sessionId];
}

class CheckOutRequested extends AttendanceEvent {
  final String userId;
  final double lat;
  final double lng;
  final String sessionId;

  const CheckOutRequested({
    required this.userId,
    required this.lat,
    required this.lng,
    required this.sessionId,
  });

  @override
  List<Object?> get props => [userId, lat, lng, sessionId];
}

class LoadHistory extends AttendanceEvent {
  final String userId;
  final int page;
  final String? startDate;
  final String? endDate;

  const LoadHistory({
    required this.userId,
    this.page = 1,
    this.startDate,
    this.endDate,
  });

  @override
  List<Object?> get props => [userId, page, startDate, endDate];
}
