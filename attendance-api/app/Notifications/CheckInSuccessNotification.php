<?php
// =============================================================================
// app/Notifications/CheckInSuccessNotification.php
// =============================================================================
namespace App\Notifications;

use App\Jobs\SendFcmNotificationJob;
use App\Models\Attendance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CheckInSuccessNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Attendance $attendance)
    {
        $this->onQueue('notifications');
    }

    public function via($notifiable): array
    {
        // database = in-app; FCM dikirim via Job terpisah agar retry-able
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $checkedAt = $this->attendance->checked_at;

        // Dispatch FCM push secara terpisah
        if ($notifiable->fcm_token) {
            $label   = $this->attendance->type === 'checkin' ? 'Check-in' : 'Check-out';
            $timeStr = \Carbon\Carbon::parse($checkedAt)->setTimezone('Asia/Jakarta')->format('H:i');

            SendFcmNotificationJob::dispatch(
                $notifiable,
                "{$label} Berhasil",
                "Absensi {$label} tercatat pukul {$timeStr}",
                [
                    'type'       => 'attendance',
                    'status'     => $this->attendance->status,
                    'checked_at' => (string) $checkedAt,
                ]
            );
        }

        return [
            'title'      => $this->attendance->type === 'checkin' ? 'Check-in Berhasil' : 'Check-out Berhasil',
            'body'       => 'Absensi kamu telah tercatat',
            'type'       => 'attendance',
            'status'     => $this->attendance->status,
            'checked_at' => $checkedAt,
        ];
    }
}
