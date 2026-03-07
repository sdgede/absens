/// Encapsulates all possible failure types returned by repositories
/// and services via Either<Failure, T>.
abstract class Failure {
  final String message;
  const Failure({required this.message});
}

/// 4xx / 5xx HTTP errors from the server.
class ServerFailure extends Failure {
  final int? statusCode;
  const ServerFailure({required super.message, this.statusCode});
}

/// No internet / connection timeout.
class NetworkFailure extends Failure {
  const NetworkFailure({required super.message});
}

/// Local cache / storage read-write failure.
class CacheFailure extends Failure {
  const CacheFailure({required super.message});
}

/// Authentication errors (invalid token, expired, etc.)
class AuthFailure extends Failure {
  const AuthFailure({required super.message});
}

/// Face recognition / liveness failures.
class FaceFailure extends Failure {
  const FaceFailure({required super.message});
}

/// Location / GPS failures.
class LocationFailure extends Failure {
  const LocationFailure({required super.message});
}

/// Catch-all for unexpected errors.
class UnknownFailure extends Failure {
  const UnknownFailure({required super.message});
}
