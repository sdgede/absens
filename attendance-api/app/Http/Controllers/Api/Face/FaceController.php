<?php

namespace App\Http\Controllers\Api\Face;

use App\Http\Controllers\Controller;
use App\Http\Requests\Face\RegisterFaceRequest;
use App\Models\FaceEmbedding;
use App\Models\User;
use App\Notifications\FaceRegisteredNotification;
use App\Services\FaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FaceController extends Controller
{
    public function __construct(private readonly FaceService $faceService) {}


    public function register(RegisterFaceRequest $request): JsonResponse
    {
        $user      = $this->resolveTargetUser($request);
        $embedding = $request->validated('embedding');

        if (!$this->faceService->validateEmbedding($embedding)) {
            return response()->json([
                'success' => false,
                'message' => 'Embedding tidak valid. Pastikan terdiri dari 128 nilai float dalam range [-1.0, 1.0].',
            ], 422);
        }

        $record = FaceEmbedding::updateOrCreate(
            ['user_id' => $user->id],
            [
                'embedding_data' => $embedding,
                'is_active'      => true,
                'registered_at'  => now(),
            ]
        );

        $this->invalidateSyncCache($user->tenant_id);

        // Notifikasi ke user
        $user->notify(new FaceRegisteredNotification($record));

        return response()->json([
            'success'       => true,
            'message'       => 'Wajah berhasil didaftarkan.',
            'registered_at' => $record->registered_at,
        ]);
    }


    public function update(RegisterFaceRequest $request): JsonResponse
    {
        $user      = $this->resolveTargetUser($request);
        $embedding = $request->validated('embedding');

        if (!$this->faceService->validateEmbedding($embedding)) {
            return response()->json([
                'success' => false,
                'message' => 'Embedding tidak valid.',
            ], 422);
        }

        $record = FaceEmbedding::where('user_id', $user->id)->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Embedding belum terdaftar. Gunakan endpoint register terlebih dahulu.',
            ], 404);
        }

        $record->update([
            'embedding_data' => $embedding,
            'is_active'      => true,
            'registered_at'  => now(),
            'version'        => $record->version + 1,
        ]);

        $this->invalidateSyncCache($user->tenant_id);

        $user->notify(new FaceRegisteredNotification($record));

        return response()->json([
            'success'       => true,
            'message'       => 'Embedding wajah berhasil diperbarui.',
            'version'       => $record->version,
            'registered_at' => $record->registered_at,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/v1/face/status
    // -------------------------------------------------------------------------

    /**
     * Cek apakah user yang login sudah punya embedding aktif.
     */
    public function status(Request $request): JsonResponse
    {
        $record = auth('api')->user()
            ->faceEmbedding()
            ->select(['is_active', 'registered_at', 'version'])
            ->first();

        if (!$record || !$record->is_active) {
            return response()->json([
                'success'  => true,
                'has_face' => false,
            ]);
        }

        return response()->json([
            'success'       => true,
            'has_face'      => true,
            'registered_at' => $record->registered_at,
            'version'       => $record->version,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/v1/embeddings/sync
    // -------------------------------------------------------------------------

    /**
     * Ambil semua embedding aktif untuk tenant ini (untuk device absensi lokal).
     * Di-cache di Redis 1 jam. Rate limited 10x/jam per user.
     *
     *     embedding_data di-SELECT eksplisit — tidak ikut TenantScope select *
     *     agar cast EmbeddingCast bisa bekerja dengan benar.
     */
    public function sync(Request $request): JsonResponse
    {
        $tenantId = currentTenantId();
        $userId   = auth('api')->id();

        // Log setiap akses sync
        Log::channel('daily')->info('embeddings.sync accessed', [
            'user_id'    => $userId,
            'tenant_id'  => $tenantId,
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'at'         => now()->toIso8601String(),
        ]);

        $cacheKey = "embeddings_{$tenantId}";

        $data = Cache::remember($cacheKey, now()->addHour(), function () use ($tenantId) {
            return FaceEmbedding::query()
                ->with('user:id,name,tenant_id')
                ->where('is_active', true)
                ->whereHas('user', fn($q) => $q->where('tenant_id', $tenantId)->where('is_active', true))
                ->get()
                ->map(fn($record) => [
                    'user_id'   => $record->user_id,
                    'name'      => $record->user->name,
                    'embedding' => $record->embedding_data,  // dekripsi otomatis via EmbeddingCast
                ])
                ->values()
                ->all();
        });

        return response()->json([
            'success' => true,
            'count'   => count($data),
            'data'    => $data,
        ]);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Resolve target user dari request.
     * Admin bisa daftarkan embedding user lain via user_id.
     * User biasa hanya bisa daftarkan milik sendiri.
     */
    private function resolveTargetUser(Request $request): User
    {
        $requestedUserId = $request->validated('user_id');
        $authUser        = auth('api')->user();

        // Jika tidak kirim user_id, atau user_id = milik sendiri → pakai auth user
        if (!$requestedUserId || $requestedUserId === $authUser->id) {
            return $authUser;
        }

        // Hanya admin/super-admin yang boleh daftarkan user lain
        if (!$authUser->hasAnyRole(['admin', 'super-admin'])) {
            abort(403, 'Anda tidak memiliki izin untuk mendaftarkan wajah user lain.');
        }

        return User::where('id', $requestedUserId)
            ->where('tenant_id', $authUser->tenant_id)  // pastikan satu tenant
            ->firstOrFail();
    }

    /**
     * Hapus cache sync embeddings untuk tenant tertentu.
     */
    private function invalidateSyncCache(int $tenantId): void
    {
        Cache::forget("embeddings_{$tenantId}");
    }
}
