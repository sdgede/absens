<?php
// =============================================================================
// app/Notifications/LeaveRejectedNotification.php
// =============================================================================
namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LeaveRejectedNotification extends Notification implements ShouldQueue
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
            'title'        => 'Izin Ditolak ❌',
            'body'         => "Pengajuan {$this->leaveRequest->leaveType->name} kamu ditolak. Alasan: {$this->leaveRequest->rejection_reason}",
            'type'         => 'leave_rejected',
            'reference_id' => $this->leaveRequest->id,
        ];
    }
}
