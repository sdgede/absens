<x-layouts.admin title="Izin & Cuti" subtitle="Kelola pengajuan karyawan">

    <x-slot:actions>
        <a href="{{ route('admin.izin.create') }}" class="btn-secondary" style="text-decoration:none;">+ Tambah
            Manual</a>
    </x-slot:actions>

    {{-- Ringkasan --}}
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:0.75rem; margin-bottom:1rem;">
        <x-admin.stat-card label="Menunggu" nilai="{{ $jumlah['pending'] }}" warna="amber" />
        <x-admin.stat-card label="Disetujui" nilai="{{ $jumlah['disetujui'] }}" warna="emerald" />
        <x-admin.stat-card label="Ditolak" nilai="{{ $jumlah['ditolak'] }}" warna="rose" />
        <x-admin.stat-card label="Bulan Ini" nilai="{{ $jumlah['total'] }}" warna="brand" />
    </div>

    {{-- Tab filter --}}
    <div
        style="display:flex; gap:0.125rem; background-color:var(--color-dark-700); border:1px solid var(--color-dark-500); border-radius:0.5rem; padding:0.1875rem; margin-bottom:1rem; width:fit-content;">
        @foreach (['' => 'Semua', 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $nilai => $teks)
            <a href="{{ route('admin.izin', ['status' => $nilai]) }}" style="
                        padding:0.3125rem 1rem;
                        font-size:0.6875rem;
                        font-weight:600;
                        border-radius:0.375rem;
                        text-decoration:none;
                        transition:all 0.15s;
                        {{ request('status', '') === $nilai ? 'background-color:var(--color-dark-600); color:var(--color-dark-50);' : 'color:var(--color-dark-300);' }}
                    ">{{ $teks }}</a>
        @endforeach
    </div>

    {{-- Tabel --}}
    <div class="card" style="padding:0; overflow:hidden;">
        <table style="width:100%; border-collapse:collapse; font-size:0.75rem;">
            <thead>
                <tr>
                    <th class="th">Karyawan</th>
                    <th class="th">Jenis</th>
                    <th class="th">Tanggal</th>
                    <th class="th">Hari</th>
                    <th class="th">Alasan</th>
                    <th class="th">Status</th>
                    <th class="th">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($izinList as $izin)
                    <tr class="tr">
                        <td class="td">
                            <div style="display:flex; align-items:center; gap:0.5rem;">
                                <div
                                    style="width:1.75rem; height:1.75rem; border-radius:0.4375rem; background-color:var(--color-indigo); display:flex; align-items:center; justify-content:center; font-size:0.6875rem; font-weight:700; color:white; flex-shrink:0;">
                                    {{ strtoupper(substr($izin->user->name, 0, 1)) }}{{ strtoupper(substr(strstr($izin->user->name, ' '), 1, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:600; color:var(--color-dark-50);">{{ $izin->user->name }}</div>
                                    <div
                                        style="font-size:0.625rem; font-family:var(--font-mono); color:var(--color-dark-300);">
                                        {{ $izin->user->employee_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="td">
                            <span
                                style="font-size:0.625rem; font-weight:700; padding:0.1875rem 0.5rem; border-radius:0.375rem; background-color:color-mix(in srgb, var(--color-indigo) 15%, transparent); color:#818cf8;">
                                {{ $izin->leaveType->name }}
                            </span>
                        </td>
                        <td class="td" style="font-family:var(--font-mono);">
                            {{ $izin->start_date->format('d M') }}–{{ $izin->end_date->format('d M Y') }}
                        </td>
                        <td class="td" style="font-family:var(--font-mono);">{{ $izin->total_days }} hr</td>
                        <td class="td"
                            style="max-width:12rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            {{ $izin->reason }}</td>
                        <td class="td">
                            <x-admin.badge :tipe="$izin->status === 'approved' ? 'disetujui' : ($izin->status === 'rejected' ? 'ditolak' : 'pending')" />
                        </td>
                        <td class="td">
                            @if ($izin->status === 'pending')
                                <div style="display:flex; gap:0.3125rem;">
                                    <form method="POST" action="{{ route('admin.izin.setujui', $izin->id) }}">
                                        @csrf @method('PUT')
                                        <button type="submit"
                                            style="font-size:0.625rem; font-weight:700; padding:0.25rem 0.5625rem; border-radius:0.375rem; background-color:color-mix(in srgb, var(--color-emerald) 15%, transparent); color:var(--color-emerald); border:none; cursor:pointer;">Setujui</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.izin.tolak', $izin->id) }}">
                                        @csrf @method('PUT')
                                        <button type="submit"
                                            style="font-size:0.625rem; font-weight:700; padding:0.25rem 0.5625rem; border-radius:0.375rem; background-color:color-mix(in srgb, var(--color-rose) 10%, transparent); color:var(--color-rose); border:none; cursor:pointer;">Tolak</button>
                                    </form>
                                </div>
                            @else
                                <span style="font-size:0.625rem; color:var(--color-dark-300);">
                                    {{ $izin->approved_at?->format('d M Y') ?? '-' }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7"
                            style="padding:2rem; text-align:center; color:var(--color-dark-300); font-size:0.8125rem;">
                            Tidak ada data pengajuan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($izinList->hasPages())
            <div style="padding:0.75rem 1rem; border-top:1px solid var(--color-dark-500);">
                {{ $izinList->withQueryString()->links() }}
            </div>
        @endif
    </div>

</x-layouts.admin>
