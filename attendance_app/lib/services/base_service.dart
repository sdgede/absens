import 'package:dartz/dartz.dart';

import '../data/model/essential/failure.dart';

/// Base class for all API/data services.
/// Provides a shared [handleResponse] utility to convert
/// raw API results into [Either<Failure, T>].
abstract class BaseService {
  /// Wraps a remote call in a try/catch and returns Either.
  ///
  /// Usage:
  /// ```dart
  /// return handleResponse(() async {
  ///   final res = await dio.get('/endpoint');
  ///   return ModelFromJson.fromJson(res.data);
  /// });
  /// ```
  Future<Either<Failure, T>> handleResponse<T>(
    Future<T> Function() call,
  ) async {
    try {
      final result = await call();
      return Right(result);
    } on ServerFailure catch (e) {
      return Left(e);
    } on NetworkFailure catch (e) {
      return Left(e);
    } catch (e) {
      return Left(UnknownFailure(message: e.toString()));
    }
  }
}
