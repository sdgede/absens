import 'package:dartz/dartz.dart';

import '../../data/model/essential/failure.dart';
import '../../data/remote/face_api.dart';
import '../../services/face_recognition_service.dart';
import '../../services/embedding_sync_service.dart';
import '../interfaces/i_face_repository.dart';

class FaceRepositoryImpl implements IFaceRepository {
  final FaceApi faceApi;
  final FaceRecognitionService faceRecognitionService;
  final EmbeddingSyncService embeddingSyncService;

  FaceRepositoryImpl({
    required this.faceApi,
    required this.faceRecognitionService,
    required this.embeddingSyncService,
  });

  @override
  Future<Either<Failure, void>> registerFace(List<double> embedding) async {
    final result = await faceApi.register(embedding);
    return result.fold(Left.new, (_) async {
      await embeddingSyncService.saveEmbedding(embedding);
      return const Right(null);
    });
  }

  @override
  Future<Either<Failure, void>> updateFace(List<double> embedding) async {
    final result = await faceApi.update(embedding);
    return result.fold(Left.new, (_) async {
      await embeddingSyncService.saveEmbedding(embedding);
      return const Right(null);
    });
  }

  @override
  Future<Either<Failure, Map<String, dynamic>>> getFaceStatus() async {
    final result = await faceApi.status();
    return result.fold(Left.new, (res) => Right(res.data ?? {}));
  }

  @override
  Future<Either<Failure, List<double>?>> syncEmbedding() async {
    final result = await faceApi.sync();
    return result.fold(Left.new, (res) async {
      if (res.data != null && res.data!['embedding'] != null) {
        final raw = (res.data!['embedding'] as List).cast<double>();
        await embeddingSyncService.saveEmbedding(raw);
        return Right(raw);
      }
      return const Right(null);
    });
  }
}
