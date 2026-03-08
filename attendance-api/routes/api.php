<?php

// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\Branch\BranchController;
use App\Http\Controllers\Api\Face\FaceController;
use App\Http\Controllers\Api\Attendance\AttendanceController;
use App\Http\Controllers\Api\Attendance\SessionController;
use App\Http\Controllers\Api\Leave\LeaveController;
use App\Http\Controllers\Api\Leave\LeaveAttachmentController;
use App\Http\Controllers\Api\Notification\NotificationController;


Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware(['auth:api', 'tenant'])->group(function () {

        // Auth
        Route::post('/auth/logout',  [AuthController::class, 'logout']);
        Route::get('/auth/tokens',   [AuthController::class, 'tokens']);
        Route::get('/auth/me',       [AuthController::class, 'me']);

        // Admin-only resources
        Route::middleware(['role:admin|super-admin'])->group(function () {
            Route::apiResource('users',    UserController::class);
            Route::apiResource('branches', BranchController::class);
        });

        Route::prefix('face')->group(function () {
            Route::post('register', [FaceController::class, 'register']);
            Route::put('update',    [FaceController::class, 'update']);
            Route::get('status',    [FaceController::class, 'status']);
        });

        // Embedding sync — rate limited 10x/jam per user
        Route::middleware('throttle:10,60')->group(function () {
            Route::get('embeddings/sync', [FaceController::class, 'sync']);
        });

        Route::prefix('attendance')->group(function () {

            Route::post('checkin',  [AttendanceController::class, 'checkIn']);
            Route::post('checkout', [AttendanceController::class, 'checkOut']);
            Route::get('today',     [AttendanceController::class, 'today']);
            Route::get('history',   [AttendanceController::class, 'history']);

            Route::middleware('role:admin|super-admin')->group(function () {
                Route::get('all', [AttendanceController::class, 'all']);
            });

                Route::get('sessions',        [SessionController::class, 'index']);

            Route::middleware('role:admin|super-admin')->group(function () {
                Route::post('sessions/bulk',    [SessionController::class, 'bulk']);
                Route::post('sessions',         [SessionController::class, 'store']);
                Route::put('sessions/{id}',     [SessionController::class, 'update']);
                Route::delete('sessions/{id}',  [SessionController::class, 'destroy']);
            });
        });

        Route::prefix('leaves')->group(function () {
            Route::get('/',         [LeaveController::class, 'index']);
            Route::post('/request', [LeaveController::class, 'store']);

            // PENTING: /balance harus didaftarkan SEBELUM /{id}
            Route::get('/balance',  [LeaveController::class, 'balance']);

            Route::get('/{id}',     [LeaveController::class, 'show']);
            Route::delete('/{id}',  [LeaveController::class, 'destroy']);

            Route::middleware('role:admin')->group(function () {
                Route::put('/{id}/approve', [LeaveController::class, 'approve']);
                Route::put('/{id}/reject',  [LeaveController::class, 'reject']);
            });
        });
        Route::get('/leaves/{id}/attachment', [LeaveAttachmentController::class, 'show'])
            ->name('leave.attachment')
            ->middleware('signed');

        Route::get('/leave-types', [LeaveController::class, 'leaveTypes']);
    });

    Route::prefix('notifications')->group(function () {
        // PENTING: route static harus didaftarkan SEBELUM /{id}
        Route::get('/unread-count',    [NotificationController::class, 'unreadCount']);   // GET
        Route::post('/mark-read',      [NotificationController::class, 'markRead']);      // POST
        Route::post('/mark-all-read',  [NotificationController::class, 'markAllRead']);   // POST
        Route::post('/fcm-token',      [NotificationController::class, 'updateFcmToken']); // POST

        Route::get('/',       [NotificationController::class, 'index']); // GET  /api/v1/notifications
        Route::get('/{id}',   [NotificationController::class, 'show']);  // GET  /api/v1/notifications/{id}
    });
});
