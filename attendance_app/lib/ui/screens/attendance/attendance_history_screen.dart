import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:intl/intl.dart';
import 'package:shimmer/shimmer.dart';

import '../../../bloc/attendance/attendance_bloc.dart';
import '../../../bloc/attendance/attendance_event.dart';
import '../../../bloc/attendance/attendance_state.dart';
import '../../../bloc/auth/auth_bloc.dart';
import '../../../bloc/auth/auth_state.dart';
import '../../../data/model/attendance/model_attendance.dart';
import '../../../res/colors/base_colors.dart';
import '../../widgets/label/label_attendance_status.dart';

class AttendanceHistoryScreen extends StatefulWidget {
  const AttendanceHistoryScreen({super.key});

  @override
  State<AttendanceHistoryScreen> createState() =>
      _AttendanceHistoryScreenState();
}

class _AttendanceHistoryScreenState extends State<AttendanceHistoryScreen> {
  final _scrollCtrl = ScrollController();
  int _currentPage = 1;
  bool _isLoadingMore = false;
  DateTime _selectedMonth = DateTime(DateTime.now().year, DateTime.now().month);

  String? get _userId {
    final s = context.read<AuthBloc>().state;
    return s is AuthAuthenticated ? s.user.userId : null;
  }

  @override
  void initState() {
    super.initState();
    _loadPage(1);
    _scrollCtrl.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollCtrl.dispose();
    super.dispose();
  }

  void _loadPage(int page) {
    final id = _userId;
    if (id == null) return;
    final start = DateFormat('yyyy-MM-01').format(_selectedMonth);
    final end = DateFormat('yyyy-MM-dd')
        .format(DateTime(_selectedMonth.year, _selectedMonth.month + 1, 0));
    context.read<AttendanceBloc>().add(LoadHistory(
          userId: id,
          page: page,
          startDate: start,
          endDate: end,
        ));
  }

  void _onScroll() {
    if (_scrollCtrl.position.pixels >=
            _scrollCtrl.position.maxScrollExtent - 200 &&
        !_isLoadingMore) {
      setState(() {
        _isLoadingMore = true;
        _currentPage++;
      });
      _loadPage(_currentPage);
    }
  }

  Future<void> _pickMonth() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedMonth,
      firstDate: DateTime(now.year - 2),
      lastDate: now,
      helpText: 'Pilih bulan',
      initialEntryMode: DatePickerEntryMode.calendarOnly,
    );
    if (picked == null) return;
    setState(() {
      _selectedMonth = DateTime(picked.year, picked.month);
      _currentPage = 1;
      _isLoadingMore = false;
    });
    _loadPage(1);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: BaseColors.background,
      appBar: AppBar(
        title: const Text('Riwayat Absensi'),
        actions: [
          TextButton.icon(
            icon: const Icon(Icons.calendar_month, size: 18),
            label: Text(
              DateFormat('MMM yyyy', 'id').format(_selectedMonth),
              style: const TextStyle(fontWeight: FontWeight.w600),
            ),
            onPressed: _pickMonth,
          ),
        ],
      ),
      body: BlocConsumer<AttendanceBloc, AttendanceState>(
        listener: (context, state) {
          if (state is AttendanceHistoryLoaded ||
              state is AttendanceHistoryAppended) {
            setState(() => _isLoadingMore = false);
          }
        },
        builder: (context, state) {
          // Loading first page
          if (state is AttendanceLoading) {
            return _ShimmerHistory();
          }

          List<ModelAttendance> records = [];
          bool hasMore = false;

          if (state is AttendanceHistoryLoaded) {
            records = state.records;
            hasMore = state.hasMore;
          } else if (state is AttendanceHistoryAppended) {
            records = state.allRecords;
            hasMore = state.hasMore;
          }

          if (records.isEmpty) {
            return const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.inbox_outlined,
                      size: 64, color: BaseColors.textSecondary),
                  SizedBox(height: 12),
                  Text(
                    'Tidak ada riwayat absensi',
                    style: TextStyle(color: BaseColors.textSecondary),
                  ),
                ],
              ),
            );
          }

          // Group records by date
          final grouped = _groupByDate(records);

          return ListView.builder(
            controller: _scrollCtrl,
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            itemCount: grouped.length + (_isLoadingMore ? 1 : 0),
            itemBuilder: (context, index) {
              if (index == grouped.length) {
                return const Padding(
                  padding: EdgeInsets.all(16),
                  child: Center(
                      child: CircularProgressIndicator(strokeWidth: 2)),
                );
              }

              final entry = grouped.entries.elementAt(index);
              return _DateGroup(
                  dateLabel: entry.key, records: entry.value);
            },
          );
        },
      ),
    );
  }

  /// Group records by formatted date string.
  Map<String, List<ModelAttendance>> _groupByDate(
      List<ModelAttendance> records) {
    final Map<String, List<ModelAttendance>> grouped = {};
    for (final r in records) {
      final key = DateFormat('EEEE, d MMMM yyyy', 'id').format(r.timestamp);
      grouped.putIfAbsent(key, () => []).add(r);
    }
    return grouped;
  }
}

// ── Date group header + records ───────────────────────────────────────────────
class _DateGroup extends StatelessWidget {
  final String dateLabel;
  final List<ModelAttendance> records;

  const _DateGroup({required this.dateLabel, required this.records});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.only(top: 16, bottom: 8),
          child: Text(
            dateLabel,
            style: const TextStyle(
              fontWeight: FontWeight.bold,
              color: BaseColors.textSecondary,
              fontSize: 12,
            ),
          ),
        ),
        ...records.map((r) => _HistoryCard(record: r)),
      ],
    );
  }
}

class _HistoryCard extends StatelessWidget {
  final ModelAttendance record;
  const _HistoryCard({required this.record});

  @override
  Widget build(BuildContext context) {
    final fmt = DateFormat('HH:mm');
    final isCheckin = record.type == AttendanceType.checkin;

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          // Icon
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: (isCheckin ? BaseColors.primaryBlue : BaseColors.success)
                  .withOpacity(0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(
              isCheckin ? Icons.login : Icons.logout,
              size: 20,
              color: isCheckin ? BaseColors.primaryBlue : BaseColors.success,
            ),
          ),
          const SizedBox(width: 14),

          // Label
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  isCheckin ? 'Check-In' : 'Check-Out',
                  style: const TextStyle(
                    fontWeight: FontWeight.w600,
                    color: BaseColors.textPrimary,
                    fontSize: 14,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  'Conf: ${((record.faceConfidence ?? 0) * 100).toStringAsFixed(0)}%',
                  style: const TextStyle(
                    fontSize: 11,
                    color: BaseColors.textSecondary,
                  ),
                ),
              ],
            ),
          ),

          // Time + status
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                fmt.format(record.timestamp),
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 16,
                  color: BaseColors.textPrimary,
                ),
              ),
              const SizedBox(height: 4),
              LabelAttendanceStatus(status: record.status),
            ],
          ),
        ],
      ),
    );
  }
}

// ── Shimmer ───────────────────────────────────────────────────────────────────
class _ShimmerHistory extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Shimmer.fromColors(
      baseColor: const Color(0xFFE0E0E0),
      highlightColor: const Color(0xFFF5F5F5),
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: 6,
        itemBuilder: (_, __) => Container(
          margin: const EdgeInsets.only(bottom: 12),
          height: 68,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(14),
          ),
        ),
      ),
    );
  }
}
