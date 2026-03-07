import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:shared_preferences/shared_preferences.dart';

// ─── State ────────────────────────────────────────────────────────────────────
class ThemeState {
  final ThemeMode themeMode;
  const ThemeState(this.themeMode);
}

// ─── Cubit ────────────────────────────────────────────────────────────────────
class ThemeCubit extends Cubit<ThemeState> {
  static const _key = 'theme_mode';
  final SharedPreferences _prefs;

  ThemeCubit(this._prefs)
      : super(ThemeState(
          _prefs.getString(_key) == 'dark' ? ThemeMode.dark : ThemeMode.light,
        ));

  void setLight() => _applyTheme(ThemeMode.light);
  void setDark() => _applyTheme(ThemeMode.dark);
  void setSystem() => _applyTheme(ThemeMode.system);

  void toggle() {
    final next =
        state.themeMode == ThemeMode.light ? ThemeMode.dark : ThemeMode.light;
    _applyTheme(next);
  }

  void _applyTheme(ThemeMode mode) {
    _prefs.setString(_key, mode.name);
    emit(ThemeState(mode));
  }
}
