import 'dart:math' as math;

import 'package:image/image.dart' as img;
import 'package:tflite_flutter/tflite_flutter.dart';

import '../data/model/face/match_result.dart';
import '../data/model/face/user_embedding.dart';
import '../res/constants/config.dart';

/// On-device face recognition using FaceNet via TFLite.
///
/// Model: assets/models/facenet.tflite
/// Input:  [1, 160, 160, 3]  float32  (pixels normalized -1 to 1)
/// Output: [1, 128]          float32  (L2-normalised embedding)
class FaceRecognitionService {
  static const _modelPath = 'assets/models/facenet.tflite';
  static const _inputSize = 160;

  Interpreter? _interpreter;

  // ── Lifecycle ─────────────────────────────────────────────────────────────

  Future<void> initialize() async {
    _interpreter = await Interpreter.fromAsset(
      _modelPath,
      options: InterpreterOptions()..threads = 2,
    );
  }

  void dispose() {
    _interpreter?.close();
    _interpreter = null;
  }

  bool get isReady => _interpreter != null;

  // ── Embedding extraction ──────────────────────────────────────────────────

  /// Preprocess, run inference, and return the 128-dim embedding.
  List<double> extractEmbedding(img.Image faceImage) {
    assert(_interpreter != null, 'Call initialize() first');

    // 1. Resize to 160 × 160
    final resized = img.copyResize(
      faceImage,
      width: _inputSize,
      height: _inputSize,
      interpolation: img.Interpolation.linear,
    );

    // 2. Build [1, 160, 160, 3] input tensor, normalised to [-1, 1]
    final input = List.generate(
      1,
      (_) => List.generate(
        _inputSize,
        (y) => List.generate(
          _inputSize,
          (x) {
            final pixel = resized.getPixel(x, y);
            return [
              (pixel.r / 127.5) - 1.0,
              (pixel.g / 127.5) - 1.0,
              (pixel.b / 127.5) - 1.0,
            ];
          },
        ),
      ),
    );

    // 3. Prepare output buffer [1, 128]
    final outputBuffer =
        List.generate(1, (_) => List<double>.filled(128, 0.0));

    // 4. Run inference
    _interpreter!.run(input, outputBuffer);

    final embedding = outputBuffer[0];

    // 5. L2-normalise
    return _l2Normalize(embedding);
  }

  // ── Comparison ────────────────────────────────────────────────────────────

  /// Returns a similarity score [0.0 – 1.0] using Euclidean distance.
  /// Score is: 1 / (1 + distance)
  double compareFaces(List<double> e1, List<double> e2) {
    final dist = _euclideanDistance(e1, e2);
    return 1.0 / (1.0 + dist);
  }

  /// Find the best-matching user among [users].
  /// [isMatch] is true when confidence >= [AppConfig.faceConfidenceThreshold].
  MatchResult findBestMatch(
    List<double> input,
    List<UserEmbedding> users,
  ) {
    if (users.isEmpty) return const MatchResult.noMatch();

    UserEmbedding? bestUser;
    double bestConfidence = 0.0;

    for (final user in users) {
      final confidence = compareFaces(input, user.embedding);
      if (confidence > bestConfidence) {
        bestConfidence = confidence;
        bestUser = user;
      }
    }

    final isMatch = bestConfidence >= AppConfig.faceConfidenceThreshold;

    return MatchResult(
      user: isMatch ? bestUser : null,
      confidence: bestConfidence,
      isMatch: isMatch,
    );
  }

  // ── Helpers ───────────────────────────────────────────────────────────────

  double _euclideanDistance(List<double> a, List<double> b) {
    double sum = 0.0;
    for (int i = 0; i < a.length; i++) {
      final diff = a[i] - b[i];
      sum += diff * diff;
    }
    return math.sqrt(sum);
  }

  List<double> _l2Normalize(List<double> v) {
    double norm = 0.0;
    for (final x in v) {
      norm += x * x;
    }
    norm = math.sqrt(norm);
    if (norm == 0) return v;
    return v.map((x) => x / norm).toList();
  }
}
