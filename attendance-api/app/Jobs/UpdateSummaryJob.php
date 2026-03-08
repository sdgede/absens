<?php

namespace App\Jobs;

use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateAttendanceSummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;
    public int $timeout = 120;

    /**
     * Simpan sebagai string (Y-m-d) karena Carbon tidak serializable dengan aman di queue.
     * Di-parse kembali ke Carbon saat handle().
     *
     * @param int    $userId
     * @param string $date    Format: 'Y-m-d', mis. '2026-03-08'
     */
    public function __construct(
        public readonly int    $userId,
        public readonly string $date   // Y-m-d string, bukan Carbon — aman untuk serialize
    ) {
        $this->onQueue('default');
    }

    public function handle(AttendanceService $attendanceService): void
    {
        $attendanceService->updateMonthlySummary(
            $this->userId,
            Carbon::parse($this->date)
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('UpdateAttendanceSummaryJob: gagal', [
            'user_id' => $this->userId,
            'date'    => $this->date,
            'error'   => $exception->getMessage(),
        ]);
    }
}
