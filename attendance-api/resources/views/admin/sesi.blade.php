{{-- ======================================================== --}}
{{-- CATATAN: Pisahkan file-file ini ke path masing-masing --}}
{{-- ======================================================== --}}


{{-- ========== resources/views/admin/sesi.blade.php ========== --}}
<x-layouts.admin title="Sesi Absensi" subtitle="Kelola jam masuk & keluar per cabang">

    <x-slot:actions>
        <a href="{{ route('admin.sesi.create') }}" class="btn-secondary" style="text-decoration:none;">+ Sesi Baru</a>
    </x-slot:actions>

    {{-- Form Sesi Berulang --}}
    <div class="card" style="margin-bottom:1rem;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
            <div>
                <div style="font-size:0.8125rem; font-weight:700; color:var(--color-dark-50);">Buat Sesi Berulang</div>
                <div style="font-size:0.6875rem; color:var(--color-dark-300); margin-top:0.125rem;">Otomatis buat sesi
                    Senin–Jumat</div>
            </div>
            <a href="{{ route('admin.sesi.bulk') }}" class="btn-secondary" style="text-decoration:none;">⚡ Buat
                Massal</a>
        </div>
        <form method="GET" action="{{ route('admin.sesi') }}"
            style="display:grid; grid-template-columns:repeat(5,1fr); gap:0.75rem;">
            <div>
                <div style="font-size:0.625rem; color:var(--color-dark-300); margin-bottom:0.375rem;">Cabang</div>
                <select name="cabang_id" class="input-field">
                    <option value="">Semua Cabang</option>
                    @foreach ($cabangList as $c)
                        <option value="{{ $c->id }}" {{ request('cabang_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <div style="font-size:0.625rem; color:var(--color-dark-300); margin-bottom:0.375rem;">Tanggal</div>
                <input type="date" name="tanggal" value="{{ request('tanggal', today()->toDateString()) }}"
                    class="input-field">
            </div>
            <div style="display:flex; align-items:flex-end;">
                <button type="submit" class="btn-primary" style="padding:0.5rem 1rem; width:auto;">Terapkan</button>
            </div>
        </form>
    </div>

    {{-- Tabel Sesi --}}
    <div class="card" style="padding:0; overflow:hidden;">
        <table style="width:100%; border-collapse:collapse; font-size:0.75rem;">
            <thead>
                <tr>
                    <th class="th">Nama Sesi</th>
                    <th class="th">Cabang</th>
                    <th class="th">Tanggal</th>
                    <th class="th">Buka CI</th>
                    <th class="th">Batas Tepat</th>
                    <th class="th">Tutup CI</th>
                    <th class="th">Check-out</th>
                    <th class="th">Status</th>
                    <th class="th">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sesiList as $sesi)
                    <tr class="tr">
                        <td class="td" style="font-weight:600; color:var(--color-dark-50);">{{ $sesi->name }}</td>
                        <td class="td">{{ $sesi->branch->name }}</td>
                        <td class="td" style="font-family:var(--font-mono);">{{ $sesi->date->format('d M Y') }}</td>
                        <td class="td" style="font-family:var(--font-mono);">{{ $sesi->check_in_start }}</td>
                        <td class="td" style="font-family:var(--font-mono);">{{ $sesi->late_after }}</td>
                        <td class="td" style="font-family:var(--font-mono);">{{ $sesi->check_in_end }}</td>
                        <td class="td" style="font-family:var(--font-mono);">{{ $sesi->check_out_end ?? '—' }}</td>
                        <td class="td">
                            <x-admin.badge :tipe="$sesi->is_active ? 'aktif' : 'selesai'" />
                        </td>
                        <td class="td">
                            <div style="display:flex; gap:0.375rem;">
                                <a href="{{ route('admin.sesi.edit', $sesi->id) }}" class="btn-secondary"
                                    style="font-size:0.625rem; padding:0.1875rem 0.5rem; text-decoration:none;">Edit</a>
                                <form method="POST" action="{{ route('admin.sesi.destroy', $sesi->id) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        style="font-size:0.625rem; padding:0.1875rem 0.5rem; border-radius:0.375rem; border:1px solid color-mix(in srgb, var(--color-rose) 30%, transparent); color:var(--color-rose); background:none; cursor:pointer;"
                                        onclick="return confirm('Hapus sesi ini?')">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="padding:2rem; text-align:center; color:var(--color-dark-300);">Belum ada sesi
                            absensi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($sesiList->hasPages())
            <div style="padding:0.75rem 1rem; border-top:1px solid var(--color-dark-500);">
                {{ $sesiList->withQueryString()->links() }}
            </div>
        @endif
    </div>

</x-layouts.admin>
