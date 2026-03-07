import 'package:equatable/equatable.dart';

import '../../data/model/attendance/model_attendance.dart';

sealed class AttendanceState extends Equatable {
  const AttendanceState();

  @override
  List<Object?> get props => [];
}

class AttendanceInitial extends AttendanceState {
  const AttendanceInitial();
}

class AttendanceLoading extends AttendanceState {
  const AttendanceLoading();
}

/// Emitted after loading today's status.
class AttendanceTodayLoaded extends AttendanceState {
  final ModelAttendanceToday today;
  const AttendanceTodayLoaded(this.today);

  @override
  List<Object?> get props => [today];
}

/// Emitted after a successful check-in.
class AttendanceCheckInSuccess extends AttendanceState {
  final ModelAttendance attendance;
  const AttendanceCheckInSuccess(this.attendance);

  @override
  List<Object?> get props => [attendance.id];
}

/// Emitted after a successful check-out.
class AttendanceCheckOutSuccess extends AttendanceState {
  final ModelAttendance attendance;
  const AttendanceCheckOutSuccess(this.attendance);

  @override
  List<Object?> get props => [attendance.id];
}

/// Emitted after history is loaded (supports pagination).
class AttendanceHistoryLoaded extends AttendanceState {
  final List<ModelAttendance> records;
  final int page;
  final bool hasMore;

  const AttendanceHistoryLoaded({
    required this.records,
    required this.page,
    this.hasMore = true,
  });

  @override
  List<Object?> get props => [page, records.length];
}

/// Emitted when appending next page to existing list.
class AttendanceHistoryAppended extends AttendanceState {
  final List<ModelAttendance> allRecords;
  final int page;
  final bool hasMore;

  const AttendanceHistoryAppended({
    required this.allRecords,
    required this.page,
    this.hasMore = true,
  });

  @override
  List<Object?> get props => [page, allRecords.length];
}

class AttendanceError extends AttendanceState {
  final String message;
  const AttendanceError(this.message);

  @override
  List<Object?> get props => [message];
}
