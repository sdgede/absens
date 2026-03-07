import 'package:flutter/material.dart';

/// Brand / semantic colour tokens.
/// Import this file in [ThemeColors] or directly in widgets.
class BaseColors {
  BaseColors._();

  // ─── Primary ──────────────────────────────────────────────────────────────
  static const Color primaryBlue = Color(0xFF1E6FD9);
  static const Color secondaryBlue = Color(0xFF5BA4F5);

  // ─── Semantic ─────────────────────────────────────────────────────────────
  static const Color success = Color(0xFF27AE60);
  static const Color warning = Color(0xFFF2994A);
  static const Color error = Color(0xFFEB5757);
  static const Color info = Color(0xFF2D9CDB);

  // ─── Background / Surface ─────────────────────────────────────────────────
  static const Color background = Color(0xFFF5F7FA);
  static const Color backgroundDark = Color(0xFF121826);

  static const Color surface = Color(0xFFFFFFFF);
  static const Color surfaceDark = Color(0xFF1E2535);

  // ─── Text ─────────────────────────────────────────────────────────────────
  static const Color textPrimary = Color(0xFF1A1F36);
  static const Color textPrimaryDark = Color(0xFFE8EDF5);

  static const Color textSecondary = Color(0xFF6B7280);
  static const Color textSecondaryDark = Color(0xFF9CA3AF);

  // ─── Misc ────────────────────────────────────────────────────────────────
  static const Color divider = Color(0xFFE5E7EB);
  static const Color dividerDark = Color(0xFF374151);
}
