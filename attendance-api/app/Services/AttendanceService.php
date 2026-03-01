<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\AttendanceSummary;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Tentukan status absensi berdasarkan waktu check-in vs batas terlambat.
     */
    public function determineStatus(AttendanceSession $session, Carbon $time): string
    {
        // Gabungkan tanggal session dengan jam late_after untuk perbandingan
        $lateAfter = Carbon::parse(
            $session->date->format('Y-m-d') . ' ' . $session->late_after
        );

        return $time->lte($lateAfter) ? 'present' : 'late';
    }

    /**
     * Cek apakah user sudah check-in hari ini pada sesi tertentu.
     */
    public function hasCheckedInToday(int $userId, int $sessionId): bool
    {
        return Attendance::where('user_id', $userId)
            ->where('session_id', $sessionId)
            ->where('type', 'checkin')
            ->exists();
    }

    /**
     * Cek apakah user sudah check-out hari ini pada sesi tertentu.
     */
    public function hasCheckedOutToday(int $userId, int $sessionId): bool
    {
        return Attendance::where('user_id', $userId)
            ->where('session_id', $sessionId)
            ->where('type', 'checkout')
            ->exists();
    }

    /**
     * Ambil sesi absensi aktif untuk branch tertentu pada waktu sekarang.
     * Sesi dianggap aktif jika:
     * - tanggal = hari ini
     * - is_active = true
     * - $now berada dalam rentang check_in_start - check_in_end
     */
    public function getActiveSession(int $branchId, Carbon $now): ?AttendanceSession
    {
        $today   = $now->toDateString();
        $nowTime = $now->format('H:i:s');

        return AttendanceSession::where('branch_id', $branchId)
            ->where('date', $today)
            ->where('is_active', true)
            ->where('check_in_start', '<=', $nowTime)
            ->where('check_in_end', '>=', $nowTime)
            ->first();
    }

    /**
     * Recalculate dan upsert attendance_summaries untuk user + bulan + tahun.
     */
    public function updateMonthlySummary(int $userId, Carbon $date): void
    {
        $month = (int) $date->format('m');
        $year  = (int) $date->format('Y');

        // Ambil semua checkin user pada bulan & tahun tersebut
        $attendances = Attendance::where('user_id', $userId)
            ->where('type', 'checkin')
            ->whereHas('session', function ($q) use ($month, $year) {
                $q->whereMonth('date', $month)
                    ->whereYear('date', $year);
            })
            ->get();

        $totalPresent = $attendances->where('status', 'present')->count();
        $totalLate    = $attendances->where('status', 'late')->count();
        $totalLeave   = $attendances->whereIn('status', ['leave', 'sick'])->count();

        // Hitung hari kerja pada bulan tersebut (kasar: jumlah hari non-weekend)
        // Ini bisa disesuaikan dengan tabel holidays jika diperlukan
        $daysInMonth  = $date->daysInMonth;
        $workingDays  = 0;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $day = Carbon::create($year, $month, $d);
            if (!$day->isWeekend()) {
                $workingDays++;
            }
        }

        $totalAbsent = max(0, $workingDays - $totalPresent - $totalLate - $totalLeave);

        AttendanceSummary::updateOrCreate(
            [
                'user_id' => $userId,
                'month'   => $month,
                'year'    => $year,
            ],
            [
                'total_present' => $totalPresent,
                'total_late'    => $totalLate,
                'total_absent'  => $totalAbsent,
                'total_leave'   => $totalLeave,
            ]
        );
    }
}
