@props([
    'route',
    'icon',
    'label',
    'badge' => null,
])

@php
    $aktif = request()->routeIs($route);
@endphp

<a
    href="{{ route($route) }}"
    class="nav-item {{ $aktif ? 'aktif' : '' }}"
    style="text-decoration:none;"
>
    <span style="width:1.25rem; text-align:center; font-size:0.9375rem;">{{ $icon }}</span>
    <span>{{ $label }}</span>
    @if ($badge)
        <span style="margin-left:auto; background-color:var(--color-rose); color:white; font-size:0.625rem; font-weight:700; padding:0.125rem 0.375rem; border-radius:9999px; font-family:var(--font-mono);">{{ $badge }}</span>
    @endif
</a>
