<?php

namespace App\Notifications;

use App\Models\Attendance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CheckInNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Attendance $attendance) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->fcm_token) {
            $channels[] = 'fcm';
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        $status  = $this->attendance->status === 'late' ? 'Terlambat' : 'Tepat Waktu';
        $type    = $this->attendance->type === 'checkin' ? 'Check-In' : 'Check-Out';
        $time    = $this->attendance->checked_at->format('H:i');
        $date    = $this->attendance->checked_at->format('d M Y');

        return [
            'title'        => "{$type} Berhasil",
            'body'         => "Anda berhasil {$type} pukul {$time} pada {$date}. Status: {$status}.",
            'type'         => 'attendance_checkin',
            'reference_id' => $this->attendance->id,
            'status'       => $this->attendance->status,
        ];
    }
}
