import 'package:dartz/dartz.dart';
import '../../data/model/essential/failure.dart';
import '../../data/remote/report_api.dart';
import '../interfaces/i_report_repository.dart';

class ReportRepositoryImpl implements IReportRepository {
  final ReportApi reportApi;
  ReportRepositoryImpl({required this.reportApi});

  @override
  Future<Either<Failure, Map<String, dynamic>>> getDaily(
      Map<String, dynamic> params) async {
    final r = await reportApi.daily(params);
    return r.fold(Left.new, (res) => Right(res.data ?? {}));
  }

  @override
  Future<Either<Failure, Map<String, dynamic>>> getMonthly(
      Map<String, dynamic> params) async {
    final r = await reportApi.monthly(params);
    return r.fold(Left.new, (res) => Right(res.data ?? {}));
  }

  @override
  Future<Either<Failure, Map<String, dynamic>>> getSummary(
      Map<String, dynamic> params) async {
    final r = await reportApi.summary(params);
    return r.fold(Left.new, (res) => Right(res.data ?? {}));
  }

  @override
  Future<Either<Failure, void>> exportPdf(Map<String, dynamic> params) async {
    final r = await reportApi.exportPdf(params);
    return r.fold(Left.new, (_) => const Right(null));
  }

  @override
  Future<Either<Failure, void>> exportExcel(
      Map<String, dynamic> params) async {
    final r = await reportApi.exportExcel(params);
    return r.fold(Left.new, (_) => const Right(null));
  }
}
