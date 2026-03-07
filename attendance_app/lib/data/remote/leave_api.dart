import 'package:dartz/dartz.dart';
import '../../data/model/essential/failure.dart';
import '../../data/model/essential/model_response_api.dart';
import '../../res/constants/endpoints.dart';
import '../../services/api_service.dart';

class LeaveApi {
  final ApiService apiService;
  LeaveApi({required this.apiService});

  Future<Either<Failure, ModelResponseApi<List<dynamic>>>> list() async {
    try {
      final res = await apiService.dio.get(Endpoints.leaveList);
      return Right(ModelResponseApi.fromJson(res.data, (j) => j as List));
    } on Exception catch (e) {
      return Left(ServerFailure(message: e.toString()));
    }
  }

  Future<Either<Failure, ModelResponseApi<Map<String, dynamic>>>> request(
      Map<String, dynamic> data) async {
    try {
      final res =
          await apiService.dio.post(Endpoints.leaveRequest, data: data);
      return Right(ModelResponseApi.fromJson(
          res.data, (j) => j as Map<String, dynamic>));
    } on Exception catch (e) {
      return Left(ServerFailure(message: e.toString()));
    }
  }

  Future<Either<Failure, ModelResponseApi<void>>> approve(int id) async {
    try {
      final res = await apiService.dio
          .post(Endpoints.leaveApprove.replaceAll('{id}', '$id'));
      return Right(ModelResponseApi.fromJson(res.data, null));
    } on Exception catch (e) {
      return Left(ServerFailure(message: e.toString()));
    }
  }

  Future<Either<Failure, ModelResponseApi<void>>> reject(int id) async {
    try {
      final res = await apiService.dio
          .post(Endpoints.leaveReject.replaceAll('{id}', '$id'));
      return Right(ModelResponseApi.fromJson(res.data, null));
    } on Exception catch (e) {
      return Left(ServerFailure(message: e.toString()));
    }
  }

  Future<Either<Failure, ModelResponseApi<Map<String, dynamic>>>> balance() async {
    try {
      final res = await apiService.dio.get(Endpoints.leaveBalance);
      return Right(ModelResponseApi.fromJson(
          res.data, (j) => j as Map<String, dynamic>));
    } on Exception catch (e) {
      return Left(ServerFailure(message: e.toString()));
    }
  }

  Future<Either<Failure, ModelResponseApi<List<dynamic>>>> types() async {
    try {
      final res = await apiService.dio.get(Endpoints.leaveTypes);
      return Right(ModelResponseApi.fromJson(res.data, (j) => j as List));
    } on Exception catch (e) {
      return Left(ServerFailure(message: e.toString()));
    }
  }
}
