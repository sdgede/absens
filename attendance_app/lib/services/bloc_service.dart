import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../data/remote/auth_api.dart';
import '../data/remote/face_api.dart';
import '../data/remote/attendance_api.dart';
import '../data/remote/leave_api.dart';
import '../data/remote/report_api.dart';
import '../data/remote/branch_api.dart';

import '../repository/implements/auth_repo.dart';
import '../repository/implements/attendance_repo.dart';
import '../repository/implements/leave_repository_impl.dart';
import '../repository/implements/report_repository_impl.dart';
import '../repository/implements/branch_repository_impl.dart';

import '../repository/interfaces/auth_int.dart';
import '../repository/interfaces/attendance_int.dart';
import '../repository/interfaces/i_leave_repository.dart';
import '../repository/interfaces/i_report_repository.dart';
import '../repository/interfaces/i_branch_repository.dart';

import 'api_service.dart';
import 'auth_service.dart';
import 'biometric_service.dart';
import 'auto_logout_service.dart';
import 'encryption_service.dart';
import 'face_recognition_service.dart';
import 'liveness_service.dart';
import 'embedding_sync_service.dart';
import 'gps_service.dart';
import 'permission_service.dart';
import 'notification_service.dart';

/// Singleton manual DI container — no get_it.
/// Call [BlocService.init()] once in main() before runApp().
/// BLoCs access dependencies via static typed getters.
class BlocService {
  // ── Singleton ─────────────────────────────────────────────────────────────
  static final BlocService _instance = BlocService._internal();
  factory BlocService() => _instance;
  BlocService._internal();

  // ── Storage ────────────────────────────────────────────────────────────────
  FlutterSecureStorage? _secureStorage;
  SharedPreferences? _prefs;

  // ── Core Services ──────────────────────────────────────────────────────────
  EncryptionService? _encryptionService;
  AuthService? _authService;
  ApiService? _apiService;

  // ── Feature Services ───────────────────────────────────────────────────────
  FaceRecognitionService? _faceRecognitionService;
  LivenessService? _livenessService;
  EmbeddingSyncService? _embeddingSyncService;
  GpsService? _gpsService;
  PermissionService? _permissionService;
  BiometricService? _biometricService;
  NotificationService? _notificationService;
  AutoLogoutService? _autoLogoutService;

  // ── Remote APIs ────────────────────────────────────────────────────────────
  AuthApi? _authApi;
  FaceApi? _faceApi;
  AttendanceApi? _attendanceApi;
  LeaveApi? _leaveApi;
  ReportApi? _reportApi;
  BranchApi? _branchApi;

  // ── Repositories ───────────────────────────────────────────────────────────
  AuthInt? _authRepo;
  AttendanceInt? _attendanceRepo;
  ILeaveRepository? _leaveRepo;
  IReportRepository? _reportRepo;
  IBranchRepository? _branchRepo;

  // ── Init ───────────────────────────────────────────────────────────────────
  static Future<void> init() async {
    final s = _instance;

    // 1. Storage
    s._secureStorage = const FlutterSecureStorage(
      aOptions: AndroidOptions(encryptedSharedPreferences: true),
    );
    s._prefs = await SharedPreferences.getInstance();

    // 2. Core services
    s._encryptionService = EncryptionService();
    s._authService = AuthService(
      secureStorage: s._secureStorage!,
      prefs: s._prefs!,
    );

    // 3. API service (Dio + interceptors + certificate pinning)
    s._apiService = ApiService(secureStorage: s._secureStorage!);
    await s._apiService!.init();

    // 4. Face & liveness services
    s._faceRecognitionService = FaceRecognitionService();
    await s._faceRecognitionService!.init();

    s._livenessService = LivenessService();
    s._embeddingSyncService = EmbeddingSyncService(
      secureStorage: s._secureStorage!,
      prefs: s._prefs!,
      faceApi: FaceApi(apiService: s._apiService!),
      encryption: s._encryptionService!,
    );

    // 5. Location, permission & biometric
    s._gpsService = GpsService();
    s._permissionService = PermissionService();
    s._biometricService = BiometricService();

    // 6. Notification & auto-logout
    s._notificationService = NotificationService();
    await s._notificationService!.init();
    s._autoLogoutService = AutoLogoutService(authService: s._authService!);

    // 7. Remote APIs
    s._authApi = AuthApi(apiService: s._apiService!);
    s._faceApi = FaceApi(apiService: s._apiService!);
    s._attendanceApi = AttendanceApi(apiService: s._apiService!);
    s._leaveApi = LeaveApi(apiService: s._apiService!);
    s._reportApi = ReportApi(apiService: s._apiService!);
    s._branchApi = BranchApi(apiService: s._apiService!);

    // 8. Repositories
    s._authRepo = AuthRepo(
      authApi: s._authApi!,
      authService: s._authService!,
    );
    s._attendanceRepo = AttendanceRepo(api: s._attendanceApi!);
    s._leaveRepo = LeaveRepositoryImpl(leaveApi: s._leaveApi!);
    s._reportRepo = ReportRepositoryImpl(reportApi: s._reportApi!);
    s._branchRepo = BranchRepositoryImpl(branchApi: s._branchApi!);
  }

  // ── Static Getters — Storage ───────────────────────────────────────────────
  static FlutterSecureStorage get secureStorage => _instance._secureStorage!;
  static SharedPreferences get prefs => _instance._prefs!;

  // ── Static Getters — Core Services ────────────────────────────────────────
  static EncryptionService get encryptionService => _instance._encryptionService!;
  static AuthService get authService => _instance._authService!;
  static ApiService get api => _instance._apiService!;

  // ── Static Getters — Feature Services ─────────────────────────────────────
  static FaceRecognitionService get faceRecognitionService =>
      _instance._faceRecognitionService!;
  static LivenessService get livenessService => _instance._livenessService!;
  static EmbeddingSyncService get embeddingSyncService =>
      _instance._embeddingSyncService!;
  static GpsService get gpsService => _instance._gpsService!;
  static PermissionService get permissionService => _instance._permissionService!;
  static BiometricService get biometricService => _instance._biometricService!;
  static NotificationService get notificationService =>
      _instance._notificationService!;
  static AutoLogoutService get autoLogoutService => _instance._autoLogoutService!;

  // ── Static Getters — Remote APIs ──────────────────────────────────────────
  static AuthApi get authApi => _instance._authApi!;
  static FaceApi get faceApi => _instance._faceApi!;
  static AttendanceApi get attendanceApi => _instance._attendanceApi!;
  static LeaveApi get leaveApi => _instance._leaveApi!;
  static ReportApi get reportApi => _instance._reportApi!;
  static BranchApi get branchApi => _instance._branchApi!;

  // ── Static Getters — Repositories ─────────────────────────────────────────
  static AuthInt get authRepo => _instance._authRepo!;
  static AttendanceInt get attendanceRepo => _instance._attendanceRepo!;
  static ILeaveRepository get leaveRepo => _instance._leaveRepo!;
  static IReportRepository get reportRepo => _instance._reportRepo!;
  static IBranchRepository get branchRepo => _instance._branchRepo!;
}
