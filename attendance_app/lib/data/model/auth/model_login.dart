/// Represents the authenticated user returned by the login/me endpoints.
class ModelLogin {
  final String userId;
  final String tenantId;
  final String? branchId;
  final String name;
  final String email;
  final String role;
  final String accessToken;
  final String refreshToken;

  const ModelLogin({
    required this.userId,
    required this.tenantId,
    this.branchId,
    required this.name,
    required this.email,
    required this.role,
    required this.accessToken,
    required this.refreshToken,
  });

  factory ModelLogin.fromJson(Map<String, dynamic> json) {
    // Support both flat (from /auth/me) and nested (from /auth/login)
    final data =
        json.containsKey('user') ? json['user'] as Map<String, dynamic> : json;

    return ModelLogin(
      userId: (data['id'] ?? data['user_id'] ?? '').toString(),
      tenantId: (data['tenant_id'] ?? '').toString(),
      branchId: data['branch_id']?.toString(),
      name: data['name'] as String? ?? '',
      email: data['email'] as String? ?? '',
      role: data['role'] as String? ?? '',
      accessToken: json['access_token'] as String? ??
          data['access_token'] as String? ??
          '',
      refreshToken: json['refresh_token'] as String? ??
          data['refresh_token'] as String? ??
          '',
    );
  }

  Map<String, dynamic> toJson() => {
        'user_id': userId,
        'tenant_id': tenantId,
        'branch_id': branchId,
        'name': name,
        'email': email,
        'role': role,
        'access_token': accessToken,
        'refresh_token': refreshToken,
      };

  ModelLogin copyWith({
    String? userId,
    String? tenantId,
    String? branchId,
    String? name,
    String? email,
    String? role,
    String? accessToken,
    String? refreshToken,
  }) {
    return ModelLogin(
      userId: userId ?? this.userId,
      tenantId: tenantId ?? this.tenantId,
      branchId: branchId ?? this.branchId,
      name: name ?? this.name,
      email: email ?? this.email,
      role: role ?? this.role,
      accessToken: accessToken ?? this.accessToken,
      refreshToken: refreshToken ?? this.refreshToken,
    );
  }

  @override
  String toString() => 'ModelLogin(userId: $userId, email: $email, role: $role)';
}
