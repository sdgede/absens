import 'package:dartz/dartz.dart';
import 'package:dio/dio.dart';

import '../../data/model/auth/model_login.dart';
import '../../data/model/essential/app_exception.dart';
import '../../data/model/essential/failure.dart';
import '../../data/remote/auth_api.dart';
import '../../services/auth_service.dart';
import '../interfaces/auth_int.dart';

class AuthRepo implements AuthInt {
  final AuthApi _authApi;
  final AuthService _authService;

  AuthRepo({required AuthApi authApi, required AuthService authService})
      : _authApi = authApi,
        _authService = authService;

  @override
  Future<Either<Failure, ModelLogin>> login({
    required String email,
    required String password,
  }) async {
    try {
      final res = await _authApi.login(email: email, password: password);
      if (res.success && res.data != null) {
        await _authService.saveUserData(res.data!);
        return Right(res.data!);
      }
      return Left(AuthFailure(message: res.message));
    } on DioException catch (e) {
      return Left(_mapDio(e));
    } catch (e) {
      return Left(UnknownFailure(message: e.toString()));
    }
  }

  @override
  Future<Either<Failure, void>> logout() async {
    try {
      await _authApi.logout();
    } catch (_) {
      // Best-effort server logout; always clear local session
    }
    await _authService.logout();
    return const Right(null);
  }

  @override
  Future<Either<Failure, ModelLogin>> refreshToken(String token) async {
    try {
      final res = await _authApi.refreshToken(token);
      if (res.success && res.data != null) {
        await _authService.saveUserData(res.data!);
        return Right(res.data!);
      }
      return Left(AuthFailure(message: res.message));
    } on DioException catch (e) {
      return Left(_mapDio(e));
    } catch (e) {
      return Left(UnknownFailure(message: e.toString()));
    }
  }

  @override
  Future<Either<Failure, ModelLogin>> getMe() async {
    try {
      final res = await _authApi.getMe();
      if (res.success && res.data != null) return Right(res.data!);
      return Left(AuthFailure(message: res.message));
    } on DioException catch (e) {
      return Left(_mapDio(e));
    } catch (e) {
      return Left(UnknownFailure(message: e.toString()));
    }
  }

  // ── Helpers ───────────────────────────────────────────────────────────────

  Failure _mapDio(DioException e) {
    final appEx = e.error;
    if (appEx is AppException) {
      if (appEx is NetworkException) {
        return NetworkFailure(message: appEx.message);
      }
      if (appEx is UnauthorizedException || appEx is ForbiddenException) {
        return AuthFailure(message: appEx.message);
      }
      return ServerFailure(
        message: appEx.message,
        statusCode: appEx.statusCode,
      );
    }
    return UnknownFailure(message: e.message ?? 'Unknown error');
  }
}
