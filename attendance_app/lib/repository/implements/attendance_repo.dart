import 'package:dartz/dartz.dart';

import '../../data/model/attendance/model_attendance.dart';
import '../../data/model/attendance/model_attendance_session.dart';
import '../../data/model/essential/failure.dart';
import '../../data/remote/attendance_api.dart';
import '../interfaces/attendance_int.dart';

class AttendanceRepo implements AttendanceInt {
  final AttendanceApi _api;
  AttendanceRepo({required AttendanceApi api}) : _api = api;

  @override
  Future<Either<Failure, ModelAttendance>> checkIn({
    required String userId,
    required double confidence,
    required double livenessScore,
    required double lat,
    required double lng,
    required String sessionId,
  }) =>
      _api.checkIn(
        userId: userId,
        confidence: confidence,
        livenessScore: livenessScore,
        lat: lat,
        lng: lng,
        sessionId: sessionId,
      );

  @override
  Future<Either<Failure, ModelAttendance>> checkOut({
    required String userId,
    required double lat,
    required double lng,
    required String sessionId,
  }) =>
      _api.checkOut(userId: userId, lat: lat, lng: lng, sessionId: sessionId);

  @override
  Future<Either<Failure, ModelAttendanceToday>> getTodayStatus(
          String userId) =>
      _api.getTodayStatus(userId);

  @override
  Future<Either<Failure, List<ModelAttendance>>> getHistory(
    String userId, {
    int page = 1,
    String? startDate,
    String? endDate,
  }) =>
      _api.getHistory(userId,
          page: page, startDate: startDate, endDate: endDate);

  @override
  Future<Either<Failure, List<ModelAttendanceSession>>> getSessions(
    String branchId,
    String date,
  ) =>
      _api.getSessions(branchId, date);
}
