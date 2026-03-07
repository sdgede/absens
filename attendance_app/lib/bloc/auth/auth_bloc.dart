import 'package:flutter_bloc/flutter_bloc.dart';

import '../../repository/interfaces/auth_int.dart';
import '../../services/auth_service.dart';
import 'auth_event.dart';
import 'auth_state.dart';

class AuthBloc extends Bloc<AuthEvent, AuthState> {
  final AuthInt _authRepo;
  final AuthService _authService;

  AuthBloc({
    required AuthInt authRepo,
    required AuthService authService,
  })  : _authRepo = authRepo,
        _authService = authService,
        super(const AuthInitial()) {
    on<AuthCheckRequested>(_onAuthCheck);
    on<LoginRequested>(_onLogin);
    on<LogoutRequested>(_onLogout);
    on<TokenRefreshed>(_onTokenRefreshed);
  }

  // ── Handlers ─────────────────────────────────────────────────────────────

  Future<void> _onAuthCheck(
    AuthCheckRequested event,
    Emitter<AuthState> emit,
  ) async {
    emit(const AuthLoading());
    if (!_authService.isLoggedIn()) {
      return emit(const AuthUnauthenticated());
    }

    // Try restoring user from secure cache; fall back to /me
    final cached = await _authService.getUserData();
    if (cached != null) {
      return emit(AuthAuthenticated(cached));
    }

    final result = await _authRepo.getMe();
    result.fold(
      (f) => emit(const AuthUnauthenticated()),
      (user) => emit(AuthAuthenticated(user)),
    );
  }

  Future<void> _onLogin(
    LoginRequested event,
    Emitter<AuthState> emit,
  ) async {
    emit(const AuthLoading());
    final result = await _authRepo.login(
      email: event.email,
      password: event.password,
    );
    result.fold(
      (f) => emit(AuthError(f.message)),
      (user) => emit(AuthAuthenticated(user)),
    );
  }

  Future<void> _onLogout(
    LogoutRequested event,
    Emitter<AuthState> emit,
  ) async {
    emit(const AuthLoading());
    await _authRepo.logout();
    emit(const AuthUnauthenticated());
  }

  Future<void> _onTokenRefreshed(
    TokenRefreshed event,
    Emitter<AuthState> emit,
  ) async {
    if (!event.success) {
      await _authService.logout();
      emit(const AuthUnauthenticated());
    }
  }
}
