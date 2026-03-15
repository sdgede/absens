<x-layouts.admin title="Ringkasan" subtitle="Rekap absensi hari ini">

    {{-- Slot aksi topbar --}}
    <x-slot:actions>
        <button onclick="window.print()" class="btn-secondary">📤 Ekspor PDF</button>
    </x-slot:actions>

    {{-- Kartu Statistik --}}
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:0.75rem; margin-bottom:1.25rem;">
        <x-admin.stat-card
            label="Hadir Hari Ini"
            nilai="{{ $statistik['hadir'] }}"
            sub="dari {{ $statistik['total'] }} karyawan"
            badge="↑ +3 vs kemarin"
            badge-tipe="naik"
            ikon="✅"
            warna="brand"
            href="{{ route('admin.monitoring') }}"
        />
        <x-admin.stat-card
            label="Terlambat"
            nilai="{{ $statistik['telat'] }}"
            sub="toleransi 5 menit"
            badge="→ sama minggu lalu"
            badge-tipe="sama"
            ikon="⏰"
            warna="amber"
            href="{{ route('admin.monitoring') }}"
        />
        <x-admin.stat-card
            label="Alpha / Absen"
            nilai="{{ $statistik['alpha'] }}"
            sub="belum check-in"
            badge="↑ +1 vs kemarin"
            badge-tipe="turun"
            ikon="❌"
            warna="rose"
            href="{{ route('admin.monitoring') }}"
        />
        <x-admin.stat-card
            label="Izin / Cuti"
            nilai="{{ $statistik['izin'] }}"
            sub="{{ $statistik['pending_izin'] }} menunggu persetujuan"
            badge="✓ disetujui {{ $statistik['izin'] - $statistik['pending_izin'] }}"
            badge-tipe="naik"
            ikon="📋"
            warna="emerald"
            href="{{ route('admin.izin') }}"
        />
    </div>

    {{-- Baris Tengah: Chart + Donut --}}
    <div style="display:grid; grid-template-columns:2fr 1fr; gap:0.875rem; margin-bottom:0.875rem;">

        {{-- Grafik Kehadiran --}}
        <div class="card">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                <div>
                    <div style="font-size:0.8125rem; font-weight:700; color:var(--color-dark-50);">Kehadiran 7 Hari Terakhir</div>
                    <div style="font-size:0.6875rem; color:var(--color-dark-300); margin-top:0.125rem;">Hadir · Terlambat · Alpha</div>
                </div>
            </div>
            <div id="grafikKehadiran" style="display:flex; align-items:flex-end; gap:0.375rem; height:7rem; padding-top:0.625rem;"></div>
            <div style="display:flex; gap:1rem; margin-top:0.75rem;">
                <div style="display:flex; align-items:center; gap:0.375rem; font-size:0.625rem; color:var(--color-dark-300);">
                    <div style="width:0.625rem; height:0.625rem; border-radius:0.125rem; background-color:var(--color-brand);"></div>Hadir
                </div>
                <div style="display:flex; align-items:center; gap:0.375rem; font-size:0.625rem; color:var(--color-dark-300);">
                    <div style="width:0.625rem; height:0.625rem; border-radius:0.125rem; background-color:var(--color-amber);"></div>Terlambat
                </div>
                <div style="display:flex; align-items:center; gap:0.375rem; font-size:0.625rem; color:var(--color-dark-300);">
                    <div style="width:0.625rem; height:0.625rem; border-radius:0.125rem; background-color:var(--color-rose);"></div>Alpha
                </div>
            </div>
        </div>

        {{-- Distribusi Status --}}
        <div class="card">
            <div style="font-size:0.8125rem; font-weight:700; color:var(--color-dark-50); margin-bottom:0.25rem;">Distribusi Status</div>
            <div style="font-size:0.6875rem; color:var(--color-dark-300); margin-bottom:1rem;">{{ now()->translatedFormat('F Y') }}</div>
            <div style="display:flex; align-items:center; gap:1rem;">
                <svg style="transform:rotate(-90deg); flex-shrink:0;" width="88" height="88" viewBox="0 0 88 88">
                    <circle cx="44" cy="44" r="34" fill="none" stroke="#2A3A52" stroke-width="10"/>
                    <circle cx="44" cy="44" r="34" fill="none" stroke="#4F8EF7" stroke-width="10" stroke-dasharray="128 86" stroke-linecap="round"/>
                    <circle cx="44" cy="44" r="34" fill="none" stroke="#F59E0B" stroke-width="10" stroke-dasharray="43 171" stroke-dashoffset="-128" stroke-linecap="round"/>
                    <circle cx="44" cy="44" r="34" fill="none" stroke="#F43F5E" stroke-width="10" stroke-dasharray="21 193" stroke-dashoffset="-171" stroke-linecap="round"/>
                    <circle cx="44" cy="44" r="34" fill="none" stroke="#A78BFA" stroke-width="10" stroke-dasharray="21 193" stroke-dashoffset="-192" stroke-linecap="round"/>
                </svg>
                <div style="flex:1; display:flex; flex-direction:column; gap:0.5rem;">
                    @foreach ([
                        ['warna'=>'#4F8EF7','label'=>'Hadir',     'pct'=>'60%'],
                        ['warna'=>'#F59E0B','label'=>'Terlambat', 'pct'=>'20%'],
                        ['warna'=>'#F43F5E','label'=>'Alpha',     'pct'=>'10%'],
                        ['warna'=>'#A78BFA','label'=>'Izin',      'pct'=>'10%'],
                    ] as $item)
                        <div style="display:flex; align-items:center; gap:0.5rem;">
                            <div style="width:0.5rem; height:0.5rem; border-radius:0.125rem; background-color:{{ $item['warna'] }}; flex-shrink:0;"></div>
                            <span style="font-size:0.6875rem; color:var(--color-dark-200); flex:1;">{{ $item['label'] }}</span>
                            <span style="font-family:var(--font-mono); font-size:0.75rem; font-weight:700; color:var(--color-dark-50);">{{ $item['pct'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Baris Bawah: Izin Pending + Top Terlambat --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.875rem;">

        {{-- Izin Menunggu Persetujuan --}}
        <div class="card">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                <div>
                    <div style="font-size:0.8125rem; font-weight:700; color:var(--color-dark-50);">Izin Menunggu Persetujuan</div>
                    <div style="font-size:0.6875rem; color:var(--color-dark-300); margin-top:0.125rem;">{{ $statistik['pending_izin'] }} pengajuan</div>
                </div>
                <a href="{{ route('admin.izin') }}" class="btn-secondary" style="text-decoration:none;">Lihat semua →</a>
            </div>
            @forelse ($izinPending as $izin)
                <div style="display:flex; align-items:center; gap:0.625rem; padding:0.625rem 0; border-bottom:1px solid color-mix(in srgb, var(--color-dark-500) 50%, transparent);">
                    <div style="width:1.75rem; height:1.75rem; border-radius:0.4375rem; background-color:{{ $izin['warna'] }}; display:flex; align-items:center; justify-content:center; font-size:0.6875rem; font-weight:700; color:white; flex-shrink:0;">
                        {{ $izin['inisial'] }}
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:0.75rem; font-weight:600; color:var(--color-dark-50);">{{ $izin['nama'] }}</div>
                        <div style="font-size:0.625rem; color:var(--color-dark-300); font-family:var(--font-mono);">{{ $izin['tanggal'] }} · {{ $izin['hari'] }} hari</div>
                    </div>
                    <span style="font-size:0.625rem; font-weight:700; padding:0.1875rem 0.5rem; border-radius:0.375rem; background-color:color-mix(in srgb, var(--color-indigo) 15%, transparent); color:#818cf8;">{{ $izin['jenis'] }}</span>
                    <div style="display:flex; gap:0.3125rem;">
                        <form method="POST" action="{{ route('admin.izin.setujui', $izin['id']) }}">
                            @csrf @method('PUT')
                            <button type="submit" style="font-size:0.625rem; font-weight:700; padding:0.25rem 0.5rem; border-radius:0.375rem; background-color:color-mix(in srgb, var(--color-emerald) 15%, transparent); color:var(--color-emerald); border:none; cursor:pointer;">Setujui</button>
                        </form>
                        <form method="POST" action="{{ route('admin.izin.tolak', $izin['id']) }}">
                            @csrf @method('PUT')
                            <button type="submit" style="font-size:0.625rem; font-weight:700; padding:0.25rem 0.5rem; border-radius:0.375rem; background-color:color-mix(in srgb, var(--color-rose) 10%, transparent); color:var(--color-rose); border:none; cursor:pointer;">Tolak</button>
                        </form>
                    </div>
                </div>
            @empty
                <p style="font-size:0.8125rem; color:var(--color-dark-300); text-align:center; padding:1rem 0;">Tidak ada izin pending.</p>
            @endforelse
        </div>

        {{-- Top Karyawan Terlambat --}}
        <div class="card">
            <div style="font-size:0.8125rem; font-weight:700; color:var(--color-dark-50); margin-bottom:0.25rem;">Top Karyawan Terlambat</div>
            <div style="font-size:0.6875rem; color:var(--color-dark-300); margin-bottom:1rem;">Bulan ini</div>
            @foreach ($topTerlambat as $i => $kar)
                <div style="display:flex; align-items:center; gap:0.5625rem; padding:0.5rem 0; border-bottom:1px solid color-mix(in srgb, var(--color-dark-500) 40%, transparent);">
                    <span style="font-family:var(--font-mono); font-size:0.75rem; font-weight:800; color:var(--color-dark-300); width:1.125rem;">#{{ $i + 1 }}</span>
                    <div style="width:1.625rem; height:1.625rem; border-radius:0.4375rem; background-color:{{ $kar['warna'] }}; display:flex; align-items:center; justify-content:center; font-size:0.625rem; font-weight:700; color:white; flex-shrink:0;">{{ $kar['inisial'] }}</div>
                    <div style="flex:1;">
                        <div style="font-size:0.75rem; font-weight:600; color:var(--color-dark-50);">{{ $kar['nama'] }}</div>
                        <div style="font-size:0.625rem; color:var(--color-dark-300);">{{ $kar['dept'] }}</div>
                    </div>
                    <span style="font-family:var(--font-mono); font-size:0.75rem; font-weight:700; color:var(--color-amber);">{{ $kar['jumlah'] }}×</span>
                </div>
            @endforeach
        </div>
    </div>

</x-layouts.admin>

<script>
    // Grafik batang sederhana
    const data = @json($grafikMingguan);
    const el   = document.getElementById('grafikKehadiran');
    const maks = 47;

    data.forEach(d => {
        const col = document.createElement('div');
        col.style.cssText = 'flex:1; display:flex; flex-direction:column; align-items:center; gap:0.3125rem;';

        const wrap = document.createElement('div');
        wrap.style.cssText = 'width:100%; display:flex; gap:2px; align-items:flex-end; height:5.5rem;';

        [
            [d.hadir, '#4F8EF7', 0.85],
            [d.telat,  '#F59E0B', 1],
            [d.alpha,  '#F43F5E', 1],
        ].forEach(([val, warna, op]) => {
            const b = document.createElement('div');
            b.style.cssText = `flex:1; border-radius:3px 3px 0 0; background-color:${warna}; opacity:${op}; height:${(val/maks)*88}px; transition:height 0.5s ease;`;
            wrap.appendChild(b);
        });

        const lbl = document.createElement('div');
        lbl.style.cssText = 'font-size:0.5625rem; color:var(--color-dark-300); font-family:var(--font-mono);';
        lbl.textContent = d.hari;

        col.append(wrap, lbl);
        el.appendChild(col);
    });
</script>
