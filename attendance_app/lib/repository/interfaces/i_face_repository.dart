import 'package:dartz/dartz.dart';
import '../../data/model/essential/failure.dart';

abstract class IFaceRepository {
  Future<Either<Failure, void>> registerFace(List<double> embedding);
  Future<Either<Failure, void>> updateFace(List<double> embedding);
  Future<Either<Failure, Map<String, dynamic>>> getFaceStatus();
  Future<Either<Failure, List<double>?>> syncEmbedding();
}
