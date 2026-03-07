import 'package:flutter_bloc/flutter_bloc.dart';

import '../../data/model/attendance/model_attendance.dart';
import '../../repository/interfaces/attendance_int.dart';
import 'attendance_event.dart';
import 'attendance_state.dart';

class AttendanceBloc extends Bloc<AttendanceEvent, AttendanceState> {
  final AttendanceInt _repo;

  /// All history records buffered for pagination.
  final List<ModelAttendance> _historyBuffer = [];

  AttendanceBloc({required AttendanceInt repo})
      : _repo = repo,
        super(const AttendanceInitial()) {
    on<LoadTodayStatus>(_onLoadToday);
    on<CheckInRequested>(_onCheckIn);
    on<CheckOutRequested>(_onCheckOut);
    on<LoadHistory>(_onLoadHistory);
  }

  // ── Handlers ──────────────────────────────────────────────────────────────

  Future<void> _onLoadToday(
    LoadTodayStatus event,
    Emitter<AttendanceState> emit,
  ) async {
    emit(const AttendanceLoading());
    final result = await _repo.getTodayStatus(event.userId);
    result.fold(
      (f) => emit(AttendanceError(f.message)),
      (today) => emit(AttendanceTodayLoaded(today)),
    );
  }

  Future<void> _onCheckIn(
    CheckInRequested event,
    Emitter<AttendanceState> emit,
  ) async {
    emit(const AttendanceLoading());
    final result = await _repo.checkIn(
      userId: event.userId,
      confidence: event.confidence,
      livenessScore: event.livenessScore,
      lat: event.lat,
      lng: event.lng,
      sessionId: event.sessionId,
    );
    result.fold(
      (f) => emit(AttendanceError(f.message)),
      (attendance) => emit(AttendanceCheckInSuccess(attendance)),
    );
  }

  Future<void> _onCheckOut(
    CheckOutRequested event,
    Emitter<AttendanceState> emit,
  ) async {
    emit(const AttendanceLoading());
    final result = await _repo.checkOut(
      userId: event.userId,
      lat: event.lat,
      lng: event.lng,
      sessionId: event.sessionId,
    );
    result.fold(
      (f) => emit(AttendanceError(f.message)),
      (attendance) => emit(AttendanceCheckOutSuccess(attendance)),
    );
  }

  Future<void> _onLoadHistory(
    LoadHistory event,
    Emitter<AttendanceState> emit,
  ) async {
    if (event.page == 1) {
      _historyBuffer.clear();
      emit(const AttendanceLoading());
    }

    final result = await _repo.getHistory(
      event.userId,
      page: event.page,
      startDate: event.startDate,
      endDate: event.endDate,
    );

    result.fold(
      (f) => emit(AttendanceError(f.message)),
      (records) {
        _historyBuffer.addAll(records);
        final hasMore = records.isNotEmpty;

        if (event.page == 1) {
          emit(AttendanceHistoryLoaded(
            records: List.from(_historyBuffer),
            page: event.page,
            hasMore: hasMore,
          ));
        } else {
          emit(AttendanceHistoryAppended(
            allRecords: List.from(_historyBuffer),
            page: event.page,
            hasMore: hasMore,
          ));
        }
      },
    );
  }
}
