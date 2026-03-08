<?php

namespace App\Services;

use App\Jobs\SendFcmNotificationJob;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    // =========================================================================
    // Kirim FCM ke satu user — dipanggil dari dalam Job
    // =========================================================================
    public function sendFcmNotification(User $user, string $title, string $body, array $data = []): bool
    {
        if (! $user->fcm_token) {
            return false;
        }

        $projectId = config('services.firebase.project_id');

        if (! $projectId) {
            Log::warning('FCM: FIREBASE_PROJECT_ID belum diset');
            return false;
        }

        try {
            $accessToken = $this->getAccessToken();

            // Semua value di data payload FCM harus string
            $stringData = array_map('strval', $data);

            $response = Http::timeout(15)
                ->withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                    'message' => [
                        'token'        => $user->fcm_token,
                        'notification' => [
                            'title' => $title,
                            'body'  => $body,
                        ],
                        'data'    => $stringData,
                        'android' => ['priority' => 'high'],
                        'apns'    => ['headers' => ['apns-priority' => '10']],
                    ],
                ]);

            if ($response->successful()) {
                return true;
            }

            $status   = $response->status();
            $fcmError = $response->json('error.status') ?? 'UNKNOWN';

            Log::warning('FCM: kirim gagal', [
                'user_id'   => $user->id,
                'http_code' => $status,
                'fcm_error' => $fcmError,
            ]);

            // Token tidak valid → bersihkan
            if ($status === 404 || $fcmError === 'UNREGISTERED') {
                $user->updateQuietly(['fcm_token' => null]);
            }

            return false;
        } catch (\Throwable $e) {
            Log::error('FCM: exception saat kirim', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
            throw $e; // biarkan Job handle retry
        }
    }

    // =========================================================================
    // Dispatch FCM job ke banyak user
    // =========================================================================
    public function sendToMultiple(Collection|array $users, string $title, string $body, array $data = []): void
    {
        foreach ($users as $user) {
            if (! $user->fcm_token) {
                continue;
            }

            SendFcmNotificationJob::dispatch($user, $title, $body, $data);
        }
    }

    // =========================================================================
    // OAuth2 access token — cache 50 menit (token valid 1 jam)
    // =========================================================================
    public function getAccessToken(): string
    {
        return Cache::remember('fcm_oauth_access_token', now()->addMinutes(50), function () {
            $path = config('services.firebase.credentials_path');

            if (! file_exists($path)) {
                throw new \RuntimeException("Firebase credentials tidak ditemukan: {$path}");
            }

            $sa = json_decode(file_get_contents($path), true);

            if (empty($sa['client_email']) || empty($sa['private_key'])) {
                throw new \RuntimeException('Firebase credentials.json tidak valid');
            }

            $now    = time();
            $header = base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $claims = base64url_encode(json_encode([
                'iss'   => $sa['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ]));

            $signingInput = "{$header}.{$claims}";
            openssl_sign($signingInput, $signature, $sa['private_key'], 'SHA256');
            $jwt = $signingInput . '.' . base64url_encode($signature);

            $response = Http::timeout(10)
                ->asForm()
                ->post('https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion'  => $jwt,
                ]);

            if ($response->failed()) {
                throw new \RuntimeException('Gagal ambil FCM access token: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }
}
