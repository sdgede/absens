import 'package:dartz/dartz.dart';
import '../../data/model/essential/failure.dart';
import '../../data/model/essential/model_response_api.dart';
import '../../res/constants/endpoints.dart';
import '../../services/api_service.dart';

class ReportApi {
  final ApiService apiService;
  ReportApi({required this.apiService});

  Future<Either<Failure, ModelResponseApi<Map<String, dynamic>>>> daily(
      Map<String, dynamic> params) async {
    try {
      final res = await apiService.dio
          .get(Endpoints.reportDaily, queryParameters: params);
      return Right(ModelResponseApi.fromJson(
          res.data, (j) => j as Map<String, dynamic>));
    } on Exception catch (e) {
      return Left(ServerFailure(message: e.toString()));
    }
  }

  Future<Either<Failure, ModelResponseApi<Map<String, dynamic>>>> monthly(
      Map<String, dynamic> params) async {
    try {
      final res = await apiService.dio
          .get(Endpoints.reportMonthly, queryParameters: params);
      return Right(ModelResponseApi.fromJson(
          res.data, (j) => j as Map<String, dynamic>));
    } on Exception catch (e) {
      return Left(ServerFailure(message: e.toString()));
    }
  }

  Future<Either<Failure, ModelResponseApi<Map<String, dynamic>>>> summary(
      Map<String, dynamic> params) async {
    try {
      final res = await apiService.dio
          .get(Endpoints.reportSummary, queryParameters: params);
      return Right(ModelResponseApi.fromJson(
          res.data, (j) => j as Map<String, dynamic>));
    } on Exception catch (e) {
      return Left(ServerFailure(message: e.toString()));
    }
  }

  Future<Either<Failure, ModelResponseApi<void>>> exportPdf(
      Map<String, dynamic> params) async {
    try {
      final res = await apiService.dio
          .get(Endpoints.reportExportPdf, queryParameters: params);
      return Right(ModelResponseApi.fromJson(res.data, null));
    } on Exception catch (e) {
      return Left(ServerFailure(message: e.toString()));
    }
  }

  Future<Either<Failure, ModelResponseApi<void>>> exportExcel(
      Map<String, dynamic> params) async {
    try {
      final res = await apiService.dio
          .get(Endpoints.reportExportExcel, queryParameters: params);
      return Right(ModelResponseApi.fromJson(res.data, null));
    } on Exception catch (e) {
      return Left(ServerFailure(message: e.toString()));
    }
  }
}
