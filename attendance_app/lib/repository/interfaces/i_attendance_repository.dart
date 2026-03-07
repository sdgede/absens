import 'package:dartz/dartz.dart';
import '../../data/model/essential/failure.dart';

abstract class IAttendanceRepository {
  Future<Either<Failure, Map<String, dynamic>>> checkIn(
      Map<String, dynamic> data);
  Future<Either<Failure, Map<String, dynamic>>> checkOut(
      Map<String, dynamic> data);
  Future<Either<Failure, Map<String, dynamic>>> getToday();
  Future<Either<Failure, List<dynamic>>> getHistory(
      Map<String, dynamic> params);
  Future<Either<Failure, List<dynamic>>> getSessions();
}
