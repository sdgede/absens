import 'package:equatable/equatable.dart';

import '../../../data/model/face/match_result.dart';
import '../../../services/liveness_service.dart';

// ── Events ────────────────────────────────────────────────────────────────────

sealed class FaceScanEvent extends Equatable {
  const FaceScanEvent();

  @override
  List<Object?> get props => [];
}

class StartScan extends FaceScanEvent {
  const StartScan();
}

class LivenessStateChanged extends FaceScanEvent {
  final LivenessState livenessState;
  const LivenessStateChanged(this.livenessState);

  @override
  List<Object?> get props => [livenessState];
}

class FaceDetected extends FaceScanEvent {
  final MatchResult matchResult;
  const FaceDetected(this.matchResult);

  @override
  List<Object?> get props => [matchResult];
}

class ScanFailed extends FaceScanEvent {
  final String reason;
  const ScanFailed(this.reason);

  @override
  List<Object?> get props => [reason];
}

// ── States ────────────────────────────────────────────────────────────────────

sealed class FaceScanState extends Equatable {
  const FaceScanState();

  @override
  List<Object?> get props => [];
}

class FaceScanIdle extends FaceScanState {
  const FaceScanIdle();
}

class FaceScanCheckingGps extends FaceScanState {
  const FaceScanCheckingGps();
}

class FaceScanLivenessInProgress extends FaceScanState {
  final LivenessState livenessState;
  const FaceScanLivenessInProgress(this.livenessState);

  @override
  List<Object?> get props => [livenessState];
}

class FaceScanProcessing extends FaceScanState {
  const FaceScanProcessing();
}

class FaceScanMatched extends FaceScanState {
  final MatchResult matchResult;
  const FaceScanMatched(this.matchResult);

  @override
  List<Object?> get props => [matchResult];
}

class FaceScanNotMatched extends FaceScanState {
  const FaceScanNotMatched();
}

class FaceScanFailed extends FaceScanState {
  final String reason;
  const FaceScanFailed(this.reason);

  @override
  List<Object?> get props => [reason];
}
