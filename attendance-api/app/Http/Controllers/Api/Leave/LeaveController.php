<?php

namespace App\Http\Controllers\Api\Leave;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\LeaveRejectRequest;
use App\Http\Requests\Leave\LeaveRequestStore;
use App\Services\LeaveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function __construct(
        protected LeaveService $leaveService
    ) {}

    // =========================================================================
    // GET /api/v1/leaves
    // =========================================================================
    public function index(Request $request): JsonResponse
    {
        try {
            $user    = auth()->user();
            $filters = $request->only(['status', 'page']);

            $leaves = $this->leaveService->getLeaves($user, $filters);

            return ApiResponse::paginated($leaves, 'Data izin/cuti berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    // =========================================================================
    // POST /api/v1/leaves/request
    // =========================================================================
    public function store(LeaveRequestStore $request): JsonResponse
    {
        try {
            $user   = auth()->user();
            $result = $this->leaveService->createLeaveRequest(
                $user,
                $request->validated(),
                $request->file('attachment')
            );

            return ApiResponse::success($result, 'Pengajuan izin/cuti berhasil dibuat', 201);
        } catch (\App\Exceptions\InsufficientLeaveBalanceException $e) {
            return ApiResponse::error($e->getMessage(), 422, [
                'leave_balance' => [$e->getMessage()],
            ]);
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    // =========================================================================
    // GET /api/v1/leaves/balance   ← harus SEBELUM /{id}
    // =========================================================================
    public function balance(): JsonResponse
    {
        try {
            $user   = auth()->user();
            $result = $this->leaveService->getLeaveBalance($user);

            return ApiResponse::success($result, 'Saldo cuti berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    // =========================================================================
    // GET /api/v1/leaves/{id}
    // =========================================================================
    public function show(int $id): JsonResponse
    {
        try {
            $user  = auth()->user();
            $leave = $this->leaveService->getLeaveById($user, $id);

            return ApiResponse::success($leave, 'Detail izin/cuti berhasil diambil');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Data izin/cuti tidak ditemukan', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    // =========================================================================
    // DELETE /api/v1/leaves/{id}  → set status = cancelled
    // =========================================================================
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = auth()->user();
            $this->leaveService->cancelLeaveRequest($user, $id);

            return ApiResponse::success(null, 'Pengajuan izin/cuti berhasil dibatalkan');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Data izin/cuti tidak ditemukan', 404);
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    // =========================================================================
    // PUT /api/v1/leaves/{id}/approve  [middleware: role:admin]
    // =========================================================================
    public function approve(int $id): JsonResponse
    {
        try {
            $admin  = auth()->user();
            $result = $this->leaveService->approveLeaveRequest($admin, $id);

            return ApiResponse::success($result, 'Pengajuan izin/cuti berhasil disetujui');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Data izin/cuti tidak ditemukan', 404);
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    // =========================================================================
    // PUT /api/v1/leaves/{id}/reject  [middleware: role:admin]
    // =========================================================================
    public function reject(LeaveRejectRequest $request, int $id): JsonResponse
    {
        try {
            $admin  = auth()->user();
            $result = $this->leaveService->rejectLeaveRequest(
                $admin,
                $id,
                $request->validated('rejection_reason')
            );

            return ApiResponse::success($result, 'Pengajuan izin/cuti berhasil ditolak');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ApiResponse::error('Data izin/cuti tidak ditemukan', 404);
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    // =========================================================================
    // GET /api/v1/leave-types
    // =========================================================================
    public function leaveTypes(): JsonResponse
    {
        try {
            $user       = auth()->user();
            $leaveTypes = $this->leaveService->getLeaveTypes($user->tenant_id);

            return ApiResponse::success($leaveTypes, 'Jenis izin/cuti berhasil diambil');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
