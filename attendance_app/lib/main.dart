import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'package:go_router/go_router.dart';

import 'bloc/auth/auth_bloc.dart';
import 'bloc/auth/auth_event.dart';
import 'bloc/auth/auth_state.dart';
import 'bloc/theme/theme_cubit.dart';
import 'res/colors/theme_colors.dart';
import 'res/constants/routes.dart';
import 'services/bloc_service.dart';
import 'ui/screens/auth/login_screen.dart';
import 'ui/screens/auth/splash_screen.dart';
import 'bloc/attendance/attendance_bloc.dart';
import 'ui/screens/attendance/attendance_screen.dart';
import 'ui/screens/attendance/attendance_history_screen.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // 1. Load environment variables from .env
  await dotenv.load(fileName: '.env');

  // 2. Initialise all DI (services, APIs, repos)
  await BlocService.init();

  runApp(const AttendanceApp());
}

class AttendanceApp extends StatelessWidget {
  const AttendanceApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiBlocProvider(
      providers: [
        BlocProvider<ThemeCubit>(
          create: (_) => ThemeCubit(BlocService.prefs),
        ),
        BlocProvider<AuthBloc>(
          create: (_) => AuthBloc(
            authRepo: BlocService.authRepo,
            authService: BlocService.authService,
          ),
        ),
        BlocProvider<AttendanceBloc>(
          create: (_) => AttendanceBloc(
            repo: BlocService.attendanceRepo,
          ),
        ),
      ],
      child: BlocBuilder<ThemeCubit, ThemeState>(
        builder: (context, themeState) {
          return MaterialApp.router(
            title: 'Attendance App',
            debugShowCheckedModeBanner: false,
            theme: ThemeColors.light,
            darkTheme: ThemeColors.dark,
            themeMode: themeState.themeMode,
            routerConfig: _buildRouter(context),
          );
        },
      ),
    );
  }
}

// ── Router ────────────────────────────────────────────────────────────────────
GoRouter _buildRouter(BuildContext context) => GoRouter(
      initialLocation: AppRoutes.splash,
      redirect: (context, state) {
        // Allow unauthenticated access to splash and login only
        final loggingIn = state.matchedLocation == AppRoutes.login;
        final splashing = state.matchedLocation == AppRoutes.splash;
        if (loggingIn || splashing) return null;

        final authState = context.read<AuthBloc>().state;
        if (authState is AuthUnauthenticated) return AppRoutes.login;
        return null;
      },
      routes: [
        GoRoute(
          path: AppRoutes.splash,
          name: 'splash',
          builder: (context, state) => const SplashScreen(),
        ),
        GoRoute(
          path: AppRoutes.login,
          name: 'login',
          builder: (context, state) => const LoginScreen(),
        ),
        GoRoute(
          path: AppRoutes.home,
          name: 'home',
          builder: (context, state) => const _PlaceholderHome(),
        ),
        GoRoute(
          path: AppRoutes.attendance,
          name: 'attendance',
          builder: (context, state) => const AttendanceScreen(),
        ),
        GoRoute(
          path: AppRoutes.attendanceHistory,
          name: 'attendance_history',
          builder: (context, state) => const AttendanceHistoryScreen(),
        ),
        // TODO: add remaining feature routes here
      ],
      errorBuilder: (context, state) => Scaffold(
        body: Center(child: Text('Page not found: ${state.error}')),
      ),
    );

// ── Placeholder home ──────────────────────────────────────────────────────────
class _PlaceholderHome extends StatelessWidget {
  const _PlaceholderHome();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Home')),
      body: const Center(child: Text('Dashboard — coming soon')),
    );
  }
}
