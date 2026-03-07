import 'package:dartz/dartz.dart';
import '../../data/model/essential/failure.dart';

abstract class IAuthRepository {
  Future<Either<Failure, Map<String, dynamic>>> login({
    required String email,
    required String password,
  });
  Future<Either<Failure, void>> logout();
  Future<Either<Failure, bool>> refreshToken();
  Future<Either<Failure, Map<String, dynamic>>> getProfile();
}
