<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\TenantBranch;
use App\Models\Department;
use App\Models\User;
use App\Models\AttendanceSession;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

    // ── Helper: ambil user_id milik tenant ini ───────────────────────────
    private function userIdsTenant(): \Illuminate\Support\Collection
    {
        return User::where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->pluck('id');
    }


    public function ringkasan(): View
    {
        $tenantId  = $this->tenantId();
        $userIds   = $this->userIdsTenant();
        $hari      = today();

        $totalKaryawan = $userIds->count();

        $absensiHariIni = Attendance::whereIn('user_id', $userIds)
            ->whereDate('checked_at', $hari)
            ->where('type', 'checkin')
            ->get();

        $statistik = [
            'total'        => $totalKaryawan,
            'hadir'        => $absensiHariIni->whereIn('status', ['present', 'late'])->count(),
            'telat'        => $absensiHariIni->where('status', 'late')->count(),
            'alpha'        => $totalKaryawan - $absensiHariIni->count(),
            'izin'         => $absensiHariIni->where('status', 'leave')->count(),
            'pending_izin' => LeaveRequest::whereIn('user_id', $userIds)
                ->where('status', 'pending')->count(),
        ];

        $izinPending = LeaveRequest::with(['user', 'leaveType'])
            ->whereIn('user_id', $userIds)
            ->where('status', 'pending')
            ->latest()
            ->take(3)
            ->get()
            ->map(fn($izin) => [
                'id'      => $izin->id,
                'nama'    => $izin->user->name,
                'inisial' => strtoupper(substr($izin->user->name, 0, 1)) . strtoupper(substr(strstr($izin->user->name, ' '), 1, 1)),
                'warna'   => '#6366F1',
                'tanggal' => $izin->start_date->format('d M') . '–' . $izin->end_date->format('d M'),
                'hari'    => $izin->total_days,
                'jenis'   => $izin->leaveType->name,
            ]);

        $topTerlambat = User::where('tenant_id', $tenantId)
            ->withCount([
                'attendances as jumlah_telat' => fn($q) =>
                $q->where('status', 'late')
                    ->whereMonth('checked_at', now()->month)
                    ->whereYear('checked_at', now()->year)
            ])
            ->orderByDesc('jumlah_telat')
            ->take(5)
            ->get()
            ->map(fn($u) => [
                'nama'    => $u->name,
                'inisial' => strtoupper(substr($u->name, 0, 1)) . strtoupper(substr(strstr($u->name, ' '), 1, 1)),
                'warna'   => '#F43F5E',
                'dept'    => $u->department->name ?? '-',
                'jumlah'  => $u->jumlah_telat,
            ]);

        $grafikMingguan = collect(range(6, 0))->map(function ($offset) use ($userIds) {
            $tgl   = today()->subDays($offset);
            $total = Attendance::whereIn('user_id', $userIds)
                ->whereDate('checked_at', $tgl)
                ->where('type', 'checkin')
                ->get();

            return [
                'hari'  => $tgl->translatedFormat('D'),
                'hadir' => $total->where('status', 'present')->count(),
                'telat' => $total->where('status', 'late')->count(),
                'alpha' => $total->where('status', 'absent')->count(),
            ];
        });

        return view('admin.dashboard', compact('statistik', 'izinPending', 'topTerlambat', 'grafikMingguan'));
    }


    public function monitoring(Request $request): View
    {
        $tenantId = $this->tenantId();

        $userIds = $this->userIdsTenant();

        $query = Attendance::with(['user.branch', 'user.department'])
            ->whereIn('user_id', $userIds)
            ->whereDate('checked_at', today())
            ->where('type', 'checkin');

        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->whereHas(
                'user',
                fn($q) =>
                $q->where('name', 'like', "%$cari%")
                    ->orWhere('employee_id', 'like', "%$cari%")
            );
        }

        if ($request->filled('cabang_id')) {
            $query->whereHas('user', fn($q) => $q->where('branch_id', $request->cabang_id));
        }

        if ($request->filled('dept_id')) {
            $query->whereHas('user', fn($q) => $q->where('department_id', $request->dept_id));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $absensiHariIni = $query->latest('checked_at')->paginate(20);
        $cabangList     = TenantBranch::where('tenant_id', $tenantId)->get();
        $deptList       = Department::where('tenant_id', $tenantId)->get();

        return view('admin.monitoring', compact('absensiHariIni', 'cabangList', 'deptList'));
    }


    public function izin(Request $request): View
    {
        $tenantId = $this->tenantId();

        $userIds = $this->userIdsTenant();

        $query = LeaveRequest::with(['user', 'leaveType'])
            ->whereIn('user_id', $userIds);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $izinList = $query->latest()->paginate(20);

        $jumlah = [
            'pending'   => LeaveRequest::whereIn('user_id', $userIds)->where('status', 'pending')->count(),
            'disetujui' => LeaveRequest::whereIn('user_id', $userIds)->where('status', 'approved')->count(),
            'ditolak'   => LeaveRequest::whereIn('user_id', $userIds)->where('status', 'rejected')->count(),
            'total'     => LeaveRequest::whereIn('user_id', $userIds)->whereMonth('created_at', now()->month)->count(),
        ];

        return view('admin.izin', compact('izinList', 'jumlah'));
    }

    public function setujuiIzin(LeaveRequest $leaveRequest): \Illuminate\Http\RedirectResponse
    {
        $leaveRequest->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('status', 'Izin berhasil disetujui.');
    }

    public function tolakIzin(Request $request, LeaveRequest $leaveRequest): \Illuminate\Http\RedirectResponse
    {
        $leaveRequest->update([
            'status'           => 'rejected',
            'approved_by'      => auth()->id(),
            'approved_at'      => now(),
            'rejection_reason' => $request->alasan,
        ]);

        return back()->with('status', 'Izin berhasil ditolak.');
    }

    // ── Sesi Absensi ─────────────────────────────────────────────────────

    public function sesi(Request $request): View
    {
        $tenantId = $this->tenantId();

        $query = AttendanceSession::with('branch')
            ->where('tenant_id', $tenantId);

        if ($request->filled('cabang_id')) {
            $query->where('branch_id', $request->cabang_id);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('date', $request->tanggal);
        } else {
            $query->whereDate('date', today());
        }

        $sesiList   = $query->latest('date')->paginate(20);
        $cabangList = TenantBranch::where('tenant_id', $tenantId)->get();

        return view('admin.sesi', compact('sesiList', 'cabangList'));
    }

    public function hapusSesi(AttendanceSession $attendanceSession): \Illuminate\Http\RedirectResponse
    {
        $attendanceSession->delete();
        return back()->with('status', 'Sesi berhasil dihapus.');
    }


    public function karyawan(Request $request): View
    {
        $tenantId = $this->tenantId();

        $query = User::with(['branch', 'department', 'roles', 'faceEmbedding'])
            ->where('tenant_id', $tenantId);

        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->where(
                fn($q) =>
                $q->where('name', 'like', "%$cari%")
                    ->orWhere('email', 'like', "%$cari%")
                    ->orWhere('employee_id', 'like', "%$cari%")
            );
        }

        if ($request->filled('cabang_id')) {
            $query->where('branch_id', $request->cabang_id);
        }

        if ($request->filled('dept_id')) {
            $query->where('department_id', $request->dept_id);
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        // dd($query->get()->toArray());


        $karyawanList  = $query->latest()->paginate(20);
        $totalKaryawan = User::where('tenant_id', $tenantId)->count();
        $cabangList    = TenantBranch::where('tenant_id', $tenantId)->get();
        $deptList      = Department::where('tenant_id', $tenantId)->get();

        return view('admin.karyawan', compact('karyawanList', 'totalKaryawan', 'cabangList', 'deptList'));
    }

    public function hapusKaryawan(User $user): \Illuminate\Http\RedirectResponse
    {
        $user->update(['is_active' => false]);
        return back()->with('status', 'Karyawan berhasil dinonaktifkan.');
    }

    // ── Cabang ───────────────────────────────────────────────────────────

    public function cabang(): View
    {
        $tenantId = $this->tenantId();

        $cabangList  = TenantBranch::withCount('users')
            ->where('tenant_id', $tenantId)
            ->get();
        $totalCabang = $cabangList->count();

        return view('admin.cabang', compact('cabangList', 'totalCabang'));
    }

    // ── Laporan ──────────────────────────────────────────────────────────

    public function laporan(Request $request): View
    {
        $tenantId = $this->tenantId();
        $bulan    = (int) $request->get('bulan', now()->month);
        $tahun    = (int) $request->get('tahun',  now()->year);

        $karyawanList = User::with(['department'])
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->when($request->filled('cabang_id'), fn($q) => $q->where('branch_id', $request->cabang_id))
            ->get();

        $laporanList = $karyawanList->map(function ($user) use ($bulan, $tahun) {
            $absensi = Attendance::where('user_id', $user->id)
                ->whereMonth('checked_at', $bulan)
                ->whereYear('checked_at', $tahun)
                ->where('type', 'checkin')
                ->get();

            $hadir = $absensi->where('status', 'present')->count();
            $telat = $absensi->where('status', 'late')->count();
            $alpha = $absensi->where('status', 'absent')->count();
            $izin  = $absensi->where('status', 'leave')->count();
            $total = $hadir + $telat + $izin;
            $hariKerja = Carbon::create($tahun, $bulan)->daysInMonth;

            return [
                'nama'  => $user->name,
                'dept'  => $user->department->name ?? '-',
                'hadir' => $hadir,
                'telat' => $telat,
                'alpha' => $alpha,
                'izin'  => $izin,
                'pct'   => $hariKerja > 0 ? round(($total / $hariKerja) * 100) : 0,
            ];
        })->sortByDesc('pct')->values();

        $allAbsensi = Attendance::whereIn('user_id', $karyawanList->pluck('id'))
            ->whereMonth('checked_at', $bulan)
            ->whereYear('checked_at', $tahun)
            ->where('type', 'checkin')
            ->get();

        $totalHadir = $allAbsensi->whereIn('status', ['present', 'late'])->count();
        $totalTelat = $allAbsensi->where('status', 'late')->count();
        $totalAlpha = $allAbsensi->where('status', 'absent')->count();

        $ringkasan = [
            'total_hadir' => $totalHadir,
            'pct_tepat'   => $totalHadir > 0 ? round((($totalHadir - $totalTelat) / $totalHadir) * 100) : 0,
            'total_telat' => $totalTelat,
            'rata_telat'  => 8,
            'total_alpha' => $totalAlpha,
            'pct_alpha'   => ($totalHadir + $totalAlpha) > 0 ? round(($totalAlpha / ($totalHadir + $totalAlpha)) * 100) : 0,
        ];

        $cabangList = TenantBranch::where('tenant_id', $tenantId)->get();

        return view('admin.laporan', compact('laporanList', 'ringkasan', 'cabangList'));
    }


    public function notifikasi(): View
    {
        $user = auth()->user();

        $notifikasiList  = $user->notifications()->latest()->paginate(10);
        $jumlahBelumDibaca = $user->unreadNotifications()->count();
        $cabangList      = TenantBranch::where('tenant_id', $user->tenant_id)->withCount('users')->get();
        $totalKaryawan   = User::where('tenant_id', $user->tenant_id)->where('is_active', true)->count();

        return view('admin.notifikasi', compact('notifikasiList', 'jumlahBelumDibaca', 'cabangList', 'totalKaryawan'));
    }

    public function bacaSemua(): \Illuminate\Http\RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('status', 'Semua notifikasi ditandai telah dibaca.');
    }

    public function kirimPengumuman(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'judul'  => ['required', 'string', 'max:255'],
            'pesan'  => ['required', 'string'],
            'target' => ['required', 'string'],
        ]);

        $tenantId = $this->tenantId();

        $query = User::where('tenant_id', $tenantId)->where('is_active', true);

        if ($request->target !== 'semua' && str_starts_with($request->target, 'cabang_')) {
            $cabangId = (int) str_replace('cabang_', '', $request->target);
            $query->where('branch_id', $cabangId);
        }

        $users = $query->get();

        // Dispatch ke queue — sesuaikan dengan NotificationService yang sudah ada
        foreach ($users as $user) {
            $user->notify(new \App\Notifications\AnnouncementNotification(
                $request->judul,
                $request->pesan,
            ));
        }

        return back()->with('sukses_kirim', "Pengumuman berhasil dikirim ke {$users->count()} karyawan.");
    }
}
