/// Represents a registered user with their stored face embedding.
class UserEmbedding {
  final String userId;
  final String name;
  final List<double> embedding;

  const UserEmbedding({
    required this.userId,
    required this.name,
    required this.embedding,
  });

  factory UserEmbedding.fromJson(Map<String, dynamic> json) {
    return UserEmbedding(
      userId: (json['user_id'] ?? json['userId'] ?? '').toString(),
      name: json['name'] as String? ?? '',
      embedding: (json['embedding'] as List<dynamic>)
          .map((e) => (e as num).toDouble())
          .toList(),
    );
  }

  Map<String, dynamic> toJson() => {
        'user_id': userId,
        'name': name,
        'embedding': embedding,
      };
}
