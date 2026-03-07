import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../../data/model/attendance/model_attendance.dart';
import '../../../res/colors/base_colors.dart';
import '../label/label_attendance_status.dart';

/// Summary card displayed on the attendance home screen.
/// Shows check-in time, check-out time, and status label.
class CardAttendanceStatus extends StatelessWidget {
  final ModelAttendanceToday today;

  const CardAttendanceStatus({super.key, required this.today});

  @override
  Widget build(BuildContext context) {
    final checkIn = today.checkIn;
    final checkOut = today.checkOut;
    final fmt = DateFormat('HH:mm');

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            BaseColors.primaryBlue,
            BaseColors.primaryBlue.withBlue(220),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: BaseColors.primaryBlue.withOpacity(0.30),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ── Date & Status ───────────────────────────────────────────────
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                DateFormat('EEEE, d MMMM yyyy', 'id').format(DateTime.now()),
                style: const TextStyle(
                  color: Colors.white70,
                  fontSize: 13,
                ),
              ),
              if (today.status != null)
                LabelAttendanceStatus(status: today.status!),
            ],
          ),
          const SizedBox(height: 20),

          // ── Time row ────────────────────────────────────────────────────
          Row(
            children: [
              _TimeBlock(
                label: 'Masuk',
                time: checkIn != null ? fmt.format(checkIn.timestamp) : '--:--',
                icon: Icons.login,
              ),
              Container(
                width: 1,
                height: 40,
                margin: const EdgeInsets.symmetric(horizontal: 24),
                color: Colors.white30,
              ),
              _TimeBlock(
                label: 'Keluar',
                time: checkOut != null
                    ? fmt.format(checkOut.timestamp)
                    : '--:--',
                icon: Icons.logout,
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _TimeBlock extends StatelessWidget {
  final String label;
  final String time;
  final IconData icon;

  const _TimeBlock({
    required this.label,
    required this.time,
    required this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Icon(icon, size: 14, color: Colors.white60),
            const SizedBox(width: 4),
            Text(
              label,
              style: const TextStyle(color: Colors.white60, fontSize: 12),
            ),
          ],
        ),
        const SizedBox(height: 4),
        Text(
          time,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 24,
            fontWeight: FontWeight.bold,
            letterSpacing: 1,
          ),
        ),
      ],
    );
  }
}
