import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../../../res/colors/base_colors.dart';

/// Draws a face guide oval, a translucent mask outside the oval,
/// and an instruction label below the oval.
class FaceOverlayPainter extends CustomPainter {
  final String instruction;
  final bool faceDetected;
  final Color overlayColor;

  const FaceOverlayPainter({
    required this.instruction,
    this.faceDetected = false,
    this.overlayColor = const Color(0x99000000),
  });

  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width / 2, size.height * 0.42);
    final ovalRect = Rect.fromCenter(
      center: center,
      width: size.width * 0.70,
      height: size.height * 0.45,
    );

    // ── Mask: full canvas minus oval cut-out ─────────────────────────────
    final maskPath = Path()
      ..addRect(Rect.fromLTWH(0, 0, size.width, size.height))
      ..addOval(ovalRect)
      ..fillType = PathFillType.evenOdd;
    canvas.drawPath(maskPath, Paint()..color = overlayColor);

    // ── Oval border ───────────────────────────────────────────────────────
    canvas.drawOval(
      ovalRect,
      Paint()
        ..style = PaintingStyle.stroke
        ..strokeWidth = 3.0
        ..color = faceDetected ? BaseColors.success : Colors.white,
    );

    // ── Corner accent dots ────────────────────────────────────────────────
    final dotPaint = Paint()
      ..color = faceDetected ? BaseColors.success : BaseColors.primaryBlue
      ..style = PaintingStyle.fill;

    for (final angleDeg in [0.0, 90.0, 180.0, 270.0]) {
      final rad = angleDeg * math.pi / 180;
      final dx = center.dx + (ovalRect.width / 2) * 0.85 * math.cos(rad);
      final dy = center.dy + (ovalRect.height / 2) * 0.85 * math.sin(rad);
      canvas.drawCircle(Offset(dx, dy), 6, dotPaint);
    }

    // ── Instruction label ─────────────────────────────────────────────────
    final textPainter = TextPainter(
      text: TextSpan(
        text: instruction,
        style: const TextStyle(
          color: Colors.white,
          fontSize: 16,
          fontWeight: FontWeight.w600,
          shadows: [Shadow(color: Colors.black54, blurRadius: 6)],
        ),
      ),
      textAlign: TextAlign.center,
      textDirection: TextDirection.ltr,
    )..layout(maxWidth: size.width - 40);

    textPainter.paint(
      canvas,
      Offset(
        (size.width - textPainter.width) / 2,
        ovalRect.bottom + 20,
      ),
    );
  }

  @override
  bool shouldRepaint(FaceOverlayPainter oldDelegate) =>
      oldDelegate.instruction != instruction ||
      oldDelegate.faceDetected != faceDetected;
}
