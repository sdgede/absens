import 'user_embedding.dart';

/// Result from [FaceRecognitionService.findBestMatch].
class MatchResult {
  /// The matched user, or null if no match above threshold.
  final UserEmbedding? user;

  /// Similarity score [0.0 – 1.0]. Higher = more similar.
  final double confidence;

  /// True if [confidence] >= Config.faceConfidenceThreshold.
  final bool isMatch;

  const MatchResult({
    this.user,
    required this.confidence,
    required this.isMatch,
  });

  /// Convenience constructor for a failed match.
  const MatchResult.noMatch()
      : user = null,
        confidence = 0.0,
        isMatch = false;

  @override
  String toString() =>
      'MatchResult(user: ${user?.name}, confidence: ${confidence.toStringAsFixed(3)}, isMatch: $isMatch)';
}
