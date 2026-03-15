<x-layouts.admin title="Cabang" subtitle="{{ $totalCabang }} cabang aktif">

    <x-slot:actions>
        <a href="{{ route('admin.cabang.create') }}" class="btn-secondary" style="text-decoration:none;">+ Tambah
            Cabang</a>
    </x-slot:actions>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">

        {{-- Daftar Cabang --}}
        <div style="display:flex; flex-direction:column; gap:0.75rem;">
            @forelse ($cabangList as $cabang)
                <div class="card">
                    <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem;">
                        <div
                            style="width:2.5rem; height:2.5rem; background-color:var(--color-dark-700); border:1px solid var(--color-dark-500); border-radius:0.625rem; display:flex; align-items:center; justify-content:center; font-size:1.25rem;">
                            🏢</div>
                        <div style="flex:1;">
                            <div style="font-size:0.875rem; font-weight:700; color:var(--color-dark-50);">
                                {{ $cabang->name }}</div>
                            <div style="font-size:0.6875rem; color:var(--color-dark-300); margin-top:0.125rem;">
                                {{ $cabang->address ?? '-' }}</div>
                        </div>
                        <x-admin.badge :tipe="$cabang->is_active ? 'aktif' : 'nonaktif'" />
                    </div>

                    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:0.5rem; margin-bottom:0.75rem;">
                        <div
                            style="background-color:var(--color-dark-700); border:1px solid var(--color-dark-500); border-radius:0.5rem; padding:0.625rem; text-align:center;">
                            <div
                                style="font-size:1.125rem; font-weight:800; font-family:var(--font-mono); color:var(--color-brand);">
                                {{ $cabang->users_count }}</div>
                            <div style="font-size:0.625rem; color:var(--color-dark-300);">Karyawan</div>
                        </div>
                        <div
                            style="background-color:var(--color-dark-700); border:1px solid var(--color-dark-500); border-radius:0.5rem; padding:0.625rem; text-align:center;">
                            <div
                                style="font-size:1.125rem; font-weight:800; font-family:var(--font-mono); color:var(--color-emerald);">
                                {{ $cabang->radius_meter }}m</div>
                            <div style="font-size:0.625rem; color:var(--color-dark-300);">Radius GPS</div>
                        </div>
                        <div
                            style="background-color:var(--color-dark-700); border:1px solid var(--color-dark-500); border-radius:0.5rem; padding:0.625rem; text-align:center;">
                            <div
                                style="font-size:1.125rem; font-weight:800; font-family:var(--font-mono); color:var(--color-violet);">
                                {{ substr($cabang->timezone, -3) }}</div>
                            <div style="font-size:0.625rem; color:var(--color-dark-300);">Zona Waktu</div>
                        </div>
                    </div>

                    <div style="display:flex; gap:0.5rem;">
                        <a href="{{ route('admin.cabang.edit', $cabang->id) }}" class="btn-secondary"
                            style="flex:1; text-align:center; text-decoration:none; padding:0.375rem;">📍 Edit Lokasi</a>
                        <a href="{{ route('admin.karyawan', ['cabang_id' => $cabang->id]) }}" class="btn-secondary"
                            style="flex:1; text-align:center; text-decoration:none; padding:0.375rem;">👥
                            {{ $cabang->users_count }} Karyawan</a>
                    </div>
                </div>
            @empty
                <p style="color:var(--color-dark-300); font-size:0.8125rem;">Belum ada cabang terdaftar.</p>
            @endforelse
        </div>

        {{-- Placeholder Peta --}}
        <div class="card" style="display:flex; flex-direction:column;">
            <div style="font-size:0.8125rem; font-weight:700; color:var(--color-dark-50); margin-bottom:0.25rem;">Peta
                Lokasi Cabang</div>
            <div style="font-size:0.6875rem; color:var(--color-dark-300); margin-bottom:1rem;">Koordinat GPS setiap
                cabang</div>
            <div
                style="flex:1; background-color:var(--color-dark-700); border:1px solid var(--color-dark-500); border-radius:0.625rem; display:flex; align-items:center; justify-content:center; min-height:20rem; position:relative; overflow:hidden;">
                <div
                    style="position:absolute; inset:0; background-image:repeating-linear-gradient(0deg,transparent,transparent 28px,color-mix(in srgb,var(--color-brand) 4%,transparent) 28px,color-mix(in srgb,var(--color-brand) 4%,transparent) 29px),repeating-linear-gradient(90deg,transparent,transparent 28px,color-mix(in srgb,var(--color-brand) 4%,transparent) 28px,color-mix(in srgb,var(--color-brand) 4%,transparent) 29px);">
                </div>
                <div style="text-align:center; z-index:1; padding:1.5rem;">
                    <div style="font-size:3rem; margin-bottom:0.75rem;">🗺️</div>
                    <div style="font-size:0.8125rem; font-weight:600; color:var(--color-dark-200);">Google Maps /
                        Leaflet.js</div>
                    <div
                        style="font-size:0.6875rem; color:var(--color-dark-300); margin-top:0.25rem; margin-bottom:1.25rem;">
                        Integrasi saat production</div>
                    <div style="display:flex; flex-direction:column; gap:0.5rem;">
                        @foreach ($cabangList as $c)
                            <div
                                style="display:flex; align-items:center; gap:0.625rem; background-color:color-mix(in srgb,var(--color-brand) 8%,transparent); border:1px solid color-mix(in srgb,var(--color-brand) 20%,transparent); border-radius:0.5rem; padding:0.5rem 0.75rem;">
                                <div
                                    style="width:0.625rem; height:0.625rem; border-radius:50%; background-color:var(--color-brand); flex-shrink:0;">
                                </div>
                                <span style="font-size:0.6875rem; color:var(--color-dark-200);">{{ $c->name }} —
                                    {{ $c->lat }}, {{ $c->lng }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-layouts.admin>
