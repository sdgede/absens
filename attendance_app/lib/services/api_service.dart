import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import 'package:dio/io.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:pretty_dio_logger/pretty_dio_logger.dart';

import '../data/model/essential/app_exception.dart';
import '../res/constants/config.dart';
import 'security_service.dart';

/// Central Dio HTTP client with:
/// - Certificate pinning (production only)
/// - Auth bearer token injection
/// - Automatic 401 token refresh with single retry
/// - Typed error interceptor (DioException → AppException)
/// - PrettyDioLogger (debug mode only)
class ApiService {
  late final Dio _dio;
  final FlutterSecureStorage secureStorage;

  /// Key used by external callers to trigger a manual logout.
  /// Set this after AuthBloc is initialised.
  Function()? onLoggedOut;

  Dio get dio => _dio;

  ApiService({required this.secureStorage});

  Future<void> init() async {
    _dio = Dio(
      BaseOptions(
        baseUrl: AppConfig.baseUrl,
        connectTimeout: const Duration(seconds: 30),
        receiveTimeout: const Duration(seconds: 30),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      ),
    );

    // ── Certificate pinning (non-web, non-debug) ──────────────────────────
    if (!kIsWeb) {
      (_dio.httpClientAdapter as IOHttpClientAdapter).createHttpClient =
          () async => SecurityService.createHttpClientWithPinning();
    }

    // ── Auth interceptor ──────────────────────────────────────────────────
    _dio.interceptors.add(_buildAuthInterceptor());

    // ── Error interceptor ─────────────────────────────────────────────────
    _dio.interceptors.add(_buildErrorInterceptor());

    // ── Pretty logger (debug only) ────────────────────────────────────────
    if (kDebugMode) {
      _dio.interceptors.add(
        PrettyDioLogger(
          requestHeader: true,
          requestBody: true,
          responseBody: true,
          responseHeader: false,
          error: true,
          compact: true,
        ),
      );
    }
  }

  // ── Auth Interceptor ─────────────────────────────────────────────────────

  InterceptorsWrapper _buildAuthInterceptor() {
    return InterceptorsWrapper(
      onRequest: (options, handler) async {
        final token =
            await secureStorage.read(key: 'access_token');
        if (token != null) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        return handler.next(options);
      },
      onError: (DioException error, handler) async {
        if (error.response?.statusCode == 401) {
          // Attempt silent token refresh (once)
          final refreshed = await _tryRefreshToken();
          if (refreshed) {
            // Retry the original request with the new token
            final newToken =
                await secureStorage.read(key: 'access_token');
            error.requestOptions.headers['Authorization'] =
                'Bearer $newToken';
            try {
              final cloned = await _dio.fetch(error.requestOptions);
              return handler.resolve(cloned);
            } catch (_) {
              // Retry also failed — logout
            }
          }
          // Refresh failed → logout
          onLoggedOut?.call();
        }
        return handler.next(error);
      },
    );
  }

  Future<bool> _tryRefreshToken() async {
    try {
      final refreshToken =
          await secureStorage.read(key: 'refresh_token');
      if (refreshToken == null) return false;

      // Use a clean Dio instance (no interceptors) to avoid infinite loop
      final refreshDio = Dio(BaseOptions(baseUrl: AppConfig.baseUrl));
      final response = await refreshDio.post(
        '/auth/refresh',
        data: {'refresh_token': refreshToken},
      );

      final data = response.data as Map<String, dynamic>;
      if (data['success'] == true) {
        final newAccess = data['data']['access_token'] as String;
        final newRefresh = data['data']['refresh_token'] as String;
        await secureStorage.write(key: 'access_token', value: newAccess);
        await secureStorage.write(key: 'refresh_token', value: newRefresh);
        return true;
      }
      return false;
    } catch (_) {
      return false;
    }
  }

  // ── Error Interceptor ────────────────────────────────────────────────────

  InterceptorsWrapper _buildErrorInterceptor() {
    return InterceptorsWrapper(
      onError: (DioException e, handler) {
        AppException mapped;

        if (e.type == DioExceptionType.connectionTimeout ||
            e.type == DioExceptionType.receiveTimeout ||
            e.type == DioExceptionType.sendTimeout) {
          mapped = const TimeoutException();
        } else if (e.type == DioExceptionType.connectionError) {
          mapped = const NetworkException();
        } else {
          final status = e.response?.statusCode;
          switch (status) {
            case 401:
              mapped = const UnauthorizedException();
            case 403:
              mapped = const ForbiddenException();
            case 404:
              mapped = const NotFoundException();
            case 422:
              final errors =
                  e.response?.data?['errors'] as Map<String, dynamic>?;
              mapped = ValidationException(
                message: e.response?.data?['message'] as String? ??
                    'Validation failed.',
                errors: errors,
              );
            case >= 500:
              mapped = ServerException(
                message: e.response?.data?['message'] as String? ??
                    'Server error.',
                statusCode: status,
              );
            default:
              mapped = UnknownException(
                message: e.message ?? 'An unexpected error occurred.',
              );
          }
        }

        // Attach as extra so downstream can inspect it
        return handler.next(
          DioException(
            requestOptions: e.requestOptions,
            response: e.response,
            type: e.type,
            error: mapped,
            message: mapped.message,
          ),
        );
      },
    );
  }
}
