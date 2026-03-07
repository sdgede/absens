import 'package:equatable/equatable.dart';

/// State for the face registration flow (5-step capture process).
sealed class FaceRegisterState extends Equatable {
  const FaceRegisterState();

  @override
  List<Object?> get props => [];
}

/// Initial state before registration starts.
class FaceRegisterInitial extends FaceRegisterState {
  const FaceRegisterInitial();
}

/// User is capturing step [current] of [total].
class FaceRegisterCapturing extends FaceRegisterState {
  final int current;
  final int total;
  final String instruction;

  const FaceRegisterCapturing({
    required this.current,
    required this.total,
    required this.instruction,
  });

  @override
  List<Object?> get props => [current, total, instruction];
}

/// Captured images are being processed (embedding extraction).
class FaceRegisterProcessing extends FaceRegisterState {
  const FaceRegisterProcessing();
}

/// Registration completed successfully.
class FaceRegisterSuccess extends FaceRegisterState {
  const FaceRegisterSuccess();
}

/// Registration failed with a message.
class FaceRegisterError extends FaceRegisterState {
  final String message;
  const FaceRegisterError(this.message);

  @override
  List<Object?> get props => [message];
}
