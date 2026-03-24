@props([
    'label',
    'nilai',
    'sub'       => null,
    'badge'     => null,
    'badgeTipe' => 'naik',   //{{-- naik | turun | sama --}}
    'ikon'      => null,
    'warna'     => 'brand', // {{-- brand | emerald | amber | rose --}}
    'href'      => null,
])

@php
    $warnaMap = [
        'brand'   => 'var(--color-brand)',
        'emerald' => 'var(--color-emerald)',
        'amber'   => 'var(--color-amber)',
        'rose'    => 'var(--color-rose)',
    ];
    $warnaBadge = match ($badgeTipe) {
        'naik'  => ['bg' => 'color-mix(in srgb, var(--color-emerald) 12%, transparent)', 'teks' => 'var(--color-emerald)'],
        'turun' => ['bg' => 'color-mix(in srgb, var(--color-rose) 12%, transparent)',    'teks' => 'var(--color-rose)'],
        default => ['bg' => 'color-mix(in srgb, var(--color-amber) 12%, transparent)',   'teks' => 'var(--color-amber)'],
    };
    $warnaNilai = $warnaMap[$warna] ?? 'var(--color-brand)';
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    style="
        background-color: var(--color-dark-800);
        border: 1px solid var(--color-dark-500);
        border-radius: 0.75rem;
        padding: 1rem;
        position: relative;
        overflow: hidden;
        {{ $href ? 'cursor:pointer; text-decoration:none; display:block;' : '' }}
        transition: border-color 0.2s, transform 0.2s;
        animation: var(--animate-fade-up);
    "
    onmouseover="{{ $href ? "this.style.borderColor='var(--color-dark-400)';this.style.transform='translateY(-1px)';" : '' }}"
    onmouseout="{{ $href ? "this.style.borderColor='var(--color-dark-500)';this.style.transform='translateY(0)';" : '' }}"
>
    {{-- Lingkaran dekoratif --}}
    <div style="position:absolute; top:0; right:0; width:5rem; height:5rem; border-radius:50%; background-color:{{ $warnaNilai }}; opacity:0.07; transform:translate(20px,-20px); pointer-events:none;"></div>

    @if ($ikon)
        <span style="position:absolute; top:0.875rem; right:0.875rem; font-size:1.125rem; opacity:0.4; color:{{ $warnaNilai }};">
            <i class="{{ $ikon }}"></i>
        </span>
    @endif

    <div style="font-size:0.6875rem; font-weight:700; color:var(--color-dark-300); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.625rem;">{{ $label }}</div>
    <div style="font-size:1.75rem; font-weight:800; color:var(--color-dark-50); font-family:var(--font-mono); letter-spacing:-0.04em;">{{ $nilai }}</div>

    @if ($sub)
        <div style="font-size:0.6875rem; color:var(--color-dark-300); margin-top:0.25rem;">{{ $sub }}</div>
    @endif

    @if ($badge)
        <div style="display:inline-flex; align-items:center; gap:0.1875rem; font-size:0.625rem; font-weight:700; padding:0.125rem 0.4375rem; border-radius:9999px; margin-top:0.5rem; font-family:var(--font-mono); background-color:{{ $warnaBadge['bg'] }}; color:{{ $warnaBadge['teks'] }};">
            {{ $badge }}
        </div>
    @endif
</{{ $tag }}>
