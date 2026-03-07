import 'dart:async';

import 'package:camera/camera.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:google_mlkit_face_detection/google_mlkit_face_detection.dart';
import 'package:image/image.dart' as img;
import 'package:lottie/lottie.dart';

import '../../../bloc/face/face_scan/face_scan_bloc.dart';
import '../../../bloc/face/face_scan/face_scan_event_state.dart';
import '../../../data/model/face/match_result.dart';
import '../../../res/colors/base_colors.dart';
import '../../../services/liveness_service.dart';
import '../../widgets/essential/face_overlay_painter.dart';

class ScanFacePage extends StatefulWidget {
  const ScanFacePage({super.key});

  @override
  State<ScanFacePage> createState() => _ScanFacePageState();
}

class _ScanFacePageState extends State<ScanFacePage> {
  CameraController? _cameraController;
  bool _isCameraReady = false;
  bool _livenessComplete = false;

  final StreamController<InputImage> _frameStreamController =
      StreamController<InputImage>.broadcast();

  StreamSubscription<LivenessState>? _livenessSub;
  LivenessService? _livenessService;

  @override
  void initState() {
    super.initState();
    _initCamera().then((_) => _startLivenessListener());
    context.read<FaceScanBloc>().add(const StartScan());
  }

  Future<void> _initCamera() async {
    final cameras = await availableCameras();
    final front = cameras.firstWhere(
      (c) => c.lensDirection == CameraLensDirection.front,
      orElse: () => cameras.first,
    );
    _cameraController = CameraController(
      front,
      ResolutionPreset.high,
      enableAudio: false,
    );
    await _cameraController!.initialize();
    if (mounted) setState(() => _isCameraReady = true);
    _cameraController!.startImageStream(_onCameraFrame);
  }

  void _startLivenessListener() {
    _livenessService = LivenessService();
    final bloc = context.read<FaceScanBloc>();

    _livenessSub = _livenessService!
        .detectLiveness(_frameStreamController.stream)
        .listen((livenessState) {
      if (!mounted) return;
      bloc.add(LivenessStateChanged(livenessState));

      if (livenessState == LivenessState.success && !_livenessComplete) {
        _livenessComplete = true;
        _captureAndMatch(bloc);
      }
    });
  }

  void _onCameraFrame(CameraImage camImage) {
    if (_livenessComplete) return;
    final inputImage = _toInputImage(camImage);
    if (inputImage != null && !_frameStreamController.isClosed) {
      _frameStreamController.add(inputImage);
    }
  }

  Future<void> _captureAndMatch(FaceScanBloc bloc) async {
    await _cameraController?.stopImageStream();

    final file = await _cameraController?.takePicture();
    if (file == null) {
      bloc.add(const ScanFailed('Gagal mengambil snapshot.'));
      return;
    }
    final bytes = await file.readAsBytes();
    final image = img.decodeImage(bytes);
    if (image == null) {
      bloc.add(const ScanFailed('Gagal mendekode gambar.'));
      return;
    }

    final matchResult = await bloc.matchFace(image);
    bloc.add(FaceDetected(matchResult));
  }

  InputImage? _toInputImage(CameraImage image) {
    try {
      final allBytes = WriteBuffer();
      for (final plane in image.planes) {
        allBytes.putUint8List(plane.bytes);
      }
      return InputImage.fromBytes(
        bytes: allBytes.done().buffer.asUint8List(),
        metadata: InputImageMetadata(
          size: Size(image.width.toDouble(), image.height.toDouble()),
          rotation: InputImageRotation.rotation0deg,
          format: InputImageFormat.bgra8888,
          bytesPerRow: image.planes.first.bytesPerRow,
        ),
      );
    } catch (_) {
      return null;
    }
  }

  String _instructionFor(FaceScanState state) {
    if (state is FaceScanLivenessInProgress) {
      return switch (state.livenessState) {
        LivenessState.waiting => 'Posisikan wajah dalam lingkaran',
        LivenessState.instructBlink => 'Kedipkan mata Anda',
        LivenessState.blinkDetected => '✓ Kedipan terdeteksi!',
        LivenessState.instructHeadMove => 'Gerakkan kepala ke kiri',
        LivenessState.headMoved => '✓ Gerakan terdeteksi!',
        LivenessState.success => 'Memproses...',
        LivenessState.failed => 'Verifikasi gagal',
        LivenessState.timeout => 'Waktu habis',
      };
    }
    if (state is FaceScanCheckingGps) return 'Memeriksa lokasi GPS...';
    if (state is FaceScanProcessing) return 'Mencocokkan wajah...';
    if (state is FaceScanMatched) {
      return '✓ Selamat datang, ${state.matchResult.user?.name ?? "User"}!';
    }
    if (state is FaceScanNotMatched) return '✗ Wajah tidak dikenali';
    if (state is FaceScanFailed) return state.reason;
    return 'Menginisialisasi...';
  }

  @override
  void dispose() {
    _cameraController?.dispose();
    _livenessSub?.cancel();
    _livenessService?.dispose();
    _frameStreamController.close();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: BlocConsumer<FaceScanBloc, FaceScanState>(
        listener: (context, state) {
          if (state is FaceScanFailed) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(state.reason),
                backgroundColor: BaseColors.error,
                behavior: SnackBarBehavior.floating,
              ),
            );
          }
        },
        builder: (context, state) {
          return Stack(
            children: [
              // ── Camera preview ─────────────────────────────────────────
              if (_isCameraReady)
                Positioned.fill(child: CameraPreview(_cameraController!))
              else
                const Center(
                  child:
                      CircularProgressIndicator(color: Colors.white),
                ),

              // ── Success overlay (Lottie) ───────────────────────────────
              if (state is FaceScanMatched)
                Positioned.fill(
                  child: Container(
                    color: Colors.black87,
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        SizedBox(
                          width: 200,
                          height: 200,
                          child: _buildSuccessAnimation(),
                        ),
                        const SizedBox(height: 16),
                        const Text(
                          'Absensi Berhasil!',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 24,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          state.matchResult.user?.name ?? '',
                          style: const TextStyle(
                              color: Colors.white70, fontSize: 16),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Confidence: ${(state.matchResult.confidence * 100).toStringAsFixed(1)}%',
                          style: const TextStyle(
                              color: Colors.white54, fontSize: 13),
                        ),
                      ],
                    ),
                  ),
                ),

              // ── Face oval overlay (during liveness) ───────────────────
              if (state is! FaceScanMatched)
                Positioned.fill(
                  child: CustomPaint(
                    painter: FaceOverlayPainter(
                      instruction: _instructionFor(state),
                      faceDetected: state is FaceScanLivenessInProgress &&
                          (state.livenessState ==
                                  LivenessState.blinkDetected ||
                              state.livenessState ==
                                  LivenessState.headMoved),
                    ),
                  ),
                ),

              // ── Top bar ────────────────────────────────────────────────
              Positioned(
                top: 0,
                left: 0,
                right: 0,
                child: SafeArea(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 16, vertical: 8),
                    child: Row(
                      children: [
                        IconButton(
                          icon: const Icon(Icons.close, color: Colors.white),
                          onPressed: () => Navigator.pop(context),
                        ),
                        const Expanded(
                          child: Text(
                            'Scan Wajah',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                            textAlign: TextAlign.center,
                          ),
                        ),
                        const SizedBox(width: 48),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildSuccessAnimation() {
    try {
      return Lottie.asset(
        'assets/images/success_animation.json',
        repeat: false,
      );
    } catch (_) {
      return const Icon(Icons.check_circle,
          color: BaseColors.success, size: 120);
    }
  }
}
