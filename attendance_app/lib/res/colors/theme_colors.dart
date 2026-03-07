import 'package:flutter/material.dart';

import 'base_colors.dart';

class ThemeColors {
  ThemeColors._();

  // ─── Light Theme ──────────────────────────────────────────────────────────
  static ThemeData get light => ThemeData(
        useMaterial3: true,
        brightness: Brightness.light,
        colorScheme: const ColorScheme.light(
          primary: BaseColors.primaryBlue,
          secondary: BaseColors.secondaryBlue,
          error: BaseColors.error,
          surface: BaseColors.surface,
          onPrimary: Colors.white,
          onSecondary: Colors.white,
          onSurface: BaseColors.textPrimary,
          onError: Colors.white,
        ),
        scaffoldBackgroundColor: BaseColors.background,
        appBarTheme: const AppBarTheme(
          backgroundColor: BaseColors.primaryBlue,
          foregroundColor: Colors.white,
          elevation: 0,
          centerTitle: true,
          titleTextStyle: TextStyle(
            color: Colors.white,
            fontSize: 18,
            fontWeight: FontWeight.w600,
            letterSpacing: 0.2,
          ),
        ),
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(
            backgroundColor: BaseColors.primaryBlue,
            foregroundColor: Colors.white,
            minimumSize: const Size.fromHeight(48),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            textStyle: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w600,
            ),
          ),
        ),
        inputDecorationTheme: InputDecorationTheme(
          filled: true,
          fillColor: BaseColors.surface,
          contentPadding:
              const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: BaseColors.divider),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: BaseColors.divider),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide:
                const BorderSide(color: BaseColors.primaryBlue, width: 2),
          ),
          errorBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: BaseColors.error),
          ),
          labelStyle: const TextStyle(color: BaseColors.textSecondary),
          hintStyle: const TextStyle(color: BaseColors.textSecondary),
        ),
        dividerColor: BaseColors.divider,
      );

  // ─── Dark Theme ───────────────────────────────────────────────────────────
  static ThemeData get dark => ThemeData(
        useMaterial3: true,
        brightness: Brightness.dark,
        colorScheme: const ColorScheme.dark(
          primary: BaseColors.primaryBlue,
          secondary: BaseColors.secondaryBlue,
          error: BaseColors.error,
          surface: BaseColors.surfaceDark,
          onPrimary: Colors.white,
          onSecondary: Colors.white,
          onSurface: BaseColors.textPrimaryDark,
          onError: Colors.white,
        ),
        scaffoldBackgroundColor: BaseColors.backgroundDark,
        appBarTheme: const AppBarTheme(
          backgroundColor: BaseColors.surfaceDark,
          foregroundColor: BaseColors.textPrimaryDark,
          elevation: 0,
          centerTitle: true,
          titleTextStyle: TextStyle(
            color: BaseColors.textPrimaryDark,
            fontSize: 18,
            fontWeight: FontWeight.w600,
            letterSpacing: 0.2,
          ),
        ),
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(
            backgroundColor: BaseColors.primaryBlue,
            foregroundColor: Colors.white,
            minimumSize: const Size.fromHeight(48),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            textStyle: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w600,
            ),
          ),
        ),
        inputDecorationTheme: InputDecorationTheme(
          filled: true,
          fillColor: BaseColors.surfaceDark,
          contentPadding:
              const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: BaseColors.dividerDark),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: BaseColors.dividerDark),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide:
                const BorderSide(color: BaseColors.primaryBlue, width: 2),
          ),
          errorBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: BaseColors.error),
          ),
          labelStyle: const TextStyle(color: BaseColors.textSecondaryDark),
          hintStyle: const TextStyle(color: BaseColors.textSecondaryDark),
        ),
        dividerColor: BaseColors.dividerDark,
      );
}
