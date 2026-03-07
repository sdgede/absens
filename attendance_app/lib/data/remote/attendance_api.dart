import 'package:dartz/dartz.dart';

import '../model/attendance/model_attendance.dart';
import '../model/attendance/model_attendance_session.dart';
import '../model/essential/failure.dart';
import '../model/essential/model_response_api.dart';
import '../../res/constants/endpoints.dart';
import '../../services/api_service.dart';

class AttendanceApi {
  final ApiService apiService;
  AttendanceApi({required this.apiService});

  /// Submit a check-in record.
  Future<Either<Failure, ModelAttendance>> checkIn({
    required String userId,
    required double confidence,
    required double livenessScore,
    required double lat,
    required double lng,
    required String sessionId,
  }) async {
    try {
      final res = await apiService.dio.post(
        Endpoints.checkIn,
        data: {
          'user_id': userId,
          'face_confidence': confidence,
          'liveness_score': livenessScore,
          'lat': lat,
          'lng': lng,
          'session_id': sessionId,
        },
      );
      final wrapped = ModelResponseApi.fromJson(
        res.data as Map<String, dynamic>,
        (j) => ModelAttendance.fromJson(j as Map<String, dynamic>),
      );
      if (wrapped.success && wrapped.data != null) {
        return Right(wrapped.data!);
      }
      return Left(ServerFailure(message: wrapped.message));
    } on Exception catch (e) {
      return Left(ServerFailure(message: e.toString()));
    }
  }

  /// Submit a check-out record.
  Future<Either<Failure, ModelAttendance>> checkOut({
    required String userId,
    required double lat,
    required double lng,
    required String sessionId,
  }) async {
    try {
      final res = await apiService.dio.post(
        Endpoints.checkOut,
        data: {
          'user_id': userId,
          'lat': lat,
          'lng': lng,
          'session_id': sessionId,
        },
      );
      final wrapped = ModelResponseApi.fromJson(
        res.data as Map<String, dynamic>,
        (j) => ModelAttendance.fromJson(j as Map<String, dynamic>),
      );
      if (wrapped.success && wrapped.data != null) {
        return Right(wrapped.data!);
      }
      return Left(ServerFailure(message: wrapped.message));
    } on Exception catch (e) {
      return Left(ServerFailure(message: e.toString()));
    }
  }

  /// Get today's check-in & check-out summary.
  Future<Either<Failure, ModelAttendanceToday>> getTodayStatus(
      String userId) async {
    try {
      final res = await apiService.dio
          .get(Endpoints.today, queryParameters: {'user_id': userId});
      final today = ModelAttendanceToday.fromJson(
          res.data['data'] as Map<String, dynamic>);
      return Right(today);
    } on Exception catch (e) {
      return Left(ServerFailure(message: e.toString()));
    }
  }

  /// Paginated attendance history.
  Future<Either<Failure, List<ModelAttendance>>> getHistory(
    String userId, {
    int page = 1,
    String? startDate,
    String? endDate,
  }) async {
    try {
      final res = await apiService.dio.get(
        Endpoints.history,
        queryParameters: {
          'user_id': userId,
          'page': page,
          if (startDate != null) 'start_date': startDate,
          if (endDate != null) 'end_date': endDate,
        },
      );
      final list = (res.data['data'] as List<dynamic>? ?? [])
          .map((e) => ModelAttendance.fromJson(e as Map<String, dynamic>))
          .toList();
      return Right(list);
    } on Exception catch (e) {
      return Left(ServerFailure(message: e.toString()));
    }
  }

  /// Get attendance sessions for a branch on a specific date.
  Future<Either<Failure, List<ModelAttendanceSession>>> getSessions(
    String branchId,
    String date,
  ) async {
    try {
      final res = await apiService.dio.get(
        Endpoints.sessions,
        queryParameters: {'branch_id': branchId, 'date': date},
      );
      final list = (res.data['data'] as List<dynamic>? ?? [])
          .map((e) =>
              ModelAttendanceSession.fromJson(e as Map<String, dynamic>))
          .toList();
      return Right(list);
    } on Exception catch (e) {
      return Left(ServerFailure(message: e.toString()));
    }
  }
}
