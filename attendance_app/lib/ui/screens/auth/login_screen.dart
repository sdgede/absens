import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:shimmer/shimmer.dart';

import '../../../bloc/auth/auth_bloc.dart';
import '../../../bloc/auth/auth_event.dart';
import '../../../bloc/auth/auth_state.dart';
import '../../../res/colors/base_colors.dart';
import '../../../res/constants/config.dart';
import '../../../res/constants/routes.dart';
import '../../widgets/form/button_primary.dart';
import '../../widgets/form/input_password.dart';
import '../../widgets/form/input_textlabel.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailCtrl = TextEditingController();
  final _passwordCtrl = TextEditingController();

  @override
  void dispose() {
    _emailCtrl.dispose();
    _passwordCtrl.dispose();
    super.dispose();
  }

  void _submit() {
    if (!_formKey.currentState!.validate()) return;
    context.read<AuthBloc>().add(
          LoginRequested(
            email: _emailCtrl.text.trim(),
            password: _passwordCtrl.text,
          ),
        );
  }

  @override
  Widget build(BuildContext context) {
    return BlocConsumer<AuthBloc, AuthState>(
      listener: (context, state) {
        if (state is AuthAuthenticated) {
          context.go(AppRoutes.home);
        }
        if (state is AuthError) {
          ScaffoldMessenger.of(context)
            ..hideCurrentSnackBar()
            ..showSnackBar(
              SnackBar(
                content: Text(state.message),
                backgroundColor: BaseColors.error,
                behavior: SnackBarBehavior.floating,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
                margin:
                    const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              ),
            );
        }
      },
      builder: (context, state) {
        final isLoading = state is AuthLoading;

        return Scaffold(
          backgroundColor: BaseColors.background,
          body: SafeArea(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const SizedBox(height: 56),

                  // ── Header ───────────────────────────────────────────────
                  Center(
                    child: Container(
                      width: 72,
                      height: 72,
                      decoration: BoxDecoration(
                        color: BaseColors.primaryBlue,
                        borderRadius: BorderRadius.circular(20),
                        boxShadow: [
                          BoxShadow(
                            color: BaseColors.primaryBlue.withOpacity(0.35),
                            blurRadius: 20,
                            offset: const Offset(0, 8),
                          )
                        ],
                      ),
                      child: const Icon(Icons.fingerprint,
                          color: Colors.white, size: 40),
                    ),
                  ),
                  const SizedBox(height: 32),
                  Text(
                    'Selamat Datang',
                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                          fontWeight: FontWeight.bold,
                          color: BaseColors.textPrimary,
                        ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    'Masuk ke ${AppConfig.appName}',
                    style: const TextStyle(
                      color: BaseColors.textSecondary,
                      fontSize: 14,
                    ),
                  ),
                  const SizedBox(height: 40),

                  // ── Form ─────────────────────────────────────────────────
                  Form(
                    key: _formKey,
                    child: Column(
                      children: [
                        // Shimmer while loading
                        if (isLoading) ...[
                          _ShimmerLoginForm(),
                        ] else ...[
                          InputTextLabel(
                            label: 'Email',
                            hint: 'nama@perusahaan.com',
                            controller: _emailCtrl,
                            keyboardType: TextInputType.emailAddress,
                            prefixIcon: Icons.email_outlined,
                            validator: (v) {
                              if (v == null || v.isEmpty) {
                                return 'Email tidak boleh kosong';
                              }
                              if (!v.contains('@')) {
                                return 'Format email tidak valid';
                              }
                              return null;
                            },
                          ),
                          const SizedBox(height: 16),
                          InputPassword(
                            label: 'Password',
                            hint: 'Masukkan password',
                            controller: _passwordCtrl,
                            validator: (v) {
                              if (v == null || v.isEmpty) {
                                return 'Password tidak boleh kosong';
                              }
                              if (v.length < 6) {
                                return 'Password minimal 6 karakter';
                              }
                              return null;
                            },
                          ),
                          const SizedBox(height: 32),
                          ButtonPrimary(
                            label: 'Masuk',
                            onPressed: _submit,
                          ),
                        ],
                      ],
                    ),
                  ),

                  const SizedBox(height: 24),

                  // ── Footer ───────────────────────────────────────────────
                  Center(
                    child: Text(
                      '${AppConfig.appName} v${AppConfig.appVersion}',
                      style: const TextStyle(
                        color: BaseColors.textSecondary,
                        fontSize: 12,
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),
                ],
              ),
            ),
          ),
        );
      },
    );
  }
}

// ── Shimmer skeleton for loading state ────────────────────────────────────────
class _ShimmerLoginForm extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Shimmer.fromColors(
      baseColor: const Color(0xFFE0E0E0),
      highlightColor: const Color(0xFFF5F5F5),
      child: Column(
        children: [
          _shimmerBox(56),
          const SizedBox(height: 16),
          _shimmerBox(56),
          const SizedBox(height: 32),
          _shimmerBox(48),
        ],
      ),
    );
  }

  Widget _shimmerBox(double height) => Container(
        width: double.infinity,
        height: height,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
        ),
      );
}
