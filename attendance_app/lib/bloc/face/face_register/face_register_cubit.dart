import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:image/image.dart' as img;

import '../../../data/remote/face_api.dart';
import '../../../services/face_recognition_service.dart';
import 'face_register_state.dart';

/// 5-step face registration flow orchestrator.
///
/// Usage:
/// ```dart
/// cubit.startCapture();
/// // on each shutter press:
/// await cubit.captureStep(croppedFaceImage);
/// ```
class FaceRegisterCubit extends Cubit<FaceRegisterState> {
  final FaceApi _faceApi;
  final FaceRecognitionService _recognitionService;
  final String userId;

  static const _totalSteps = 5;
  static const _instructions = [
    'Hadap lurus ke kamera',
    'Gerakkan kepala sedikit ke kiri',
    'Gerakkan kepala sedikit ke kanan',
    'Tengadahkan kepala ke atas',
    'Tundukkan kepala ke bawah',
  ];

  final List<img.Image> _capturedImages = [];

  FaceRegisterCubit({
    required FaceApi faceApi,
    required FaceRecognitionService recognitionService,
    required this.userId,
  })  : _faceApi = faceApi,
        _recognitionService = recognitionService,
        super(const FaceRegisterInitial());

  // ── Public Methods ────────────────────────────────────────────────────────

  /// Begin the 5-step capture process.
  void startCapture() {
    _capturedImages.clear();
    emit(FaceRegisterCapturing(
      current: 1,
      total: _totalSteps,
      instruction: _instructions[0],
    ));
  }

  /// Called when the user captures a face image at the current step.
  Future<void> captureStep(img.Image faceImage) async {
    final state = this.state;
    if (state is! FaceRegisterCapturing) return;

    _capturedImages.add(faceImage);

    if (state.current < _totalSteps) {
      // Advance to next step
      final next = state.current + 1;
      emit(FaceRegisterCapturing(
        current: next,
        total: _totalSteps,
        instruction: _instructions[next - 1],
      ));
    } else {
      // All 5 captured — process
      await _processAndUpload();
    }
  }

  // ── Processing ────────────────────────────────────────────────────────────

  Future<void> _processAndUpload() async {
    emit(const FaceRegisterProcessing());

    try {
      // Average embedding across 5 captures for robustness
      final embeddings = _capturedImages
          .map(_recognitionService.extractEmbedding)
          .toList();

      final averaged = _averageEmbeddings(embeddings);

      // Upload to server
      final result = await _faceApi.registerFace(userId, averaged);

      result.fold(
        (failure) => emit(FaceRegisterError(failure.message)),
        (_) => emit(const FaceRegisterSuccess()),
      );
    } catch (e) {
      emit(FaceRegisterError('Gagal memproses wajah: $e'));
    }
  }

  List<double> _averageEmbeddings(List<List<double>> embeddings) {
    final len = embeddings.first.length;
    final result = List<double>.filled(len, 0.0);
    for (final emb in embeddings) {
      for (int i = 0; i < len; i++) {
        result[i] += emb[i];
      }
    }
    return result.map((v) => v / embeddings.length).toList();
  }

  void reset() {
    _capturedImages.clear();
    emit(const FaceRegisterInitial());
  }
}
