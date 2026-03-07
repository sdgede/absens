import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:lottie/lottie.dart';

import '../../../bloc/auth/auth_bloc.dart';
import '../../../bloc/auth/auth_event.dart';
import '../../../bloc/auth/auth_state.dart';
import '../../../res/colors/base_colors.dart';
import '../../../res/constants/routes.dart';
import '../../../res/constants/config.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    context.read<AuthBloc>().add(const AuthCheckRequested());
  }

  @override
  Widget build(BuildContext context) {
    return BlocListener<AuthBloc, AuthState>(
      listener: (context, state) {
        if (state is AuthAuthenticated) {
          context.go(AppRoutes.home);
        } else if (state is AuthUnauthenticated) {
          context.go(AppRoutes.login);
        }
      },
      child: Scaffold(
        backgroundColor: BaseColors.primaryBlue,
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // ── Lottie animation ────────────────────────────────────────
              // Replace 'assets/images/splash_animation.json' with your
              // actual Lottie file once added to assets/images/.
              SizedBox(
                width: 180,
                height: 180,
                child: _buildAnimation(),
              ),
              const SizedBox(height: 32),

              // ── App name ─────────────────────────────────────────────────
              Text(
                AppConfig.appName,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 28,
                  fontWeight: FontWeight.bold,
                  letterSpacing: 1,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                'v${AppConfig.appVersion}',
                style: const TextStyle(
                  color: Colors.white70,
                  fontSize: 13,
                ),
              ),
              const SizedBox(height: 48),

              // ── Loading indicator ─────────────────────────────────────────
              const SizedBox(
                width: 32,
                height: 32,
                child: CircularProgressIndicator(
                  color: Colors.white,
                  strokeWidth: 2.5,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildAnimation() {
    // Gracefully handles missing Lottie file during development
    try {
      return Lottie.asset(
        'assets/images/splash_animation.json',
        fit: BoxFit.contain,
        repeat: true,
      );
    } catch (_) {
      return const Icon(Icons.fingerprint, size: 100, color: Colors.white);
    }
  }
}
