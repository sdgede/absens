<x-layouts.admin title="Notifikasi" subtitle="Kotak masuk & kirim pengumuman">

    <x-slot:actions>
        <form method="POST" action="{{ route('admin.notifikasi.baca-semua') }}" style="display:inline;">
            @csrf
            <button type="submit" class="btn-secondary">✓ Tandai Semua Dibaca</button>
        </form>
    </x-slot:actions>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">

        {{-- Kotak Masuk --}}
        <div class="card">
            <div style="font-size:0.8125rem; font-weight:700; color:var(--color-dark-50); margin-bottom:0.25rem;">Kotak
                Masuk</div>
            <div style="font-size:0.6875rem; color:var(--color-dark-300); margin-bottom:1rem;">{{ $jumlahBelumDibaca }}
                belum dibaca</div>

            @forelse ($notifikasiList as $notif)
                @php
                    $ikonMap = [
                        'attendance' => ['ikon' => '✅', 'bg' => 'color-mix(in srgb, var(--color-emerald) 10%, transparent)'],
                        'leave' => ['ikon' => '📋', 'bg' => 'color-mix(in srgb, var(--color-amber)   10%, transparent)'],
                        'announcement' => ['ikon' => '📣', 'bg' => 'color-mix(in srgb, var(--color-brand)   10%, transparent)'],
                    ];
                    $tipe = $notif->data['type'] ?? 'announcement';
                    $cfg = $ikonMap[$tipe] ?? $ikonMap['announcement'];
                @endphp
                <div
                    style="display:flex; gap:0.625rem; padding:0.625rem 0; border-bottom:1px solid color-mix(in srgb, var(--color-dark-500) 40%, transparent);">
                    <div
                        style="width:1.875rem; height:1.875rem; border-radius:0.5rem; background-color:{{ $cfg['bg'] }}; display:flex; align-items:center; justify-content:center; font-size:0.8125rem; flex-shrink:0;">
                        {{ $cfg['ikon'] }}</div>
                    <div style="flex:1;">
                        <div style="font-size:0.6875rem; color:var(--color-dark-200); line-height:1.5;">
                            {{ $notif->data['body'] ?? '-' }}</div>
                        <div
                            style="font-size:0.5625rem; color:var(--color-dark-300); margin-top:0.25rem; font-family:var(--font-mono);">
                            {{ $notif->created_at->diffForHumans() }}</div>
                    </div>
                    @if (!$notif->read_at)
                        <div
                            style="width:0.4375rem; height:0.4375rem; border-radius:50%; background-color:var(--color-brand); flex-shrink:0; margin-top:0.25rem;">
                        </div>
                    @endif
                </div>
            @empty
                <p style="font-size:0.8125rem; color:var(--color-dark-300); text-align:center; padding:1rem 0;">Tidak ada
                    notifikasi.</p>
            @endforelse

            @if ($notifikasiList->hasPages())
                <div style="margin-top:0.75rem;">{{ $notifikasiList->links() }}</div>
            @endif
        </div>

        {{-- Kirim Pengumuman --}}
        <div class="card">
            <div style="font-size:0.8125rem; font-weight:700; color:var(--color-dark-50); margin-bottom:1rem;">Kirim
                Pengumuman</div>

            @if (session('sukses_kirim'))
                <div
                    style="background-color:color-mix(in srgb, var(--color-emerald) 10%, transparent); border:1px solid color-mix(in srgb, var(--color-emerald) 30%, transparent); border-radius:0.625rem; padding:0.625rem 0.875rem; margin-bottom:1rem;">
                    <p style="font-size:0.75rem; color:var(--color-emerald); font-weight:600;">{{ session('sukses_kirim') }}
                    </p>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.notifikasi.kirim') }}"
                style="display:flex; flex-direction:column; gap:0.875rem;">
                @csrf

                <div>
                    <label
                        style="display:block; font-size:0.625rem; color:var(--color-dark-300); margin-bottom:0.375rem;">Judul</label>
                    <input type="text" name="judul" value="{{ old('judul') }}" placeholder="Judul pengumuman..."
                        class="input-field @error('judul') error @enderror">
                    @error('judul') <p style="font-size:0.625rem; color:var(--color-rose); margin-top:0.25rem;">
                    {{ $message }}</p> @enderror
                </div>

                <div>
                    <label
                        style="display:block; font-size:0.625rem; color:var(--color-dark-300); margin-bottom:0.375rem;">Isi
                        Pesan</label>
                    <textarea name="pesan" rows="4" placeholder="Isi pengumuman..."
                        class="input-field @error('pesan') error @enderror"
                        style="resize:none; line-height:1.6;">{{ old('pesan') }}</textarea>
                    @error('pesan') <p style="font-size:0.625rem; color:var(--color-rose); margin-top:0.25rem;">
                    {{ $message }}</p> @enderror
                </div>

                <div>
                    <label
                        style="display:block; font-size:0.625rem; color:var(--color-dark-300); margin-bottom:0.375rem;">Kirim
                        ke</label>
                    <select name="target" class="input-field @error('target') error @enderror">
                        <option value="semua">Semua Karyawan ({{ $totalKaryawan }} orang)</option>
                        @foreach ($cabangList as $c)
                            <option value="cabang_{{ $c->id }}">{{ $c->name }} ({{ $c->users_count }} orang)</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn-primary">📣 Kirim Sekarang</button>
            </form>
        </div>
    </div>

</x-layouts.admin>
