import 'package:flutter/material.dart';

import '../../../data/model/attendance/model_attendance.dart';
import '../../../res/colors/base_colors.dart';

/// A pill-shaped status label with icon and color per attendance status.
class LabelAttendanceStatus extends StatelessWidget {
  final AttendanceStatus status;

  const LabelAttendanceStatus({super.key, required this.status});

  @override
  Widget build(BuildContext context) {
    final config = _config(status);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: config.color.withOpacity(0.12),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: config.color.withOpacity(0.4)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(config.icon, size: 13, color: config.color),
          const SizedBox(width: 4),
          Text(
            config.label,
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: config.color,
            ),
          ),
        ],
      ),
    );
  }

  _StatusConfig _config(AttendanceStatus s) {
    return switch (s) {
      AttendanceStatus.present => _StatusConfig(
          label: 'Hadir',
          color: BaseColors.success,
          icon: Icons.check_circle_outline,
        ),
      AttendanceStatus.late => _StatusConfig(
          label: 'Terlambat',
          color: const Color(0xFFFF9800),
          icon: Icons.schedule,
        ),
      AttendanceStatus.absent => _StatusConfig(
          label: 'Alpha',
          color: BaseColors.error,
          icon: Icons.cancel_outlined,
        ),
      AttendanceStatus.leave => _StatusConfig(
          label: 'Izin',
          color: BaseColors.primaryBlue,
          icon: Icons.event_note_outlined,
        ),
      AttendanceStatus.sick => _StatusConfig(
          label: 'Sakit',
          color: const Color(0xFF9C27B0),
          icon: Icons.local_hospital_outlined,
        ),
    };
  }
}

class _StatusConfig {
  final String label;
  final Color color;
  final IconData icon;
  const _StatusConfig(
      {required this.label, required this.color, required this.icon});
}
