<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendFcmNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int   $tries   = 3;
    public array $backoff = [10, 60, 300]; // detik: retry ke-1, ke-2, ke-3
    public int   $timeout = 30;

    public function __construct(
        public readonly User   $user,
        public readonly string $title,
        public readonly string $body,
        public readonly array  $data = []
    ) {
        $this->onQueue('notifications');
    }

    public function handle(NotificationService $notificationService): void
    {
        $notificationService->sendFcmNotification($this->user, $this->title, $this->body, $this->data);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendFcmNotificationJob: gagal setelah semua retry', [
            'user_id' => $this->user->id,
            'title'   => $this->title,
            'error'   => $exception->getMessage(),
        ]);
    }
}
