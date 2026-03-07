import 'package:dartz/dartz.dart';
import '../../data/model/essential/failure.dart';
import '../../data/model/essential/model_response_api.dart';
import '../../res/constants/endpoints.dart';
import '../../services/api_service.dart';

class BranchApi {
  final ApiService apiService;
  BranchApi({required this.apiService});

  Future<Either<Failure, ModelResponseApi<List<dynamic>>>> list() async {
    try {
      final res = await apiService.dio.get(Endpoints.branchList);
      return Right(ModelResponseApi.fromJson(res.data, (j) => j as List));
    } on Exception catch (e) {
      return Left(ServerFailure(message: e.toString()));
    }
  }

  Future<Either<Failure, ModelResponseApi<Map<String, dynamic>>>> detail(
      int id) async {
    try {
      final res = await apiService.dio
          .get(Endpoints.branchDetail.replaceAll('{id}', '$id'));
      return Right(ModelResponseApi.fromJson(
          res.data, (j) => j as Map<String, dynamic>));
    } on Exception catch (e) {
      return Left(ServerFailure(message: e.toString()));
    }
  }
}
