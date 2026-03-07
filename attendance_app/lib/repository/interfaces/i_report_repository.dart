import 'package:dartz/dartz.dart';
import '../../data/model/essential/failure.dart';

abstract class IReportRepository {
  Future<Either<Failure, Map<String, dynamic>>> getDaily(
      Map<String, dynamic> params);
  Future<Either<Failure, Map<String, dynamic>>> getMonthly(
      Map<String, dynamic> params);
  Future<Either<Failure, Map<String, dynamic>>> getSummary(
      Map<String, dynamic> params);
  Future<Either<Failure, void>> exportPdf(Map<String, dynamic> params);
  Future<Either<Failure, void>> exportExcel(Map<String, dynamic> params);
}
