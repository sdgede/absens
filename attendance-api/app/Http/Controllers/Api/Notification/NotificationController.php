<?php

namespace App\Http\Controllers\Api\Notification;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    // =========================================================================
    // GET /api/v1/notifications
    // =========================================================================
    public function index(Request $request): JsonResponse
    {
        try {
            $user    = auth()->user();
            $perPage = (int) $request->input('per_page', 20);
            $perPage = min($perPage, 100); // maksimal 100

            $notifications = $user->notifications()
                ->latest()
                ->paginate($perPage);

            // Unread count dari cache atau hitung ulang
            $unreadCount = $this->getUnreadCount($user);

            // Format data agar konsisten — kolom 'data' sudah json, perlu decode
            $items = $notifications->getCollection()->map(fn($n) => [
                'id'         => $n->id,
                'data'       => $n->data,
                'read_at'    => $n->read_at,
                'created_at' => $n->created_at,
            ]);

            return response()->json([
                'success'      => true,
                'message'      => 'Notifikasi berhasil diambil',
                'data'         => $items,
                'unread_count' => $unreadCount,
                'meta'         => [
                    'current_page' => $notifications->currentPage(),
                    'last_page'    => $notifications->lastPage(),
                    'per_page'     => $notifications->perPage(),
                    'total'        => $notifications->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    // =========================================================================
    // GET /api/v1/notifications/{id}  → mark as read saat dibuka
    // =========================================================================
    public function show(string $id): JsonResponse
    {
        try {
            $user = auth()->user();

            $notification = $user->notifications()->where('id', $id)->first();

            if (! $notification) {
                return ApiResponse::error('Notifikasi tidak ditemukan', 404);
            }

            // Mark as read jika belum
            if (! $notification->read_at) {
                $notification->markAsRead();
                $this->clearUnreadCache($user->id);
            }

            return ApiResponse::success([
                'id'         => $notification->id,
                'data'       => $notification->data,
                'read_at'    => $notification->read_at,
                'created_at' => $notification->created_at,
            ], 'Notifikasi berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    // =========================================================================
    // POST /api/v1/notifications/mark-read
    // Request: { ids: [uuid, ...] }
    // =========================================================================
    public function markRead(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids'   => ['required', 'array', 'min:1'],
                'ids.*' => ['required', 'uuid'],
            ], [
                'ids.required' => 'ids wajib diisi',
                'ids.array'    => 'ids harus berupa array',
                'ids.*.uuid'   => 'Setiap id harus berformat UUID',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Validasi gagal', 422, $validator->errors());
            }

            $user = auth()->user();

            $updated = $user->notifications()
                ->whereIn('id', $request->ids)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            $this->clearUnreadCache($user->id);

            return ApiResponse::success(
                ['marked_count' => $updated],
                "{$updated} notifikasi ditandai sudah dibaca"
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    // =========================================================================
    // POST /api/v1/notifications/mark-all-read
    // =========================================================================
    public function markAllRead(): JsonResponse
    {
        try {
            $user = auth()->user();

            $updated = $user->unreadNotifications()->update(['read_at' => now()]);

            $this->clearUnreadCache($user->id);

            return ApiResponse::success(
                ['marked_count' => $updated],
                'Semua notifikasi ditandai sudah dibaca'
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    // =========================================================================
    // POST /api/v1/notifications/fcm-token
    // Request: { token: string }
    // =========================================================================
    public function updateFcmToken(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => ['required', 'string', 'max:255'],
            ], [
                'token.required' => 'FCM token wajib diisi',
            ]);

            if ($validator->fails()) {
                return ApiResponse::error('Validasi gagal', 422, $validator->errors());
            }

            auth()->user()->update(['fcm_token' => $request->token]);

            return ApiResponse::success(null, 'FCM token berhasil diperbarui');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    // =========================================================================
    // GET /api/v1/notifications/unread-count
    // =========================================================================
    public function unreadCount(): JsonResponse
    {
        try {
            $user  = auth()->user();
            $count = $this->getUnreadCount($user);

            return ApiResponse::success(['count' => $count], 'Unread count berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    /**
     * Ambil unread count dari cache (30 detik) atau hitung dari DB.
     */
    private function getUnreadCount($user): int
    {
        return Cache::remember(
            "unread_notifications_{$user->id}",
            now()->addSeconds(30),
            fn() => $user->unreadNotifications()->count()
        );
    }

    /**
     * Hapus cache unread count saat ada perubahan status baca.
     */
    private function clearUnreadCache(int $userId): void
    {
        Cache::forget("unread_notifications_{$userId}");
    }
}
