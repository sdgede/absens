import 'package:equatable/equatable.dart';

sealed class AuthEvent extends Equatable {
  const AuthEvent();

  @override
  List<Object?> get props => [];
}

/// Check if there's a valid cached session on app start.
class AuthCheckRequested extends AuthEvent {
  const AuthCheckRequested();
}

/// User submitted login form.
class LoginRequested extends AuthEvent {
  final String email;
  final String password;
  const LoginRequested({required this.email, required this.password});

  @override
  List<Object?> get props => [email, password];
}

/// User tapped logout.
class LogoutRequested extends AuthEvent {
  const LogoutRequested();
}

/// ApiService detected a 401 that could not be refreshed.
class TokenRefreshed extends AuthEvent {
  final bool success;
  const TokenRefreshed({required this.success});

  @override
  List<Object?> get props => [success];
}
