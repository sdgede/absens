<?php

namespace App\Http\Controllers\Api\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaveAttachmentController extends Controller
{
    /**
     * GET /api/v1/leaves/{id}/attachment
     *
     * Serve file attachment dari private storage.
     * Route harus pakai ->middleware('signed') agar hanya URL yang
     * digenerate server yang bisa mengakses.
     */
    public function show(Request $request, int $id): StreamedResponse
    {
        // Validasi signed URL — jika tidak valid / expired, Laravel otomatis 403
        if (! $request->hasValidSignature()) {
            abort(403, 'Link lampiran tidak valid atau sudah kadaluarsa');
        }

        $user  = auth()->user();

        // Ambil leave request — pastikan dalam tenant yang sama
        $leave = LeaveRequest::whereHas('user', fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->where('id', $id)
            ->firstOrFail();

        // Employee hanya bisa akses milik sendiri
        if (! $user->hasRole('admin') && $leave->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke lampiran ini');
        }

        if (! $leave->attachment_path || ! Storage::disk('private')->exists($leave->attachment_path)) {
            abort(404, 'File lampiran tidak ditemukan');
        }

        $mimeType = Storage::disk('private')->mimeType($leave->attachment_path);
        $fileName = basename($leave->attachment_path);

        return Storage::disk('private')->download(
            $leave->attachment_path,
            $fileName,
            ['Content-Type' => $mimeType]
        );
    }
}
