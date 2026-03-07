import 'package:flutter_dotenv/flutter_dotenv.dart';

class AppConfig {
  AppConfig._();

  static String get baseUrl =>
      dotenv.env['BASE_URL'] ?? 'https://api.attendanceapp.com/api';

  static String get appName => dotenv.env['APP_NAME'] ?? 'Attendance App';

  static String get appVersion => dotenv.env['APP_VERSION'] ?? '1.0.0';

  static double get faceConfidenceThreshold =>
      double.tryParse(dotenv.env['FACE_CONFIDENCE_THRESHOLD'] ?? '0.60') ?? 0.60;

  static double get gpsRadiusMeter =>
      double.tryParse(dotenv.env['GPS_RADIUS_METER'] ?? '100') ?? 100.0;
}
