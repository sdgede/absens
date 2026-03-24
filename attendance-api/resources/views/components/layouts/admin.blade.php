<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} — {{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body style="display:flex; height:100vh; overflow:hidden;">

    <aside style="
        width: 240px;
        flex-shrink: 0;
        background-color: var(--color-dark-800);
        border-right: 1px solid var(--color-dark-500);
        display: flex;
        flex-direction: column;
    ">
        {{-- Profil pengguna (atas) --}}
        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-dark-500);">
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <div
                    style="width:2.5rem; height:2.5rem; border-radius:0.625rem; background:linear-gradient(135deg, var(--color-indigo), var(--color-violet)); display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700; color:white; flex-shrink:0; letter-spacing:0.05em;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}{{ strtoupper(substr(strstr(auth()->user()->name, ' '), 1, 1)) }}
                </div>
                <div style="flex:1; min-width:0;">
                    <div
                        style="font-weight:700; font-size:0.875rem; color:var(--color-dark-50); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        {{ auth()->user()->name }}
                    </div>
                    <div
                        style="font-family:var(--font-mono); font-size:0.5625rem; color:var(--color-dark-300); letter-spacing:0.08em; margin-top:0.125rem;">
                        Administrator
                    </div>
                </div>
            </div>
        </div>

        {{-- Navigasi --}}
        <nav style="flex:1; overflow-y:auto; padding: 0.5rem 0;">

            <div class="nav-section-label">Master</div>

            <x-admin.nav-item route="admin.dashboard" icon="fa-solid fa-gauge" label="Dashboard" />
            <x-admin.nav-item route="admin.monitoring" icon="fa-solid fa-location-dot" label="Monitoring" :badge="3" />
            <x-admin.nav-item route="admin.izin" icon="fa-solid fa-clipboard" label="Izin & Cuti" :badge="5" />

            <div class="nav-section-label">Manajemen</div>

            <x-admin.nav-group icon="fa-solid fa-layer-group" label="Manajemen" :routes="['admin.sesi', 'admin.karyawan', 'admin.cabang']">
                <x-admin.nav-item route="admin.sesi" icon="fa-solid fa-clock" label="Sesi Absensi" />
                <x-admin.nav-item route="admin.karyawan" icon="fa-solid fa-users" label="Karyawan" />
                <x-admin.nav-item route="admin.cabang" icon="fa-solid fa-building" label="Cabang" />
            </x-admin.nav-group>

            <div class="nav-section-label">Laporan</div>

            <x-admin.nav-item route="admin.laporan" icon="fa-solid fa-chart-line" label="Laporan" />
            <x-admin.nav-item route="admin.notifikasi" icon="fa-solid fa-bell" label="Notifikasi" :badge="2" />
        </nav>

        {{-- Logout --}}
        <div style="padding:0.75rem 1rem; border-top:1px solid var(--color-dark-500);">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-item-link" onmouseover="this.style.color='var(--color-rose)'"
                    onmouseout="this.style.color=''">
                    <span class="nav-item-icon">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </span>
                    <span>Keluar</span>
                </button>
            </form>
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
                {{ $actions ?? '' }}

                <span
                    style="font-family:var(--font-mono); font-size:0.6875rem; color:var(--color-dark-200); background-color:var(--color-dark-700); border:1px solid var(--color-dark-500); padding:0.3125rem 0.75rem; border-radius:0.5rem;">
                    {{ now()->translatedFormat('l, d F Y') }}
                </span>

                <a href="{{ route('admin.notifikasi') }}" class="topbar-notif">
                    <i class="fa-solid fa-bell"></i>
                    <span
                        style="position:absolute; top:-4px; right:-4px; width:1rem; height:1rem; background-color:var(--color-rose); border-radius:50%; font-size:0.5625rem; font-weight:700; color:white; display:flex; align-items:center; justify-content:center; font-family:var(--font-mono);">2</span>
                </a>
            </div>
        </header>

        <main style="flex:1; overflow-y:auto; padding:1.5rem;">
            {{ $slot }}
        </main>
    </div>

</body>

</html>
