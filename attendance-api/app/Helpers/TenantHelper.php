<?php

namespace App\Helpers;

use App\Models\User;

class TenantHelper
{
    /**
     * Get the current active tenant ID.
     *
     * Hanya membaca dari app container — TIDAK fallback ke auth()->user() langsung.
     * Ini mencegah TenantScope aktif di luar request context (queue, artisan).
     * Middleware EnsureTenantAccess yang bertanggung jawab men-set nilai ini.
     */
    public static function currentTenantId(): ?int
    {
        if (app()->has('current_tenant_id')) {
            return app('current_tenant_id');
        }

        return null;
    }

    /**
     * Get the current authenticated user via JWT guard.
     */
    public static function currentUser(): ?User
    {
        /** @var User|null */
        return auth('api')->user();
    }

    /**
     * Determine if the current user has the 'admin' role.
     */
    public static function isAdmin(): bool
    {
        $user = self::currentUser();

        return $user?->hasRole('admin') ?? false;
    }

    /**
     * Determine if the current user has the 'super-admin' role.
     */
    public static function isSuperAdmin(): bool
    {
        $user = self::currentUser();

        return $user?->hasRole('super-admin') ?? false;
    }
}

// ---------------------------------------------------------------------------
// Procedural helper functions (di-autoload via composer.json files[])
// ---------------------------------------------------------------------------

if (!function_exists('currentTenantId')) {
    function currentTenantId(): ?int
    {
        return TenantHelper::currentTenantId();
    }
}

if (!function_exists('currentUser')) {
    function currentUser(): ?User
    {
        return TenantHelper::currentUser();
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin(): bool
    {
        return TenantHelper::isAdmin();
    }
}

if (!function_exists('isSuperAdmin')) {
    function isSuperAdmin(): bool
    {
        return TenantHelper::isSuperAdmin();
    }
}
