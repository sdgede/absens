import 'package:camera/camera.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:google_mlkit_face_detection/google_mlkit_face_detection.dart';
import 'package:image/image.dart' as img;

import '../../../bloc/face/face_register/face_register_cubit.dart';
import '../../../bloc/face/face_register/face_register_state.dart';
import '../../../res/colors/base_colors.dart';
import '../../widgets/essential/face_overlay_painter.dart';
import '../../widgets/form/button_primary.dart';

class RegisterFacePage extends StatefulWidget {
  final String userId;
  const RegisterFacePage({super.key, required this.userId});

  @override
  State<RegisterFacePage> createState() => _RegisterFacePageState();
}

class _RegisterFacePageState extends State<RegisterFacePage> {
  CameraController? _cameraController;
  bool _isCameraReady = false;
  bool _faceDetected = false;

  final _mlkitDetector = FaceDetector(
    options: FaceDetectorOptions(
      enableClassification: false,
      minFaceSize: 0.25,
      performanceMode: FaceDetectorMode.fast,
    ),
  );

  @override
  void initState() {
    super.initState();
    _initCamera();
    context.read<FaceRegisterCubit>().startCapture();
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

    // Live face detection for button activation
    _cameraController!.startImageStream(_detectFace);
  }

  Future<void> _detectFace(CameraImage camImage) async {
    final inputImage = _cameraImageToInputImage(camImage);
    if (inputImage == null) return;
    final faces = await _mlkitDetector.processImage(inputImage);
    if (mounted) {
      setState(() => _faceDetected = faces.length == 1);
    }
  }

  Future<void> _capture() async {
    if (_cameraController == null || !_faceDetected) return;
    await _cameraController!.stopImageStream();

    final file = await _cameraController!.takePicture();
    final bytes = await file.readAsBytes();
    final image = img.decodeImage(bytes);

    if (image == null) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Gagal mengambil gambar.')),
        );
      }
      return;
    }

    await context.read<FaceRegisterCubit>().captureStep(image);

    // Restart stream for next step
    final state = context.read<FaceRegisterCubit>().state;
    if (state is FaceRegisterCapturing) {
      _cameraController!.startImageStream(_detectFace);
    }
  }

  InputImage? _cameraImageToInputImage(CameraImage image) {
    try {
      final WriteBuffer allBytes = WriteBuffer();
      for (final Plane plane in image.planes) {
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

  @override
  void dispose() {
    _cameraController?.dispose();
    _mlkitDetector.close();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: BlocConsumer<FaceRegisterCubit, FaceRegisterState>(
        listener: (context, state) {
          if (state is FaceRegisterSuccess) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('Wajah berhasil didaftarkan!'),
                backgroundColor: BaseColors.success,
              ),
            );
            Navigator.of(context).pop();
          }
          if (state is FaceRegisterError) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(state.message),
                backgroundColor: BaseColors.error,
              ),
            );
          }
        },
        builder: (context, state) {
          return Stack(
            children: [
              // ── Camera preview ──────────────────────────────────────────
              if (_isCameraReady)
                Positioned.fill(child: CameraPreview(_cameraController!))
              else
                const Center(child: CircularProgressIndicator()),

              // ── Face oval overlay ───────────────────────────────────────
              Positioned.fill(
                child: CustomPaint(
                  painter: FaceOverlayPainter(
                    instruction: state is FaceRegisterCapturing
                        ? state.instruction
                        : state is FaceRegisterProcessing
                            ? 'Memproses...'
                            : '',
                    faceDetected: _faceDetected,
                  ),
                ),
              ),

              // ── Top bar ─────────────────────────────────────────────────
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
                          icon: const Icon(Icons.arrow_back,
                              color: Colors.white),
                          onPressed: () => Navigator.pop(context),
                        ),
                        const Expanded(
                          child: Text(
                            'Daftar Wajah',
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

              // ── Progress + capture button ────────────────────────────────
              Positioned(
                bottom: 0,
                left: 0,
                right: 0,
                child: SafeArea(
                  child: Container(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        // Step dots
                        if (state is FaceRegisterCapturing) ...[
                          _StepDots(
                              current: state.current, total: state.total),
                          const SizedBox(height: 8),
                          Text(
                            'Langkah ${state.current} dari ${state.total}',
                            style: const TextStyle(color: Colors.white70),
                          ),
                          const SizedBox(height: 20),
                          ButtonPrimary(
                            label: 'Ambil Foto',
                            onPressed: _faceDetected ? _capture : null,
                          ),
                        ],
                        if (state is FaceRegisterProcessing)
                          const Column(
                            children: [
                              CircularProgressIndicator(color: Colors.white),
                              SizedBox(height: 12),
                              Text('Memproses wajah...',
                                  style: TextStyle(color: Colors.white)),
                            ],
                          ),
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
}

class _StepDots extends StatelessWidget {
  final int current;
  final int total;

  const _StepDots({required this.current, required this.total});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: List.generate(total, (index) {
        final active = index < current;
        return AnimatedContainer(
          duration: const Duration(milliseconds: 300),
          margin: const EdgeInsets.symmetric(horizontal: 4),
          width: active ? 24 : 10,
          height: 10,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(5),
            color: active ? BaseColors.primaryBlue : Colors.white38,
          ),
        );
      }),
    );
  }
}
