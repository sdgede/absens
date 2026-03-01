<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\User;
use App\Notifications\CheckInNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCheckInNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $userId,
        public readonly int $attendanceId,
    ) {}

    public function handle(): void
    {
        $user       = User::find($this->userId);
        $attendance = Attendance::find($this->attendanceId);

        if (!$user || !$attendance) {
            return;
        }

        $user->notify(new CheckInNotification($attendance));
    }
}
