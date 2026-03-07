<?php


namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\StoreSessionRequest;
use App\Models\AttendanceSession;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use App\Helpers\TenantHelper;
use Illuminate\Http\Request;
class SessionController extends Controller
{
    /**
     * GET /api/v1/attendance/sessions
     */
    public function index(Request $request): JsonResponse
    {
        $query = AttendanceSession::with('branch')
            ->orderBy('date')
            ->orderBy('check_in_start');

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }

        return response()->json(['sessions' => $query->get()]);
    }

    /**
     * POST /api/v1/attendance/sessions
     * Middleware: role:admin
     */
    public function store(StoreSessionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = TenantHelper::currentTenantId();

        $session = AttendanceSession::create($data);

        return response()->json([
            'message' => 'Sesi absensi berhasil dibuat',
            'session' => $session,
        ], 201);
    }

    /**
     * PUT /api/v1/attendance/sessions/{id}
     */
    public function update(StoreSessionRequest $request, int $id): JsonResponse
    {
        $session = AttendanceSession::findOrFail($id);
        $session->update($request->validated());

        return response()->json([
            'message' => 'Sesi absensi berhasil diperbarui',
            'session' => $session->fresh(),
        ]);
    }

    /**
     * DELETE /api/v1/attendance/sessions/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $session = AttendanceSession::findOrFail($id);
        $session->delete();

        return response()->json(['message' => 'Sesi absensi berhasil dihapus']);
    }

    /**
     * POST /api/v1/attendance/sessions/bulk
     * Buat sesi berulang (mis: Senin-Jumat selama 1 bulan).
     *
     * Request:
     * {
     *   name, branch_id, check_in_start, check_in_end, late_after,
     *   check_out_start?, check_out_end?,
     *   repeat_days: [1,2,3,4,5],   ← 1=Senin ... 7=Minggu (ISO)
     *   until_date: "2025-08-31"
     * }
     */
    public function bulk(Request $request): JsonResponse
    {
        $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'branch_id'       => ['required', 'integer', 'exists:tenant_branches,id'],
            'check_in_start'  => ['required', 'date_format:H:i'],
            'check_in_end'    => ['required', 'date_format:H:i', 'after:check_in_start'],
            'late_after'      => ['required', 'date_format:H:i', 'after_or_equal:check_in_start', 'before_or_equal:check_in_end'],
            'check_out_start' => ['nullable', 'date_format:H:i'],
            'check_out_end'   => ['nullable', 'date_format:H:i', 'after:check_out_start'],
            'repeat_days'     => ['required', 'array', 'min:1'],
            'repeat_days.*'   => ['integer', 'min:1', 'max:7'],
            'until_date'      => ['required', 'date_format:Y-m-d', 'after:today'],
        ]);

        $tenantId   = TenantHelper::currentTenantId();
        $repeatDays = $request->repeat_days;
        $untilDate  = Carbon::parse($request->until_date);
        $period     = CarbonPeriod::create(Carbon::tomorrow(), $untilDate);

        $sessionBase = [
            'tenant_id'       => $tenantId,
            'branch_id'       => $request->branch_id,
            'name'            => $request->name,
            'check_in_start'  => $request->check_in_start,
            'check_in_end'    => $request->check_in_end,
            'late_after'      => $request->late_after,
            'check_out_start' => $request->check_out_start,
            'check_out_end'   => $request->check_out_end,
            'is_active'       => true,
        ];

        $created = [];
        $skipped = 0;

        foreach ($period as $date) {
            // isoWeekday: 1=Senin, 7=Minggu
            if (!in_array($date->isoWeekday(), $repeatDays)) {
                continue;
            }

            $dateStr = $date->toDateString();

            // Skip jika sudah ada sesi yang overlap di branch + tanggal ini
            $overlapExists = AttendanceSession::where('branch_id', $request->branch_id)
                ->where('date', $dateStr)
                ->where('is_active', true)
                ->where('check_in_start', '<', $request->check_in_end)
                ->where('check_in_end', '>', $request->check_in_start)
                ->exists();

            if ($overlapExists) {
                $skipped++;
                continue;
            }

            $created[] = AttendanceSession::create(array_merge($sessionBase, ['date' => $dateStr]));
        }

        return response()->json([
            'message'  => count($created) . ' sesi berhasil dibuat, ' . $skipped . ' dilewati karena overlap.',
            'created'  => count($created),
            'skipped'  => $skipped,
            'sessions' => $created,
        ], 201);
    }
}
