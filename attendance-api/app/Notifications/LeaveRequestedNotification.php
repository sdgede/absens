<?php
// =============================================================================
// app/Notifications/LeaveRequestedNotification.php
// In-app (database) + FCM sudah di-handle via Job terpisah
// =============================================================================
namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LeaveRequestedNotification extends Notification implements ShouldQueue
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
        return [
            'title'        => 'Pengajuan Izin Baru',
            'body'         => "{$this->leaveRequest->user->name} mengajukan {$this->leaveRequest->leaveType->name} ({$this->leaveRequest->total_days} hari)",
            'type'         => 'leave_requested',
            'reference_id' => $this->leaveRequest->id,
        ];
    }
}
