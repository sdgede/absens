<x-layouts.admin title="Laporan Absensi" subtitle="Rekap kehadiran bulanan">

    <x-slot:actions>
        <a href="{{ route('admin.laporan.export', ['format' => 'pdf'] + request()->all()) }}" class="btn-secondary"
            style="text-decoration:none;">📄 PDF</a>
        <a href="{{ route('admin.laporan.export', ['format' => 'excel'] + request()->all()) }}" class="btn-secondary"
            style="text-decoration:none;">📊 Excel</a>
    </x-slot:actions>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.laporan') }}"
        style="display:flex; gap:0.5rem; margin-bottom:1rem; flex-wrap:wrap; align-items:center;">
        <select name="bulan" class="input-field" style="width:auto;">
            @foreach (range(1, 12) as $b)
                <option value="{{ $b }}" {{ request('bulan', now()->month) == $b ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}
                </option>
            @endforeach
        </select>
        <select name="tahun" class="input-field" style="width:auto;">
            @foreach (range(now()->year, now()->year - 3) as $t)
                <option value="{{ $t }}" {{ request('tahun', now()->year) == $t ? 'selected' : '' }}>{{ $t }}</option>
            @endforeach
        </select>
        <select name="cabang_id" class="input-field" style="width:auto;">
            <option value="">Semua Cabang</option>
            @foreach ($cabangList as $c)
                <option value="{{ $c->id }}" {{ request('cabang_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary" style="width:auto; padding:0.5rem 1rem;">Tampilkan</button>
    </form>

    {{-- Ringkasan --}}
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:0.75rem; margin-bottom:1rem;">
        <x-admin.stat-card label="Total Kehadiran" nilai="{{ $ringkasan['total_hadir'] }}" warna="brand"
            sub="{{ request('bulan', now()->month) }}/{{ request('tahun', now()->year) }}" />
        <x-admin.stat-card label="% Tepat Waktu" nilai="{{ $ringkasan['pct_tepat'] }}%" warna="emerald"
            sub="dari total hadir" />
        <x-admin.stat-card label="Total Terlambat" nilai="{{ $ringkasan['total_telat'] }}" warna="amber"
            sub="rata-rata {{ $ringkasan['rata_telat'] }} menit" />
        <x-admin.stat-card label="Total Alpha" nilai="{{ $ringkasan['total_alpha'] }}" warna="rose"
            sub="{{ $ringkasan['pct_alpha'] }}% dari total" />
    </div>

    {{-- Tabel per Karyawan --}}
    <div class="card" style="padding:0; overflow:hidden;">
        <table style="width:100%; border-collapse:collapse; font-size:0.75rem;">
            <thead>
                <tr>
                    <th class="th">Karyawan</th>
                    <th class="th">Dept</th>
                    <th class="th">Hadir</th>
                    <th class="th">Terlambat</th>
                    <th class="th">Alpha</th>
                    <th class="th">Izin</th>
                    <th class="th">% Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($laporanList as $row)
                    <tr class="tr">
                        <td class="td">
                            <div style="display:flex; align-items:center; gap:0.5rem;">
                                <div
                                    style="width:1.75rem; height:1.75rem; border-radius:0.4375rem; background-color:var(--color-indigo); display:flex; align-items:center; justify-content:center; font-size:0.6875rem; font-weight:700; color:white; flex-shrink:0;">
                                    {{ strtoupper(substr($row['nama'], 0, 1)) }}{{ strtoupper(substr(strstr($row['nama'], ' '), 1, 1)) }}
                                </div>
                                <span style="font-weight:600; color:var(--color-dark-50);">{{ $row['nama'] }}</span>
                            </div>
                        </td>
                        <td class="td">{{ $row['dept'] }}</td>
                        <td class="td" style="font-family:var(--font-mono);">{{ $row['hadir'] }}</td>
                        <td class="td" style="font-family:var(--font-mono); color:var(--color-amber);">{{ $row['telat'] }}
                        </td>
                        <td class="td" style="font-family:var(--font-mono); color:var(--color-rose);">{{ $row['alpha'] }}
                        </td>
                        <td class="td" style="font-family:var(--font-mono); color:var(--color-violet);">{{ $row['izin'] }}
                        </td>
                        <td class="td">
                            <div style="display:flex; align-items:center; gap:0.5rem;">
                                <div class="progress-track" style="width:5rem;">
                                    <div
                                        style="height:100%; border-radius:9999px; background-color:{{ $row['pct'] >= 90 ? 'var(--color-emerald)' : ($row['pct'] >= 75 ? 'var(--color-brand)' : ($row['pct'] >= 60 ? 'var(--color-amber)' : 'var(--color-rose)')) }}; width:{{ $row['pct'] }}%;">
                                    </div>
                                </div>
                                <span
                                    style="font-family:var(--font-mono); font-size:0.625rem; color:var(--color-dark-300);">{{ $row['pct'] }}%</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding:2rem; text-align:center; color:var(--color-dark-300);">Belum ada data
                            laporan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-layouts.admin>
