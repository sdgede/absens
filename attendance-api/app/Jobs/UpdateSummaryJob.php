<?php

namespace App\Jobs;

use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateSummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $userId,
        public readonly Carbon $date,
    ) {}

    public function handle(AttendanceService $attendanceService): void
    {
        $attendanceService->updateMonthlySummary($this->userId, $this->date);
    }
}
