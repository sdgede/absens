import 'package:dartz/dartz.dart';

import '../../data/model/attendance/model_attendance.dart';
import '../../data/model/attendance/model_attendance_session.dart';
import '../../data/model/essential/failure.dart';

/// Contract for all attendance data operations.
abstract class AttendanceInt {
  Future<Either<Failure, ModelAttendance>> checkIn({
    required String userId,
    required double confidence,
    required double livenessScore,
    required double lat,
    required double lng,
    required String sessionId,
  });

  Future<Either<Failure, ModelAttendance>> checkOut({
    required String userId,
    required double lat,
    required double lng,
    required String sessionId,
  });

  Future<Either<Failure, ModelAttendanceToday>> getTodayStatus(String userId);

  Future<Either<Failure, List<ModelAttendance>>> getHistory(
    String userId, {
    int page,
    String? startDate,
    String? endDate,
  });

  Future<Either<Failure, List<ModelAttendanceSession>>> getSessions(
    String branchId,
    String date,
  );
}
