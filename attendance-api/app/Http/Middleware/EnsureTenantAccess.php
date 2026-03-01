<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAccess
{
    /**
     * Handle an incoming request.
     *
     * Super-admin dapat mengakses semua tenant.
     * User biasa hanya dapat mengakses tenant miliknya sendiri.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        // Guard: harus sudah terautentikasi sebelum middleware ini
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Super-admin bypass tenant restriction — set null agar TenantScope tidak aktif
        if ($user->hasRole('super-admin')) {
            app()->instance('current_tenant_id', null);
            return $next($request);
        }

        $userTenantId = $user->tenant_id;

        if (!$userTenantId) {
            return response()->json(['message' => 'User tidak terdaftar di tenant manapun.'], 403);
        }

        // ✅ Proteksi 403: cek apakah request mencoba mengakses tenant lain
        // Tangkap tenant_id dari semua kemungkinan sumber request
        $requestedTenantId = $request->route('tenant_id')    // route param
            ?? $request->input('tenant_id')                  // body / query param
            ?? $request->header('X-Tenant-ID');              // custom header (opsional)

        if ($requestedTenantId !== null && (int) $requestedTenantId !== (int) $userTenantId) {
            return response()->json([
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses data tenant ini.',
            ], 403);
        }

        // Simpan di app container agar TenantScope bisa membacanya
        app()->instance('current_tenant_id', $userTenantId);

        return $next($request);
    }
}
