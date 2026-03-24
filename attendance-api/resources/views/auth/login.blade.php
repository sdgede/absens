<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="min-height:100vh; display:flex;">

    <div style="
        display: none;
        width: 480px;
        flex-shrink: 0;
        flex-direction: column;
        justify-content: space-between;
        background-color: var(--color-dark-800);
        border-right: 1px solid var(--color-dark-500);
        padding: 2.5rem;
        position: relative;
        overflow: hidden;
    " class="branding-panel">

        <div class="dot-grid" style="position:absolute; inset:0; opacity:0.4;"></div>
        <div style="position:absolute; bottom:-8rem; left:-8rem; width:20rem; height:20rem; border-radius:50%; background-color:color-mix(in srgb, var(--color-brand) 10%, transparent); filter:blur(64px); pointer-events:none;"></div>
        <div style="position:absolute; top:5rem; right:-5rem; width:15rem; height:15rem; border-radius:50%; background-color:color-mix(in srgb, var(--color-indigo) 10%, transparent); filter:blur(64px); pointer-events:none;"></div>

        <div style="position:relative; z-index:10; display:flex; align-items:center; gap:0.75rem;">
            <div style="width:2.5rem; height:2.5rem; border-radius:0.75rem; background:linear-gradient(135deg, var(--color-brand), var(--color-indigo)); display:flex; align-items:center; justify-content:center; font-size:1.25rem;">👁</div>
            <div>
                <div style="font-weight:800; font-size:0.9375rem; color:var(--color-dark-50);">Abasen</div>
            </div>
        </div>

        {{-- Tagline --}}
        <div style="position:relative; z-index:10;">
            <h2 style="font-size:1.875rem; font-weight:800; color:var(--color-dark-50); line-height:1.25; margin-bottom:1rem;">
                Sistem Absensi<br>
                <span style="color:var(--color-brand);">Pengenalan Wajah</span><br>
            </h2>
            <p style="color:var(--color-dark-200); font-size:0.875rem; line-height:1.6; margin-bottom:2rem;">
                Kelola kehadiran karyawan dengan teknologi pengenalan wajah,
                validasi GPS, dan laporan real-time dalam satu platform.
            </p>

            <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:0.75rem;">
                @foreach ([
                    ['hex' => '#22D3A0', 'teks' => 'Absensi dengan pengenalan wajah & deteksi keaktifan'],
                    ['hex' => '#4F8EF7', 'teks' => 'Validasi GPS radius per cabang secara otomatis'],
                    ['hex' => '#A78BFA', 'teks' => 'Laporan & ekspor PDF/Excel secara langsung'],
                ] as $fitur)
                    <li style="display:flex; align-items:center; gap:0.75rem;">
                        <div style="width:2rem; height:2rem; border-radius:0.5rem; background-color:color-mix(in srgb, {{ $fitur['hex'] }} 10%, transparent); border:1px solid color-mix(in srgb, {{ $fitur['hex'] }} 20%, transparent); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                <path d="M2 7l3.5 3.5L12 3.5" stroke="{{ $fitur['hex'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <span style="font-size:0.875rem; color:var(--color-dark-200);">{{ $fitur['teks'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div style="flex:1; display:flex; align-items:center; justify-content:center; padding:1.5rem;">
        <div style="width:100%; max-width:24rem; animation:var(--animate-fade-up);">

            {{-- Logo mobile --}}
            <div class="logo-mobile" style="display:flex; align-items:center; gap:0.75rem; margin-bottom:2rem; justify-content:center;">
                <div style="width:2.5rem; height:2.5rem; border-radius:0.75rem; background:linear-gradient(135deg, var(--color-brand), var(--color-indigo)); display:flex; align-items:center; justify-content:center; font-size:1.25rem;">👁</div>
                <div>
                    <div style="font-weight:800; font-size:0.9375rem; color:var(--color-dark-50);">AttendX</div>
                    <div style="font-family:var(--font-mono); font-size:0.625rem; color:var(--color-dark-300); letter-spacing:0.1em;">PANEL ADMIN</div>
                </div>
            </div>

            {{-- Judul --}}
            <div style="margin-bottom:2rem;">
                <h1 style="font-size:1.5rem; font-weight:800; color:var(--color-dark-50); margin-bottom:0.25rem;">Selamat datang</h1>
            </div>

            {{-- Pesan error dari session --}}
            @if (session('error'))
                <div style="background-color:color-mix(in srgb, var(--color-rose) 10%, transparent); border:1px solid color-mix(in srgb, var(--color-rose) 30%, transparent); border-radius:0.75rem; padding:0.75rem 1rem; margin-bottom:1rem;">
                    <p style="font-size:0.75rem; color:var(--color-rose); font-weight:600;">{{ session('error') }}</p>
                </div>
            @endif

            {{-- Pesan sukses --}}
            @if (session('status'))
                <div style="background-color:color-mix(in srgb, var(--color-emerald) 10%, transparent); border:1px solid color-mix(in srgb, var(--color-emerald) 30%, transparent); border-radius:0.75rem; padding:0.75rem 1rem; margin-bottom:1rem;">
                    <p style="font-size:0.75rem; color:var(--color-emerald); font-weight:600;">{{ session('status') }}</p>
                </div>
            @endif

            {{-- Formulir --}}
            <form method="POST" action="{{ route('login') }}" id="formLogin">
                @csrf

                <div style="display:flex; flex-direction:column; gap:1rem;">

                    {{-- Email --}}
                    <div>
                        <label for="email" style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-dark-200); margin-bottom:0.375rem;">
                            Alamat Email
                        </label>
                        <div style="position:relative;">
                            <span style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); color:var(--color-dark-300); pointer-events:none;">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <rect x="1" y="3" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.2"/>
                                    <path d="M1 5.5l7 4.5 7-4.5" stroke="currentColor" stroke-width="1.2"/>
                                </svg>
                            </span>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="admin@perusahaan.com"
                                autocomplete="email"
                                autofocus
                                class="input-field input-icon-left @error('email') error @enderror"
                            >
                        </div>
                        @error('email')
                            <p style="font-size:0.6875rem; color:var(--color-rose); margin-top:0.375rem; font-weight:500;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kata Sandi --}}
                    <div>
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.375rem;">
                            <label for="password" style="font-size:0.75rem; font-weight:600; color:var(--color-dark-200);">
                                Kata Sandi
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                    style="font-size:0.6875rem; color:var(--color-brand); font-weight:600; text-decoration:none;">
                                    Lupa kata sandi?
                                </a>
                            @endif
                        </div>
                        <div style="position:relative;">
                            <span style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); color:var(--color-dark-300); pointer-events:none;">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <rect x="2" y="7" width="12" height="8" rx="2" stroke="currentColor" stroke-width="1.2"/>
                                    <path d="M5 7V5a3 3 0 016 0v2" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                    <circle cx="8" cy="11" r="1.2" fill="currentColor"/>
                                </svg>
                            </span>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Masukkan kata sandi"
                                autocomplete="current-password"
                                class="input-field input-icon-left @error('password') error @enderror"
                                style="padding-right:2.5rem;"
                            >
                            <button
                                type="button"
                                onclick="toggleKataSandi()"
                                aria-label="Tampilkan atau sembunyikan kata sandi"
                                style="position:absolute; right:0.75rem; top:50%; transform:translateY(-50%); color:var(--color-dark-300); background:none; border:none; cursor:pointer; padding:0; display:flex; align-items:center;"
                            >
                                <svg id="ikonMata" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M1 8s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z" stroke="currentColor" stroke-width="1.2"/>
                                    <circle cx="8" cy="8" r="2" stroke="currentColor" stroke-width="1.2"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p style="font-size:0.6875rem; color:var(--color-rose); margin-top:0.375rem; font-weight:500;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Ingat Saya --}}
                    <div style="display:flex; align-items:center; gap:0.625rem;">
                        <div style="position:relative; width:1rem; height:1rem; flex-shrink:0;">
                            <input
                                type="checkbox"
                                id="ingat"
                                name="remember"
                                {{ old('remember') ? 'checked' : '' }}
                                style="width:1rem; height:1rem; appearance:none; background-color:var(--color-dark-700); border:1px solid var(--color-dark-500); border-radius:0.25rem; cursor:pointer; transition:all 0.15s;"
                                onchange="this.style.backgroundColor = this.checked ? 'var(--color-brand)' : 'var(--color-dark-700)'; this.style.borderColor = this.checked ? 'var(--color-brand)' : 'var(--color-dark-500)'; document.getElementById('centangIngat').style.opacity = this.checked ? '1' : '0';"
                            >
                            <svg id="centangIngat" style="position:absolute; inset:0; width:1rem; height:1rem; pointer-events:none; opacity:{{ old('remember') ? '1' : '0' }};" viewBox="0 0 16 16" fill="none">
                                <path d="M3 8l3.5 3.5L13 4.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <label for="ingat" style="font-size:0.75rem; color:var(--color-dark-200); cursor:pointer; user-select:none;">
                            Ingat saya selama 30 hari
                        </label>
                    </div>

                    {{-- Tombol Masuk --}}
                    <button type="submit" id="tombolMasuk" class="btn-primary" style="margin-top:0.5rem;">
                        <span id="teksBtn">Masuk ke Dashboard</span>
                        <svg id="panahBtn" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M3 8h10M9 4l4 4-4 4" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <svg id="spinnerBtn" style="display:none; animation:var(--animate-spin2);" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <circle cx="8" cy="8" r="6" stroke="rgba(255,255,255,0.3)" stroke-width="2"/>
                            <path d="M8 2a6 6 0 016 6" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>

                </div>
            </form>

            <p style="text-align:center; font-size:0.6875rem; color:var(--color-dark-300); margin-top:2rem;">
                &copy; {{ date('Y') }} Abssensi &middot;
                <span style="color:var(--color-dark-200);">v{{ config('app.version') }}</span>
            </p>

        </div>
    </div>

</body>

<style>
    @media (min-width: 1024px) {
        .branding-panel { display: flex !important; }
        .logo-mobile    { display: none !important; }
    }
</style>

<script>
    function toggleKataSandi() {
        const input = document.getElementById('password');
        const ikon  = document.getElementById('ikonMata');

        if (input.type === 'password') {
            input.type = 'text';
            ikon.innerHTML = `
                <path d="M1 8s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z" stroke="currentColor" stroke-width="1.2"/>
                <line x1="2" y1="2" x2="14" y2="14" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
            `;
        } else {
            input.type = 'password';
            ikon.innerHTML = `
                <path d="M1 8s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z" stroke="currentColor" stroke-width="1.2"/>
                <circle cx="8" cy="8" r="2" stroke="currentColor" stroke-width="1.2"/>
            `;
        }
    }

    document.getElementById('formLogin').addEventListener('submit', function () {
        const tombol  = document.getElementById('tombolMasuk');
        const teks    = document.getElementById('teksBtn');
        const panah   = document.getElementById('panahBtn');
        const spinner = document.getElementById('spinnerBtn');

        tombol.disabled = true;
        tombol.style.opacity = '0.8';
        teks.textContent = 'Memverifikasi...';
        panah.style.display  = 'none';
        spinner.style.display = 'block';
    });
</script>
</html>
