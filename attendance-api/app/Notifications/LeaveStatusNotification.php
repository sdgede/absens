<?php
// =============================================================================
// app/Notifications/LeaveStatusNotification.php
// =============================================================================
namespace App\Notifications;

use App\Jobs\SendFcmNotificationJob;
use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LeaveStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly LeaveRequest $leaveRequest)
    {
        $this->onQueue('notifications');
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $status = $this->leaveRequest->status;

        [$title, $body] = match ($status) {
            'approved' => [
                'Izin Disetujui ✅',
                "Pengajuan {$this->leaveRequest->leaveType->name} ({$this->leaveRequest->total_days} hari) telah disetujui",
            ],
            'rejected' => [
                'Izin Ditolak ❌',
                "Pengajuan {$this->leaveRequest->leaveType->name} ditolak. Alasan: {$this->leaveRequest->rejection_reason}",
            ],
            default => [
                'Status Izin Diperbarui',
                "Status pengajuan {$this->leaveRequest->leaveType->name} berubah menjadi {$status}",
            ],
        };

        // FCM push
        if ($notifiable->fcm_token) {
            SendFcmNotificationJob::dispatch(
                $notifiable,
                $title,
                $body,
                array_filter([
                    'type'     => 'leave',
                    'leave_id' => (string) $this->leaveRequest->id,
                    'status'   => $status,
                    'reason'   => $this->leaveRequest->rejection_reason ?? '',
                ])
            );
        }

        return array_filter([
            'title'    => $title,
            'body'     => $body,
            'type'     => 'leave',
            'leave_id' => $this->leaveRequest->id,
            'status'   => $status,
            'reason'   => $this->leaveRequest->rejection_reason,
        ]);
    }
}
