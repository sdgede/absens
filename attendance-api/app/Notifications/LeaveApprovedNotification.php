<?php
// =============================================================================
// app/Notifications/LeaveApprovedNotification.php
// =============================================================================
namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LeaveApprovedNotification extends Notification implements ShouldQueue
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
            'title'        => 'Izin Disetujui ✅',
            'body'         => "Pengajuan {$this->leaveRequest->leaveType->name} kamu ({$this->leaveRequest->total_days} hari) telah disetujui",
            'type'         => 'leave_approved',
            'reference_id' => $this->leaveRequest->id,
        ];
    }
}
