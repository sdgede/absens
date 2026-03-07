import 'package:dartz/dartz.dart';

import '../../data/model/essential/failure.dart';
import '../../data/remote/auth_api.dart';
import '../../services/auth_service.dart';
import '../interfaces/i_auth_repository.dart';

class AuthRepositoryImpl implements IAuthRepository {
  final AuthApi authApi;
  final AuthService authService;

  AuthRepositoryImpl({required this.authApi, required this.authService});

  @override
  Future<Either<Failure, Map<String, dynamic>>> login({
    required String email,
    required String password,
  }) async {
    final result = await authApi.login(email: email, password: password);
    return result.fold(
      Left.new,
      (res) async {
        if (res.success && res.data != null) {
          await authService.saveTokens(
            accessToken: res.data!['access_token'] as String,
            refreshToken: res.data!['refresh_token'] as String,
          );
          return Right(res.data!);
        }
        return Left(AuthFailure(message: res.message));
      },
    );
  }

  @override
  Future<Either<Failure, void>> logout() async {
    await authApi.logout();
    await authService.logout();
    return const Right(null);
  }

  @override
  Future<Either<Failure, bool>> refreshToken() async {
    final token = await authService.getRefreshToken();
    if (token == null) return const Right(false);
    final result = await authApi.refreshToken(token);
    return result.fold(
      (f) => Left(f),
      (res) async {
        if (res.success && res.data != null) {
          await authService.saveTokens(
            accessToken: res.data!['access_token'] as String,
            refreshToken: res.data!['refresh_token'] as String,
          );
          return const Right(true);
        }
        return const Right(false);
      },
    );
  }

  @override
  Future<Either<Failure, Map<String, dynamic>>> getProfile() async {
    final result = await authApi.me();
    return result.fold(Left.new, (res) => Right(res.data ?? {}));
  }
}
