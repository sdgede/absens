import 'package:dartz/dartz.dart';

import '../model/essential/failure.dart';
import '../model/essential/model_response_api.dart';
import '../model/face/user_embedding.dart';
import '../../res/constants/endpoints.dart';
import '../../services/api_service.dart';

class FaceApi {
  final ApiService apiService;
  FaceApi({required this.apiService});

  /// Register a face embedding for a user.
  Future<Either<Failure, ModelResponseApi<void>>> registerFace(
    String userId,
    List<double> embedding,
  ) async {
    try {
      final res = await apiService.dio.post(
        Endpoints.faceRegister,
        data: {'user_id': userId, 'embedding': embedding},
      );
      return Right(ModelResponseApi.fromJson(res.data, null));
    } on Exception catch (e) {
      return Left(ServerFailure(message: e.toString()));
    }
  }

  /// Update an existing face embedding for a user.
  Future<Either<Failure, ModelResponseApi<void>>> updateFace(
    String userId,
    List<double> embedding,
  ) async {
    try {
      final res = await apiService.dio.put(
        Endpoints.faceUpdate,
        data: {'user_id': userId, 'embedding': embedding},
      );
      return Right(ModelResponseApi.fromJson(res.data, null));
    } on Exception catch (e) {
      return Left(ServerFailure(message: e.toString()));
    }
  }

  /// Get face registration status for a user.
  Future<Either<Failure, ModelResponseApi<Map<String, dynamic>>>> getFaceStatus(
    String userId,
  ) async {
    try {
      final res = await apiService.dio
          .get(Endpoints.faceStatus, queryParameters: {'user_id': userId});
      return Right(ModelResponseApi.fromJson(
          res.data, (j) => j as Map<String, dynamic>));
    } on Exception catch (e) {
      return Left(ServerFailure(message: e.toString()));
    }
  }

  /// Download all face embeddings for a tenant (for on-device matching).
  Future<Either<Failure, List<UserEmbedding>>> syncEmbeddings(
    String tenantId,
  ) async {
    try {
      final res = await apiService.dio.get(
        Endpoints.faceSync,
        queryParameters: {'tenant_id': tenantId},
      );
      final data = res.data['data'] as List<dynamic>? ?? [];
      return Right(
        data
            .map((e) => UserEmbedding.fromJson(e as Map<String, dynamic>))
            .toList(),
      );
    } on Exception catch (e) {
      return Left(ServerFailure(message: e.toString()));
    }
  }
}
