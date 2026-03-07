import 'dart:async';

import 'package:google_mlkit_face_detection/google_mlkit_face_detection.dart';

/// All possible liveness check states.
enum LivenessState {
  waiting,
  instructBlink,
  blinkDetected,
  instructHeadMove,
  headMoved,
  success,
  failed,
  timeout,
}

/// Stateful liveness detection driven by a stream of [InputImage] frames.
///
/// Detection sequence:
///   1. Blink: both eyes < 0.25 → open > 0.75
///   2. Head yaw: |yaw| > 20°
///   After both → emit [LivenessState.success]
///   Timeout after [frameLimit] frames (≈5 s @ 12 fps).
class LivenessService {
  static const int frameLimit = 60;
  static const double _eyeClosedThreshold = 0.25;
  static const double _eyeOpenThreshold = 0.75;
  static const double _yawThreshold = 20.0;

  final FaceDetector _detector;

  // ── Internal state ────────────────────────────────────────────────────────
  bool _eyesClosed = false;
  bool _blinkDone = false;
  bool _headMoveDone = false;
  int _frameCount = 0;

  LivenessService()
      : _detector = FaceDetector(
          options: FaceDetectorOptions(
            enableClassification: true,
            enableTracking: false,
            minFaceSize: 0.2,
            performanceMode: FaceDetectorMode.accurate,
          ),
        );

  /// Drives the liveness state machine from a camera frame stream.
  Stream<LivenessState> detectLiveness(Stream<InputImage> imageStream) async* {
    reset();
    yield LivenessState.waiting;

    await for (final image in imageStream) {
      _frameCount++;

      // Timeout
      if (_frameCount > frameLimit) {
        yield LivenessState.timeout;
        return;
      }

      final faces = await _detector.processImage(image);
      if (faces.isEmpty || faces.length > 1) continue;

      final face = faces.first;
      final leftEye = face.leftEyeOpenProbability ?? 1.0;
      final rightEye = face.rightEyeOpenProbability ?? 1.0;
      final yaw = face.headEulerAngleY ?? 0.0;

      // ── Step 1: Blink detection ──────────────────────────────────────────
      if (!_blinkDone) {
        yield LivenessState.instructBlink;

        if (!_eyesClosed &&
            leftEye < _eyeClosedThreshold &&
            rightEye < _eyeClosedThreshold) {
          _eyesClosed = true;
        }

        if (_eyesClosed &&
            leftEye > _eyeOpenThreshold &&
            rightEye > _eyeOpenThreshold) {
          _blinkDone = true;
          _eyesClosed = false;
          yield LivenessState.blinkDetected;
        }
        continue;
      }

      // ── Step 2: Head movement detection ───────────────────────────────────
      if (!_headMoveDone) {
        yield LivenessState.instructHeadMove;

        if (yaw.abs() > _yawThreshold) {
          _headMoveDone = true;
          yield LivenessState.headMoved;
        }
        continue;
      }

      // ── Both steps passed ─────────────────────────────────────────────────
      yield LivenessState.success;
      return;
    }
  }

  /// Reset internal state for a new liveness check.
  void reset() {
    _eyesClosed = false;
    _blinkDone = false;
    _headMoveDone = false;
    _frameCount = 0;
  }

  /// Release ML Kit resources.
  Future<void> dispose() => _detector.close();
}
