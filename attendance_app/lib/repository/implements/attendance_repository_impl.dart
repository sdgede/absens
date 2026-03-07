import 'package:dartz/dartz.dart';

import '../../data/model/essential/failure.dart';
import '../../data/remote/attendance_api.dart';
import '../../services/gps_service.dart';
import '../interfaces/i_attendance_repository.dart';

class AttendanceRepositoryImpl implements IAttendanceRepository {
  final AttendanceApi attendanceApi;
  final GpsService gpsService;

  AttendanceRepositoryImpl({
    required this.attendanceApi,
    required this.gpsService,
  });

  @override
  Future<Either<Failure, Map<String, dynamic>>> checkIn(
      Map<String, dynamic> data) async {
    final result = await attendanceApi.checkIn(data);
    return result.fold(Left.new, (res) => Right(res.data ?? {}));
  }

  @override
  Future<Either<Failure, Map<String, dynamic>>> checkOut(
      Map<String, dynamic> data) async {
    final result = await attendanceApi.checkOut(data);
    return result.fold(Left.new, (res) => Right(res.data ?? {}));
  }

  @override
  Future<Either<Failure, Map<String, dynamic>>> getToday() async {
    final result = await attendanceApi.today();
    return result.fold(Left.new, (res) => Right(res.data ?? {}));
  }

  @override
  Future<Either<Failure, List<dynamic>>> getHistory(
      Map<String, dynamic> params) async {
    final result = await attendanceApi.history(params);
    return result.fold(Left.new, (res) => Right(res.data ?? []));
  }

  @override
  Future<Either<Failure, List<dynamic>>> getSessions() async {
    final result = await attendanceApi.sessions();
    return result.fold(Left.new, (res) => Right(res.data ?? []));
  }
}
