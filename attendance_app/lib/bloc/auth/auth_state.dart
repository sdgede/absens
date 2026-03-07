import 'package:equatable/equatable.dart';

import '../../data/model/auth/model_login.dart';

sealed class AuthState extends Equatable {
  const AuthState();

  @override
  List<Object?> get props => [];
}

/// Not yet determined — shown during cold start check.
class AuthInitial extends AuthState {
  const AuthInitial();
}

/// Auth operation in progress.
class AuthLoading extends AuthState {
  const AuthLoading();
}

/// User is fully authenticated.
class AuthAuthenticated extends AuthState {
  final ModelLogin user;
  const AuthAuthenticated(this.user);

  @override
  List<Object?> get props => [user.userId, user.accessToken];
}

/// No valid session — show login.
class AuthUnauthenticated extends AuthState {
  const AuthUnauthenticated();
}

/// Auth operation failed.
class AuthError extends AuthState {
  final String message;
  const AuthError(this.message);

  @override
  List<Object?> get props => [message];
}
