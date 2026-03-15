<x-layouts.admin title="Monitoring Absensi" subtitle="Data kehadiran real-time hari ini">

    <x-slot:actions>
        <a href="{{ route('admin.monitoring.export') }}" class="btn-secondary" style="text-decoration:none;">⬇ Ekspor
            Excel</a>
    </x-slot:actions>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.monitoring') }}"
        style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.75rem; flex-wrap:wrap;">
        <input type="text" name="cari" value="{{ request('cari') }}" placeholder="🔍 Cari nama / NIP..."
            class="input-field" style="width:13rem;">
        <select name="cabang_id" class="input-field" style="width:auto;">
            <option value="">Semua Cabang</option>
            @foreach ($cabangList as $cabang)
                <option value="{{ $cabang->id }}" {{ request('cabang_id') == $cabang->id ? 'selected' : '' }}>
                    {{ $cabang->name }}</option>
            @endforeach
        </select>
        <select name="dept_id" class="input-field" style="width:auto;">
            <option value="">Semua Departemen</option>
            @foreach ($deptList as $dept)
                <option value="{{ $dept->id }}" {{ request('dept_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}
                </option>
            @endforeach
        </select>
        <select name="status" class="input-field" style="width:auto;">
            <option value="">Semua Status</option>
            <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Hadir</option>
            <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>Terlambat</option>
            <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>Alpha</option>
            <option value="leave" {{ request('status') === 'leave' ? 'selected' : '' }}>Izin</option>
        </select>
        <button type="submit" class="btn-primary" style="width:auto; padding:0.5rem 1rem;">Terapkan</button>
        @if (request()->hasAny(['cari', 'cabang_id', 'dept_id', 'status']))
            <a href="{{ route('admin.monitoring') }}" class="btn-secondary" style="text-decoration:none;">Reset</a>
        @endif
    </form>

    {{-- Tabel --}}
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.75rem;">
                <thead>
                    <tr>
                        <th class="th">Karyawan</th>
                        <th class="th">Cabang / Dept</th>
                        <th class="th">Check-in</th>
                        <th class="th">Check-out</th>
                        <th class="th">Akurasi Wajah</th>
                        <th class="th">GPS</th>
                        <th class="th">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($absensiHariIni as $absen)
                        <tr class="tr">
                            <td class="td">
                                <div style="display:flex; align-items:center; gap:0.5rem;">
                                    <div
                                        style="width:1.75rem; height:1.75rem; border-radius:0.4375rem; background-color:var(--color-indigo); display:flex; align-items:center; justify-content:center; font-size:0.6875rem; font-weight:700; color:white; flex-shrink:0;">
                                        {{ strtoupper(substr($absen->user->name, 0, 1)) }}{{ strtoupper(substr(strstr($absen->user->name, ' '), 1, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight:600; color:var(--color-dark-50);">{{ $absen->user->name }}
                                        </div>
                                        <div
                                            style="font-size:0.625rem; font-family:var(--font-mono); color:var(--color-dark-300);">
                                            {{ $absen->user->employee_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="td">
                                {{ $absen->user->branch->name ?? '-' }}<br>
                                <span
                                    style="font-size:0.625rem; color:var(--color-dark-300);">{{ $absen->user->department->name ?? '-' }}</span>
                            </td>
                            <td class="td" style="font-family:var(--font-mono);">
                                {{ $absen->check_in ? $absen->check_in->format('H:i') : '—' }}
                            </td>
                            <td class="td" style="font-family:var(--font-mono);">
                                {{ $absen->check_out ? $absen->check_out->format('H:i') : '—' }}
                            </td>
                            <td class="td">
                                @if ($absen->face_confidence)
                                    <div style="display:flex; align-items:center; gap:0.375rem;">
                                        <div class="progress-track">
                                            <div
                                                style="height:100%; border-radius:9999px; background-color:{{ $absen->face_confidence > 0.9 ? 'var(--color-emerald)' : ($absen->face_confidence > 0.75 ? 'var(--color-brand)' : 'var(--color-amber)') }}; width:{{ $absen->face_confidence * 100 }}%;">
                                            </div>
                                        </div>
                                        <span
                                            style="font-family:var(--font-mono); font-size:0.625rem; color:var(--color-dark-300);">{{ number_format($absen->face_confidence * 100, 0) }}%</span>
                                    </div>
                                @else
                                    <span style="color:var(--color-dark-300);">—</span>
                                @endif
                            </td>
                            <td class="td" style="font-size:0.6875rem; color:var(--color-emerald);">
                                @if ($absen->lat && $absen->lng)
                                    ✓ {{ number_format($absen->distance_meter ?? 0) }}m
                                @else
                                    —
                                @endif
                            </td>
                            <td class="td">
                                <x-admin.badge :tipe="$absen->status" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"
                                style="padding:2rem; text-align:center; color:var(--color-dark-300); font-size:0.8125rem;">
                                Belum ada data absensi hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginasi --}}
        @if ($absensiHariIni->hasPages())
            <div style="padding:0.75rem 1rem; border-top:1px solid var(--color-dark-500);">
                {{ $absensiHariIni->withQueryString()->links() }}
            </div>
        @endif
    </div>

</x-layouts.admin>
