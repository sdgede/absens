import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:image/image.dart' as img;

import '../../../data/model/face/match_result.dart';
import '../../../services/embedding_sync_service.dart';
import '../../../services/face_recognition_service.dart';
import '../../../services/gps_service.dart';
import '../../../services/liveness_service.dart';
import '../../../res/constants/config.dart';
import 'face_scan_event_state.dart';

class FaceScanBloc extends Bloc<FaceScanEvent, FaceScanState> {
  final FaceRecognitionService _recognitionService;
  final EmbeddingSyncService _syncService;
  final GpsService _gpsService;
  final LivenessService _livenessService;

  FaceScanBloc({
    required FaceRecognitionService recognitionService,
    required EmbeddingSyncService syncService,
    required GpsService gpsService,
    required LivenessService livenessService,
  })  : _recognitionService = recognitionService,
        _syncService = syncService,
        _gpsService = gpsService,
        _livenessService = livenessService,
        super(const FaceScanIdle()) {
    on<StartScan>(_onStartScan);
    on<LivenessStateChanged>(_onLivenessChanged);
    on<FaceDetected>(_onFaceDetected);
    on<ScanFailed>(_onScanFailed);
  }

  Future<void> _onStartScan(
    StartScan event,
    Emitter<FaceScanState> emit,
  ) async {
    // 1. Validate GPS location
    emit(const FaceScanCheckingGps());
    final position = await _gpsService.getCurrentPosition();
    if (position == null) {
      return emit(const FaceScanFailed('GPS tidak tersedia. Aktifkan lokasi.'));
    }

    // 2. Begin liveness check
    _livenessService.reset();
    emit(const FaceScanLivenessInProgress(LivenessState.waiting));
  }

  Future<void> _onLivenessChanged(
    LivenessStateChanged event,
    Emitter<FaceScanState> emit,
  ) async {
    if (event.livenessState == LivenessState.timeout) {
      return emit(const FaceScanFailed('Liveness timeout. Coba lagi.'));
    }
    if (event.livenessState == LivenessState.failed) {
      return emit(const FaceScanFailed('Verifikasi liveness gagal.'));
    }
    emit(FaceScanLivenessInProgress(event.livenessState));
  }

  Future<void> _onFaceDetected(
    FaceDetected event,
    Emitter<FaceScanState> emit,
  ) async {
    emit(const FaceScanProcessing());
    emit(
      event.matchResult.isMatch
          ? FaceScanMatched(event.matchResult)
          : const FaceScanNotMatched(),
    );
  }

  Future<void> _onScanFailed(
    ScanFailed event,
    Emitter<FaceScanState> emit,
  ) async {
    emit(FaceScanFailed(event.reason));
  }

  /// Called by the UI after liveness passes with a captured face image.
  /// Runs matching against locally-cached embeddings.
  Future<MatchResult> matchFace(img.Image faceImage) async {
    final embedding = _recognitionService.extractEmbedding(faceImage);
    final users = await _syncService.loadLocalEmbeddings();

    if (users.isEmpty) {
      return const MatchResult.noMatch();
    }
    return _recognitionService.findBestMatch(embedding, users);
  }
}
