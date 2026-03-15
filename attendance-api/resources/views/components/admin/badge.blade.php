@props([
    'tipe' => 'hadir', //{{-- hadir | telat | alpha | izin | aktif | nonaktif | pending --}}
    'teks' => null,
])

@php
    $labelDefault = [
        'hadir'    => 'Hadir',
        'telat'    => 'Terlambat',
        'alpha'    => 'Alpha',
        'izin'     => 'Izin',
        'aktif'    => 'Aktif',
        'nonaktif' => 'Non-aktif',
        'pending'  => 'Pending',
        'disetujui'=> 'Disetujui',
        'ditolak'  => 'Ditolak',
        'selesai'  => 'Selesai',
        'menunggu' => 'Menunggu',
    ];

    $gaya = [
        'hadir'    => ['bg' => 'color-mix(in srgb, var(--color-emerald) 12%, transparent)', 'teks' => 'var(--color-emerald)'],
        'telat'    => ['bg' => 'color-mix(in srgb, var(--color-amber)   12%, transparent)', 'teks' => 'var(--color-amber)'],
        'alpha'    => ['bg' => 'color-mix(in srgb, var(--color-rose)    12%, transparent)', 'teks' => 'var(--color-rose)'],
        'izin'     => ['bg' => 'color-mix(in srgb, var(--color-violet)  12%, transparent)', 'teks' => 'var(--color-violet)'],
        'aktif'    => ['bg' => 'color-mix(in srgb, var(--color-emerald) 12%, transparent)', 'teks' => 'var(--color-emerald)'],
        'nonaktif' => ['bg' => 'color-mix(in srgb, var(--color-rose)    10%, transparent)', 'teks' => 'var(--color-rose)'],
        'pending'  => ['bg' => 'color-mix(in srgb, var(--color-amber)   12%, transparent)', 'teks' => 'var(--color-amber)'],
        'disetujui'=> ['bg' => 'color-mix(in srgb, var(--color-emerald) 12%, transparent)', 'teks' => 'var(--color-emerald)'],
        'ditolak'  => ['bg' => 'color-mix(in srgb, var(--color-rose)    12%, transparent)', 'teks' => 'var(--color-rose)'],
        'selesai'  => ['bg' => 'color-mix(in srgb, var(--color-dark-300) 20%, transparent)','teks' => 'var(--color-dark-300)'],
        'menunggu' => ['bg' => 'color-mix(in srgb, var(--color-amber)   12%, transparent)', 'teks' => 'var(--color-amber)'],
    ];

    $g = $gaya[$tipe] ?? $gaya['hadir'];
    $label = $teks ?? ($labelDefault[$tipe] ?? $tipe);
@endphp

<span style="
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.1875rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 700;
    font-family: var(--font-mono);
    background-color: {{ $g['bg'] }};
    color: {{ $g['teks'] }};
">
    <span style="width:0.3125rem; height:0.3125rem; border-radius:50%; background-color:currentColor;"></span>
    {{ $label }}
</span>
