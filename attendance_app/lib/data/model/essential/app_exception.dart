/// Custom application exceptions that map from DioException.
/// All network errors are transformed into one of these types
/// before reaching the BLoC layer.
sealed class AppException implements Exception {
  final String message;
  final int? statusCode;
  const AppException({required this.message, this.statusCode});

  @override
  String toString() => message;
}

/// No internet connection or host unreachable.
class NetworkException extends AppException {
  const NetworkException({super.message = 'No internet connection.'});
}

/// Request timed out (connect or receive).
class TimeoutException extends AppException {
  const TimeoutException({super.message = 'The request timed out.'});
}

/// Server returned 401 — token invalid or expired.
class UnauthorizedException extends AppException {
  const UnauthorizedException({super.message = 'Unauthorized. Please log in again.'})
      : super(statusCode: 401);
}

/// Server returned 403 — user lacks permission.
class ForbiddenException extends AppException {
  const ForbiddenException({super.message = 'You do not have permission for this action.'})
      : super(statusCode: 403);
}

/// Server returned 404.
class NotFoundException extends AppException {
  const NotFoundException({super.message = 'The requested resource was not found.'})
      : super(statusCode: 404);
}

/// Server returned 422 — validation error.
class ValidationException extends AppException {
  final Map<String, dynamic>? errors;
  const ValidationException({
    super.message = 'Validation failed.',
    this.errors,
  }) : super(statusCode: 422);
}

/// Server returned 5xx.
class ServerException extends AppException {
  const ServerException({
    super.message = 'A server error occurred. Please try again later.',
    super.statusCode,
  });
}

/// Any other unexpected error.
class UnknownException extends AppException {
  const UnknownException({super.message = 'An unexpected error occurred.'});
}
