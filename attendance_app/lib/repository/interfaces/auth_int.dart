import 'package:dartz/dartz.dart';

import '../../data/model/auth/model_login.dart';
import '../../data/model/essential/failure.dart';

/// Contract for all authentication data operations.
abstract class AuthInt {
  /// Authenticate with email + password. Returns user on success.
  Future<Either<Failure, ModelLogin>> login({
    required String email,
    required String password,
  });

  /// Invalidate the current session server-side.
  Future<Either<Failure, void>> logout();

  /// Exchange a refresh token for new access + refresh tokens.
  Future<Either<Failure, ModelLogin>> refreshToken(String token);

  /// Fetch the currently authenticated user's profile.
  Future<Either<Failure, ModelLogin>> getMe();
}
