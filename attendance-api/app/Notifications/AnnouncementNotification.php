<?php
// =============================================================================
// app/Notifications/AnnouncementNotification.php
// =============================================================================
namespace App\Notifications;

use App\Jobs\SendFcmNotificationJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AnnouncementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $title,
        public readonly string $content
    ) {
        $this->onQueue('notifications');
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        // FCM push
        if ($notifiable->fcm_token) {
            SendFcmNotificationJob::dispatch(
                $notifiable,
                $this->title,
                $this->content,
                [
                    'type'    => 'announcement',
                    'content' => $this->content,
                ]
            );
        }

        return [
            'title'   => $this->title,
            'body'    => $this->content,
            'type'    => 'announcement',
            'content' => $this->content,
        ];
    }
}
