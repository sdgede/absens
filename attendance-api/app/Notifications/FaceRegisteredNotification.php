<?php

namespace App\Notifications;

use App\Models\FaceEmbedding;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class FaceRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly FaceEmbedding $embedding) {}

    public function via(object $notifiable): array
    {
        // Kirim ke DB (tabel notifications) dan FCM jika fcm_token ada
        $channels = ['database'];

        if ($notifiable->fcm_token) {
            $channels[] = 'fcm'; // pakai package seperti laravel-fcm atau kreait/laravel-firebase
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'        => 'Wajah Berhasil Didaftarkan',
            'body'         => 'Data wajah Anda telah berhasil didaftarkan pada ' .
                $this->embedding->registered_at->format('d M Y H:i'),
            'type'         => 'face_registered',
            'reference_id' => $this->embedding->id,
        ];
    }
}
