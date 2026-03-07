<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\CheckInRequest;
use App\Http\Requests\Attendance\CheckOutRequest;
use App\Jobs\SendCheckInNotificationJob;
use App\Jobs\UpdateSummaryJob;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Services\AttendanceService;
use App\Services\GpsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Helpers\TenantHelper;


class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
        private readonly GpsService $gpsService,
    ) {}

    /**
     * POST /api/v1/attendance/checkin
     */
    public function checkIn(CheckInRequest $request): JsonResponse
    {
        $user = TenantHelper::currentUser();
        $now  = Carbon::now();

        // 1. Validasi face_confidence
        if ($request->face_confidence < config('attendance.face_confidence_threshold')) {
            return response()->json(['message' => 'Wajah tidak terverifikasi'], 422);
        }

        // 2. Validasi liveness_score
        if ($request->liveness_score < config('attendance.liveness_score_threshold')) {
            return response()->json(['message' => 'Liveness check gagal'], 422);
        }

        // 3. Cek sesi aktif berdasarkan branch user
        $session = $this->attendanceService->getActiveSession($user->branch_id, $now);
        if (!$session) {
            return response()->json(['message' => 'Tidak ada sesi absensi aktif'], 422);
        }

        // 4. Cek sudah check-in hari ini
        if ($this->attendanceService->hasCheckedInToday($user->id, $session->id)) {
            return response()->json(['message' => 'Sudah check-in hari ini'], 409);
        }

        // 5. Validasi GPS
        $branch = $session->branch;
        $isWithin = $this->gpsService->isWithinRadius(
            $request->lat,
            $request->lng,
            (float) $branch->lat,
            (float) $branch->lng,
            $branch->radius_meter,
        );

        if (!$isWithin) {
            $distance = $this->gpsService->distanceTo(
                $request->lat,
                $request->lng,
                (float) $branch->lat,
                (float) $branch->lng,
            );
            return response()->json([
                'message' => "Di luar area absensi ({$distance}m dari lokasi)",
            ], 422);
        }

        // 6. Tentukan status
        $status = $this->attendanceService->determineStatus($session, $now);

        // 7. Simpan attendance
        $attendance = Attendance::create([
            'user_id'          => $user->id,
            'session_id'       => $session->id,
            'type'             => 'checkin',
            'status'           => $status,
            'lat'              => $request->lat,
            'lng'              => $request->lng,
            'face_confidence'  => $request->face_confidence,
            'liveness_score'   => $request->liveness_score,
            'checked_at'       => $now,
        ]);

        // 8. Dispatch jobs
        UpdateSummaryJob::dispatch($user->id, $now);
        SendCheckInNotificationJob::dispatch($user->id, $attendance->id);

        return response()->json([
            'attendance' => $attendance,
            'status'     => $status,
            'checked_at' => $now->toISOString(),
        ], 201);
    }

    /**
     * POST /api/v1/attendance/checkout
     */
    public function checkOut(CheckOutRequest $request): JsonResponse
    {
        $user = TenantHelper::currentUser();
        $now  = Carbon::now();

        // Validasi face & liveness
        if ($request->face_confidence < config('attendance.face_confidence_threshold')) {
            return response()->json(['message' => 'Wajah tidak terverifikasi'], 422);
        }

        if ($request->liveness_score < config('attendance.liveness_score_threshold')) {
            return response()->json(['message' => 'Liveness check gagal'], 422);
        }

        // Cek sudah checkin hari ini
        $session = AttendanceSession::findOrFail($request->session_id);

        if (!$this->attendanceService->hasCheckedInToday($user->id, $session->id)) {
            return response()->json(['message' => 'Belum check-in hari ini'], 422);
        }

        // Cek belum checkout
        if ($this->attendanceService->hasCheckedOutToday($user->id, $session->id)) {
            return response()->json(['message' => 'Sudah check-out hari ini'], 409);
        }

        // Validasi GPS
        $branch = $session->branch;
        $isWithin = $this->gpsService->isWithinRadius(
            $request->lat,
            $request->lng,
            (float) $branch->lat,
            (float) $branch->lng,
            $branch->radius_meter,
        );

        if (!$isWithin) {
            $distance = $this->gpsService->distanceTo(
                $request->lat,
                $request->lng,
                (float) $branch->lat,
                (float) $branch->lng,
            );
            return response()->json([
                'message' => "Di luar area absensi ({$distance}m dari lokasi)",
            ], 422);
        }

        // Simpan checkout
        $attendance = Attendance::create([
            'user_id'         => $user->id,
            'session_id'      => $session->id,
            'type'            => 'checkout',
            'status'          => 'present',
            'lat'             => $request->lat,
            'lng'             => $request->lng,
            'face_confidence' => $request->face_confidence,
            'liveness_score'  => $request->liveness_score,
            'checked_at'      => $now,
        ]);

        SendCheckInNotificationJob::dispatch($user->id, $attendance->id);

        return response()->json([
            'attendance' => $attendance,
            'checked_at' => $now->toISOString(),
        ], 201);
    }

    /**
     * GET /api/v1/attendance/today
     */
    public function today(): JsonResponse
    {
        $user  = TenantHelper::currentUser();
        $today = Carbon::today()->toDateString();

        $records = Attendance::with('session')
            ->where('user_id', $user->id)
            ->whereHas('session', fn($q) => $q->where('date', $today))
            ->orderBy('checked_at')
            ->get();

        $checkIn  = $records->firstWhere('type', 'checkin');
        $checkOut = $records->firstWhere('type', 'checkout');

        // Ambil sesi aktif saat ini (jika ada)
        $session = $this->attendanceService->getActiveSession($user->branch_id, Carbon::now());

        return response()->json([
            'checkin'  => $checkIn,
            'checkout' => $checkOut,
            'session'  => $session,
        ]);
    }

    /**
     * GET /api/v1/attendance/history
     */
    public function history(Request $request): JsonResponse
    {
        $user = TenantHelper::currentUser();

        $query = Attendance::with('session')
            ->where('user_id', $user->id)
            ->where('type', 'checkin')
            ->orderByDesc('checked_at');

        if ($request->filled('start_date')) {
            $query->whereHas('session', fn($q) => $q->where('date', '>=', $request->start_date));
        }

        if ($request->filled('end_date')) {
            $query->whereHas('session', fn($q) => $q->where('date', '<=', $request->end_date));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->integer('per_page', 15);

        return response()->json($query->paginate($perPage));
    }

    /**
     * GET /api/v1/attendance/all
     * Middleware: role:admin
     */
    public function all(Request $request): JsonResponse
    {
        $date = $request->input('date', Carbon::today()->toDateString());

        $query = Attendance::with(['user', 'session'])
            ->where('type', 'checkin')
            ->whereHas('session', fn($q) => $q->where('date', $date));

        if ($request->filled('branch_id')) {
            $query->whereHas('session', fn($q) => $q->where('branch_id', $request->branch_id));
        }

        if ($request->filled('department_id')) {
            $query->whereHas('user', fn($q) => $q->where('department_id', $request->department_id));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate($request->integer('per_page', 20)));
    }
}
