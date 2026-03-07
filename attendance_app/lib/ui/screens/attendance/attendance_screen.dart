import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:shimmer/shimmer.dart';

import '../../../bloc/attendance/attendance_bloc.dart';
import '../../../bloc/attendance/attendance_event.dart';
import '../../../bloc/attendance/attendance_state.dart';
import '../../../bloc/auth/auth_bloc.dart';
import '../../../bloc/auth/auth_state.dart';
import '../../../bloc/face/face_scan/face_scan_bloc.dart';
import '../../../bloc/face/face_scan/face_scan_event_state.dart';
import '../../../data/model/attendance/model_attendance.dart';
import '../../../data/model/face/match_result.dart';
import '../../../res/colors/base_colors.dart';
import '../../../res/constants/routes.dart';
import '../../../services/bloc_service.dart';
import '../../../services/gps_service.dart';
import '../../widgets/card/card_attendance_status.dart';
import '../../widgets/form/button_primary.dart';
import '../../widgets/label/label_attendance_status.dart';
import 'scan_face_page.dart';

class AttendanceScreen extends StatefulWidget {
  const AttendanceScreen({super.key});

  @override
  State<AttendanceScreen> createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen> {
  String? _activeSessionId;

  @override
  void initState() {
    super.initState();
    _loadToday();
  }

  void _loadToday() {
    final userId = _currentUserId();
    if (userId != null) {
      context.read<AttendanceBloc>().add(LoadTodayStatus(userId));
    }
  }

  String? _currentUserId() {
    final authState = context.read<AuthBloc>().state;
    if (authState is AuthAuthenticated) return authState.user.userId;
    return null;
  }

  // ── GPS + Navigate to ScanFace ─────────────────────────────────────────
  Future<void> _initiateAttendance({required bool isCheckIn}) async {
    final gps = BlocService.gpsService;

    // 1. Permission
    final granted = await gps.requestPermission();
    if (!mounted) return;
    if (!granted) {
      _showSnack('Izin lokasi diperlukan untuk absensi.', isError: true);
      return;
    }

    // 2. Current position
    late double lat, lng;
    try {
      final pos = await gps.getCurrentPosition();
      lat = pos.latitude;
      lng = pos.longitude;
    } catch (_) {
      if (mounted) {
        _showSnack('Gagal mendapatkan lokasi. Aktifkan GPS.', isError: true);
      }
      return;
    }

    // 3. Branch radius check (uses first session's branch for demo)
    // In production: fetch active session → get branch → validate
    // Here we skip the hard validation and pass coordinates to the BLoC.

    if (!mounted) return;

    // 4. Navigate to ScanFacePage and await result
    final result = await Navigator.push<MatchResult?>(
      context,
      MaterialPageRoute(
        builder: (_) => BlocProvider(
          create: (_) => FaceScanBloc(
            recognitionService: BlocService.faceRecognitionService,
            syncService: BlocService.embeddingSyncService,
            gpsService: BlocService.gpsService,
            livenessService: BlocService.livenessService,
          ),
          child: const ScanFacePage(),
        ),
      ),
    );

    if (result == null || !result.isMatch || !mounted) return;

    final userId = _currentUserId();
    if (userId == null) return;

    final sessionId = _activeSessionId ?? 'default';

    if (isCheckIn) {
      context.read<AttendanceBloc>().add(CheckInRequested(
            userId: userId,
            confidence: result.confidence,
            livenessScore: result.confidence, // reuse for demo
            lat: lat,
            lng: lng,
            sessionId: sessionId,
          ));
    } else {
      context.read<AttendanceBloc>().add(CheckOutRequested(
            userId: userId,
            lat: lat,
            lng: lng,
            sessionId: sessionId,
          ));
    }
  }

  void _showSnack(String message, {bool isError = false}) {
    ScaffoldMessenger.of(context)
      ..hideCurrentSnackBar()
      ..showSnackBar(SnackBar(
        content: Text(message),
        backgroundColor: isError ? BaseColors.error : BaseColors.success,
        behavior: SnackBarBehavior.floating,
        shape:
            RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        margin: const EdgeInsets.all(16),
      ));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: BaseColors.background,
      appBar: AppBar(
        title: const Text('Absensi'),
        actions: [
          IconButton(
            icon: const Icon(Icons.history),
            onPressed: () => context.push(AppRoutes.attendanceHistory),
            tooltip: 'Riwayat',
          ),
        ],
      ),
      body: BlocConsumer<AttendanceBloc, AttendanceState>(
        listener: (context, state) {
          if (state is AttendanceCheckInSuccess) {
            _showSnack('Check-in berhasil! ✓');
            _loadToday();
          }
          if (state is AttendanceCheckOutSuccess) {
            _showSnack('Check-out berhasil! ✓');
            _loadToday();
          }
          if (state is AttendanceError) {
            _showSnack(state.message, isError: true);
          }
        },
        builder: (context, state) {
          return RefreshIndicator(
            onRefresh: () async => _loadToday(),
            child: SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // ── Today card ──────────────────────────────────────────
                  if (state is AttendanceLoading)
                    _ShimmerCard()
                  else if (state is AttendanceTodayLoaded)
                    CardAttendanceStatus(today: state.today)
                  else
                    _EmptyTodayCard(),

                  const SizedBox(height: 28),

                  // ── Action buttons ──────────────────────────────────────
                  _buildActionButtons(state),

                  const SizedBox(height: 32),

                  // ── Quick info section ──────────────────────────────────
                  _SectionTitle(title: 'Informasi'),
                  const SizedBox(height: 12),
                  _InfoRow(
                    icon: Icons.location_on_outlined,
                    label: 'GPS required',
                    value: 'Aktif saat absensi',
                  ),
                  _InfoRow(
                    icon: Icons.face_outlined,
                    label: 'Face recognition',
                    value: 'On-device, aman',
                  ),
                  _InfoRow(
                    icon: Icons.security_outlined,
                    label: 'Liveness check',
                    value: 'Anti-spoofing aktif',
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildActionButtons(AttendanceState state) {
    final isLoading = state is AttendanceLoading;

    bool checkedIn = false;
    bool checkedOut = false;
    if (state is AttendanceTodayLoaded) {
      checkedIn = state.today.isCheckedIn;
      checkedOut = state.today.isCheckedOut;
    }

    return Column(
      children: [
        // Check-In
        ButtonPrimary(
          label: 'Check-In',
          icon: Icons.login,
          isLoading: isLoading,
          onPressed: (!checkedIn && !isLoading)
              ? () => _initiateAttendance(isCheckIn: true)
              : null,
        ),
        const SizedBox(height: 12),

        // Check-Out
        SizedBox(
          width: double.infinity,
          height: 52,
          child: OutlinedButton.icon(
            icon: const Icon(Icons.logout),
            label: const Text(
              'Check-Out',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w600,
              ),
            ),
            onPressed: (checkedIn && !checkedOut && !isLoading)
                ? () => _initiateAttendance(isCheckIn: false)
                : null,
            style: OutlinedButton.styleFrom(
              foregroundColor: BaseColors.primaryBlue,
              side: BorderSide(
                color: (checkedIn && !checkedOut)
                    ? BaseColors.primaryBlue
                    : Colors.grey.shade300,
              ),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
              ),
            ),
          ),
        ),
      ],
    );
  }
}

// ── Empty / Shimmer helpers ───────────────────────────────────────────────────

class _EmptyTodayCard extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.grey.shade100,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: const Center(
        child: Text(
          'Belum ada data absensi hari ini',
          style: TextStyle(color: BaseColors.textSecondary),
        ),
      ),
    );
  }
}

class _ShimmerCard extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Shimmer.fromColors(
      baseColor: const Color(0xFFE0E0E0),
      highlightColor: const Color(0xFFF5F5F5),
      child: Container(
        width: double.infinity,
        height: 130,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
        ),
      ),
    );
  }
}

class _SectionTitle extends StatelessWidget {
  final String title;
  const _SectionTitle({required this.title});

  @override
  Widget build(BuildContext context) {
    return Text(
      title,
      style: const TextStyle(
        fontSize: 16,
        fontWeight: FontWeight.bold,
        color: BaseColors.textPrimary,
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;
  const _InfoRow(
      {required this.icon, required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Row(
        children: [
          Icon(icon, size: 18, color: BaseColors.primaryBlue),
          const SizedBox(width: 10),
          Expanded(
              child: Text(label,
                  style: const TextStyle(color: BaseColors.textSecondary))),
          Text(value,
              style: const TextStyle(
                  fontWeight: FontWeight.w500,
                  color: BaseColors.textPrimary)),
        ],
      ),
    );
  }
}
