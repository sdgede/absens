<?php

namespace App\Services;

use App\Exceptions\InsufficientLeaveBalanceException;
use App\Jobs\SendFcmNotificationJob;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Holiday;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Notifications\LeaveApprovedNotification;
use App\Notifications\LeaveRejectedNotification;
use App\Notifications\LeaveRequestedNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class LeaveService
{
    // =========================================================================
    // GET leaves (index)
    // =========================================================================
    public function getLeaves($user, array $filters): LengthAwarePaginator
    {
        $query = LeaveRequest::with(['leaveType', 'user', 'approvedBy'])
            ->whereHas('user', fn($q) => $q->where('tenant_id', $user->tenant_id));

        // Employee hanya lihat milik sendiri
        if (! $user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate(15);
    }

    // =========================================================================
    // CREATE leave request
    // =========================================================================
    public function createLeaveRequest($user, array $data, ?UploadedFile $attachment = null): array
    {
        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate   = Carbon::parse($data['end_date'])->startOfDay();
        $today     = Carbon::today();

        // 1. Validasi tanggal
        if ($startDate->lt($today)) {
            throw new \InvalidArgumentException('Tanggal mulai tidak boleh di masa lalu');
        }

        if ($startDate->gt($endDate)) {
            throw new \InvalidArgumentException('Tanggal mulai harus sebelum atau sama dengan tanggal selesai');
        }

        // 2. Hitung total_days (exclude weekend & hari libur)
        $totalDays = $this->calculateWorkingDays($startDate, $endDate, $user->tenant_id);

        if ($totalDays === 0) {
            throw new \InvalidArgumentException('Tidak ada hari kerja dalam rentang tanggal yang dipilih');
        }

        // 3. Cek saldo cuti — filter by year
        $currentYear = now()->year;
        $balance     = LeaveBalance::where('user_id', $user->id)
            ->where('leave_type_id', $data['leave_type_id'])
            ->where('year', $currentYear)
            ->first();

        $remaining = $balance ? ($balance->total_days - $balance->used_days) : 0;

        if ($remaining < $totalDays) {
            throw new InsufficientLeaveBalanceException(
                "Saldo cuti tidak mencukupi. Sisa: {$remaining} hari, dibutuhkan: {$totalDays} hari"
            );
        }

        return DB::transaction(function () use ($user, $data, $attachment, $totalDays) {
            // 4. Upload attachment ke storage/private
            $attachmentPath = null;
            if ($attachment) {
                $attachmentPath = $attachment->store(
                    "tenants/{$user->tenant_id}/leave-attachments",
                    'private'
                );
            }

            // 5. Buat LeaveRequest
            $leaveRequest = LeaveRequest::create([
                'user_id'         => $user->id,
                'leave_type_id'   => $data['leave_type_id'],
                'start_date'      => $data['start_date'],
                'end_date'        => $data['end_date'],
                'total_days'      => $totalDays,
                'reason'          => $data['reason'],
                'attachment_path' => $attachmentPath,
                'status'          => 'pending',
            ]);

            $leaveRequest->load(['leaveType', 'user']);

            // 6. Notifikasi ke semua admin tenant
            $admins = User::where('tenant_id', $user->tenant_id)
                ->role('admin')
                ->get();

            foreach ($admins as $admin) {
                // In-app notification (database) — semua admin
                $admin->notify(new LeaveRequestedNotification($leaveRequest));

                // FCM push — hanya admin yang punya fcm_token
                if ($admin->fcm_token) {
                    SendFcmNotificationJob::dispatch($admin, [
                        'title'        => 'Pengajuan Izin Baru',
                        'body'         => "{$user->name} mengajukan {$leaveRequest->leaveType->name} ({$leaveRequest->total_days} hari)",
                        'type'         => 'leave_requested',
                        'reference_id' => $leaveRequest->id,
                    ]);
                }
            }

            return $this->formatLeaveResponse($leaveRequest);
        });
    }

    // =========================================================================
    // SHOW
    // =========================================================================
    public function getLeaveById($user, int $id): array
    {
        $query = LeaveRequest::with(['leaveType', 'user', 'approvedBy'])
            ->whereHas('user', fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->where('id', $id);

        if (! $user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        $leave = $query->firstOrFail();

        return $this->formatLeaveResponse($leave);
    }

    // =========================================================================
    // CANCEL — update status = 'cancelled' (bukan delete)
    // =========================================================================
    public function cancelLeaveRequest($user, int $id): void
    {
        $leave = LeaveRequest::whereHas('user', fn($q) => $q->where('tenant_id', $user->tenant_id))
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($leave->status !== 'pending') {
            throw new \InvalidArgumentException(
                'Hanya pengajuan dengan status pending yang dapat dibatalkan'
            );
        }

        DB::transaction(function () use ($leave) {
            // Hapus attachment dari storage jika ada
            if ($leave->attachment_path) {
                Storage::disk('private')->delete($leave->attachment_path);
            }

            $leave->update([
                'status'          => 'cancelled',
                'attachment_path' => null,
            ]);
        });
    }

    // =========================================================================
    // APPROVE
    // =========================================================================
    public function approveLeaveRequest($admin, int $id): array
    {
        $leave = LeaveRequest::with(['leaveType', 'user'])
            ->whereHas('user', fn($q) => $q->where('tenant_id', $admin->tenant_id))
            ->where('id', $id)
            ->firstOrFail();

        if ($leave->status !== 'pending') {
            throw new \InvalidArgumentException(
                'Hanya pengajuan dengan status pending yang dapat disetujui'
            );
        }

        return DB::transaction(function () use ($admin, $leave) {
            // 1. Update status
            $leave->update([
                'status'      => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);

            // 2. Kurangi saldo — pastikan record tahun ini
            LeaveBalance::where('user_id', $leave->user_id)
                ->where('leave_type_id', $leave->leave_type_id)
                ->where('year', now()->year)
                ->increment('used_days', $leave->total_days);

            // 3. Update attendance records dalam rentang tanggal → status = 'leave'
            //    attendances terhubung via session_id → attendance_sessions.date
            $sessions = AttendanceSession::where('tenant_id', $admin->tenant_id)
                ->whereBetween('date', [$leave->start_date, $leave->end_date])
                ->pluck('id');

            if ($sessions->isNotEmpty()) {
                // Update yang sudah ada
                Attendance::where('user_id', $leave->user_id)
                    ->whereIn('session_id', $sessions)
                    ->update([
                        'status' => 'leave',
                        'note'   => "Disetujui: {$leave->leaveType->name} (ID #{$leave->id})",
                    ]);

                // Insert untuk session yang belum ada record attendance-nya
                $existingSessions = Attendance::where('user_id', $leave->user_id)
                    ->whereIn('session_id', $sessions)
                    ->pluck('session_id')
                    ->toArray();

                $missingSessions = $sessions->diff($existingSessions);

                foreach ($missingSessions as $sessionId) {
                    Attendance::create([
                        'user_id'    => $leave->user_id,
                        'session_id' => $sessionId,
                        'type'       => 'checkin',
                        'status'     => 'leave',
                        'checked_at' => now(),
                        'note'       => "Auto: {$leave->leaveType->name} (ID #{$leave->id})",
                    ]);
                }
            }

            // 4. Notifikasi ke karyawan
            $leave->user->notify(new LeaveApprovedNotification($leave));

            if ($leave->user->fcm_token) {
                SendFcmNotificationJob::dispatch($leave->user, [
                    'title'        => 'Izin Disetujui',
                    'body'         => "Pengajuan {$leave->leaveType->name} kamu telah disetujui",
                    'type'         => 'leave_approved',
                    'reference_id' => $leave->id,
                ]);
            }

            return $this->formatLeaveResponse($leave->fresh(['leaveType', 'user', 'approvedBy']));
        });
    }

    // =========================================================================
    // REJECT
    // =========================================================================
    public function rejectLeaveRequest($admin, int $id, string $rejectionReason): array
    {
        $leave = LeaveRequest::with(['leaveType', 'user'])
            ->whereHas('user', fn($q) => $q->where('tenant_id', $admin->tenant_id))
            ->where('id', $id)
            ->firstOrFail();

        if ($leave->status !== 'pending') {
            throw new \InvalidArgumentException(
                'Hanya pengajuan dengan status pending yang dapat ditolak'
            );
        }

        DB::transaction(function () use ($admin, $leave, $rejectionReason) {
            $leave->update([
                'status'           => 'rejected',
                'approved_by'      => $admin->id,
                'approved_at'      => now(),
                'rejection_reason' => $rejectionReason,
            ]);

            // Notifikasi ke karyawan
            $leave->user->notify(new LeaveRejectedNotification($leave));

            if ($leave->user->fcm_token) {
                SendFcmNotificationJob::dispatch($leave->user, [
                    'title'        => 'Izin Ditolak',
                    'body'         => "Pengajuan {$leave->leaveType->name} kamu ditolak: {$rejectionReason}",
                    'type'         => 'leave_rejected',
                    'reference_id' => $leave->id,
                ]);
            }
        });

        return $this->formatLeaveResponse($leave->fresh(['leaveType', 'user', 'approvedBy']));
    }

    // =========================================================================
    // BALANCE — saldo per tahun berjalan
    // =========================================================================
    public function getLeaveBalance($user): array
    {
        $currentYear = now()->year;

        $balances = LeaveBalance::with('leaveType')
            ->where('user_id', $user->id)
            ->where('year', $currentYear)
            ->get()
            ->map(fn($b) => [
                'leave_type_id'  => $b->leave_type_id,
                'leave_type'     => $b->leaveType->name ?? '-',
                'is_paid'        => $b->leaveType->is_paid ?? true,
                'year'           => $b->year,
                'total_days'     => $b->total_days,
                'used_days'      => $b->used_days,
                'remaining_days' => $b->total_days - $b->used_days,
            ]);

        return [
            'year'     => $currentYear,
            'balances' => $balances,
        ];
    }

    // =========================================================================
    // LEAVE TYPES — aktif untuk tenant
    // =========================================================================
    public function getLeaveTypes(int $tenantId): Collection
    {
        return LeaveType::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get(['id', 'name', 'max_days_per_year', 'is_paid', 'requires_attachment']);
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    /**
     * Resolve URL untuk attachment.
     * FILESYSTEM_DISK=local → pakai signed route (expired 30 menit).
     * Jika nanti pindah ke S3, ganti isi method ini dengan:
     *   return Storage::disk('private')->temporaryUrl($path, now()->addMinutes(30));
     */
    private function resolveAttachmentUrl(int $leaveId, string $path): string
    {
        return URL::temporarySignedRoute(
            'leave.attachment',
            now()->addMinutes(30),
            ['id' => $leaveId]
        );
    }

    /**
     * Hitung hari kerja antara dua tanggal.
     * Exclude: Sabtu, Minggu, hari libur nasional, dan hari libur tenant.
     */
    private function calculateWorkingDays(Carbon $start, Carbon $end, int $tenantId): int
    {
        // Ambil semua holiday dalam rentang sekaligus (1 query)
        $holidays = Holiday::where(function ($q) use ($tenantId) {
            $q->whereNull('tenant_id')           // nasional
                ->orWhere('tenant_id', $tenantId); // atau milik tenant
        })
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->toArray();

        $days    = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if (! $current->isWeekend() && ! in_array($current->toDateString(), $holidays)) {
                $days++;
            }
            $current->addDay();
        }

        return $days;
    }

    /**
     * Format response LeaveRequest menjadi array yang konsisten.
     */
    private function formatLeaveResponse(LeaveRequest $leave): array
    {
        return [
            'id'               => $leave->id,
            'leave_type'       => [
                'id'   => $leave->leaveType->id,
                'name' => $leave->leaveType->name,
            ],
            'user'             => [
                'id'          => $leave->user->id,
                'name'        => $leave->user->name,
                'employee_id' => $leave->user->employee_id,
            ],
            'start_date'       => $leave->start_date,
            'end_date'         => $leave->end_date,
            'total_days'       => $leave->total_days,
            'reason'           => $leave->reason,
            'attachment_path'  => $leave->attachment_path
                ? $this->resolveAttachmentUrl($leave->id, $leave->attachment_path)
                : null,
            'status'           => $leave->status,
            'approved_by'      => $leave->approvedBy ? [
                'id'   => $leave->approvedBy->id,
                'name' => $leave->approvedBy->name,
            ] : null,
            'approved_at'      => $leave->approved_at,
            'rejection_reason' => $leave->rejection_reason,
            'created_at'       => $leave->created_at,
        ];
    }
}
