<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} — AttendX</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body style="display:flex; height:100vh; overflow:hidden;">

    {{-- ===== Sidebar ===== --}}
    <aside style="
        width: 240px;
        flex-shrink: 0;
        background-color: var(--color-dark-800);
        border-right: 1px solid var(--color-dark-500);
        display: flex;
        flex-direction: column;
    ">
        {{-- Logo --}}
        <div style="padding: 1.25rem; border-bottom: 1px solid var(--color-dark-500);">
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <div
                    style="width:2.25rem; height:2.25rem; border-radius:0.625rem; background:linear-gradient(135deg, var(--color-brand), var(--color-indigo)); display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0;">
                    👁</div>
                <div>
                    <div style="font-weight:800; font-size:0.9375rem; color:var(--color-dark-50);">AttendX</div>
                    <div
                        style="font-family:var(--font-mono); font-size:0.5625rem; color:var(--color-dark-300); letter-spacing:0.12em;">
                        PANEL ADMIN</div>
                </div>
            </div>
        </div>

        {{-- Info tenant --}}
        <div style="padding: 0.75rem;">
            <div
                style="background-color:var(--color-dark-700); border:1px solid var(--color-dark-500); border-radius:0.625rem; padding:0.75rem 1rem;">
                <div
                    style="font-size:0.75rem; font-weight:700; color:var(--color-dark-50); display:flex; align-items:center; gap:0.375rem;">
                    <span
                        style="width:0.375rem; height:0.375rem; border-radius:50%; background-color:var(--color-emerald); display:inline-block; animation:var(--animate-pulse2);"></span>
                    {{ auth()->user()->tenant->name ?? 'PT Sample Company' }}
                </div>
                <div style="font-size:0.625rem; color:var(--color-dark-300); margin-top:0.25rem;">
                    {{ auth()->user()->tenant->branches_count ?? 3 }} cabang ·
                    {{ auth()->user()->tenant->users_count ?? 47 }} karyawan
                </div>
            </div>
        </div>

        {{-- Navigasi --}}
        <nav style="flex:1; overflow-y:auto; padding: 0.25rem 0;">
            <div
                style="font-size:0.5625rem; font-weight:700; letter-spacing:0.12em; color:var(--color-dark-300); padding:0.5rem 1.25rem 0.25rem; text-transform:uppercase;">
                Utama</div>

            <x-admin.nav-item route="admin.dashboard" icon="📊" label="Ringkasan" />
            <x-admin.nav-item route="admin.monitoring" icon="📍" label="Monitoring" :badge="3" />
            <x-admin.nav-item route="admin.izin" icon="📋" label="Izin & Cuti" :badge="5" />

            <div
                style="font-size:0.5625rem; font-weight:700; letter-spacing:0.12em; color:var(--color-dark-300); padding:0.5rem 1.25rem 0.25rem; text-transform:uppercase; margin-top:0.25rem;">
                Manajemen</div>

            <x-admin.nav-item route="admin.sesi" icon="🕐" label="Sesi Absensi" />
            <x-admin.nav-item route="admin.karyawan" icon="👥" label="Karyawan" />
            <x-admin.nav-item route="admin.cabang" icon="🏢" label="Cabang" />

            <div
                style="font-size:0.5625rem; font-weight:700; letter-spacing:0.12em; color:var(--color-dark-300); padding:0.5rem 1.25rem 0.25rem; text-transform:uppercase; margin-top:0.25rem;">
                Laporan</div>

            <x-admin.nav-item route="admin.laporan" icon="📈" label="Laporan" />
            <x-admin.nav-item route="admin.notifikasi" icon="🔔" label="Notifikasi" :badge="2" />
        </nav>

        {{-- Profil pengguna --}}
        <div style="padding:0.75rem 1rem; border-top:1px solid var(--color-dark-500);">
            <div style="display:flex; align-items:center; gap:0.625rem;">
                <div
                    style="width:2rem; height:2rem; border-radius:0.5rem; background:linear-gradient(135deg, var(--color-indigo), var(--color-violet)); display:flex; align-items:center; justify-content:center; font-size:0.6875rem; font-weight:700; color:white; flex-shrink:0;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}{{ strtoupper(substr(strstr(auth()->user()->name, ' '), 1, 1)) }}
                </div>
                <div style="flex:1; min-width:0;">
                    <div
                        style="font-size:0.75rem; font-weight:600; color:var(--color-dark-50); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        {{ auth()->user()->name }}</div>
                    <div style="font-size:0.625rem; color:var(--color-dark-300);">Administrator</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Keluar"
                        style="width:1.75rem; height:1.75rem; border:1px solid var(--color-dark-500); border-radius:0.4375rem; display:flex; align-items:center; justify-content:center; color:var(--color-dark-300); background:none; cursor:pointer; font-size:0.8125rem; transition:all 0.15s;"
                        onmouseover="this.style.borderColor='var(--color-rose)';this.style.color='var(--color-rose)';"
                        onmouseout="this.style.borderColor='var(--color-dark-500)';this.style.color='var(--color-dark-300)';">⎋</button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ===== Konten Utama ===== --}}
    <div style="flex:1; display:flex; flex-direction:column; overflow:hidden;">

        {{-- Topbar --}}
        <header style="
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            background-color: var(--color-dark-800);
            border-bottom: 1px solid var(--color-dark-500);
            gap: 1rem;
            flex-shrink: 0;
        ">
            <div>
                <h1 style="font-size:1.125rem; font-weight:800; color:var(--color-dark-50);">{{ $title ?? 'Dashboard' }}
                </h1>
                @isset($subtitle)
                    <p style="font-size:0.75rem; color:var(--color-dark-300); margin-top:0.125rem;">{{ $subtitle }}</p>
                @endisset
            </div>
            <div style="margin-left:auto; display:flex; align-items:center; gap:0.625rem;">
                {{-- Slot aksi topbar (tombol export, dll) --}}
                {{ $actions ?? '' }}

                {{-- Tanggal --}}
                <span
                    style="font-family:var(--font-mono); font-size:0.6875rem; color:var(--color-dark-200); background-color:var(--color-dark-700); border:1px solid var(--color-dark-500); padding:0.3125rem 0.75rem; border-radius:0.5rem;">
                    {{ now()->translatedFormat('l, d F Y') }}
                </span>

                {{-- Notifikasi --}}
                <a href="{{ route('admin.notifikasi') }}"
                    style="width:2.125rem; height:2.125rem; border:1px solid var(--color-dark-500); border-radius:0.5rem; display:flex; align-items:center; justify-content:center; color:var(--color-dark-200); text-decoration:none; font-size:0.9375rem; position:relative; transition:all 0.15s;"
                    onmouseover="this.style.borderColor='var(--color-brand)';this.style.color='var(--color-brand)';"
                    onmouseout="this.style.borderColor='var(--color-dark-500)';this.style.color='var(--color-dark-200)';">
                    🔔
                    <span
                        style="position:absolute; top:-4px; right:-4px; width:1rem; height:1rem; background-color:var(--color-rose); border-radius:50%; font-size:0.5625rem; font-weight:700; color:white; display:flex; align-items:center; justify-content:center; font-family:var(--font-mono);">2</span>
                </a>
            </div>
        </header>

        {{-- Isi halaman --}}
        <main style="flex:1; overflow-y:auto; padding:1.5rem;">
            {{ $slot }}
        </main>
    </div>

</body>

</html>
