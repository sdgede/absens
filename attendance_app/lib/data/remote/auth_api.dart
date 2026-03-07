import 'package:dartz/dartz.dart';

import '../model/essential/app_exception.dart';
import '../model/essential/failure.dart';
import '../model/essential/model_response_api.dart';
import '../model/auth/model_login.dart';
import '../../res/constants/endpoints.dart';
import '../../services/api_service.dart';

class AuthApi {
  final ApiService apiService;
  AuthApi({required this.apiService});

  Future<ModelResponseApi<ModelLogin>> login({
    required String email,
    required String password,
  }) async {
    final res = await apiService.dio.post(
      Endpoints.login,
      data: {'email': email, 'password': password},
    );
    return ModelResponseApi.fromJson(
      res.data as Map<String, dynamic>,
      (j) => ModelLogin.fromJson(j as Map<String, dynamic>),
    );
  }

  Future<ModelResponseApi<void>> logout() async {
    final res = await apiService.dio.post(Endpoints.logout);
    return ModelResponseApi.fromJson(res.data as Map<String, dynamic>, null);
  }

  Future<ModelResponseApi<ModelLogin>> refreshToken(String token) async {
    final res = await apiService.dio.post(
      Endpoints.refreshToken,
      data: {'refresh_token': token},
    );
    return ModelResponseApi.fromJson(
      res.data as Map<String, dynamic>,
      (j) => ModelLogin.fromJson(j as Map<String, dynamic>),
    );
  }

  Future<ModelResponseApi<ModelLogin>> getMe() async {
    final res = await apiService.dio.get(Endpoints.me);
    return ModelResponseApi.fromJson(
      res.data as Map<String, dynamic>,
      (j) => ModelLogin.fromJson(j as Map<String, dynamic>),
    );
  }
}
