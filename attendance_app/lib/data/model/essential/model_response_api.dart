/// Generic API response envelope.
///
/// Matches the standard backend response shape:
/// ```json
/// {
///   "success": true,
///   "message": "OK",
///   "data": { ... },
///   "errors": null
/// }
/// ```
class ModelResponseApi<T> {
  final bool success;
  final String message;
  final T? data;
  final dynamic errors;

  const ModelResponseApi({
    required this.success,
    required this.message,
    this.data,
    this.errors,
  });

  factory ModelResponseApi.fromJson(
    Map<String, dynamic> json,
    T Function(dynamic json)? fromJsonT,
  ) {
    return ModelResponseApi<T>(
      success: json['success'] as bool? ?? false,
      message: json['message'] as String? ?? '',
      data: json['data'] != null && fromJsonT != null
          ? fromJsonT(json['data'])
          : null,
      errors: json['errors'],
    );
  }

  @override
  String toString() =>
      'ModelResponseApi(success: $success, message: $message, data: $data)';
}
