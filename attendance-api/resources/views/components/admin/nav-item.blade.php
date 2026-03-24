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
    class="nav-item-link {{ $aktif ? 'active' : '' }}"
    style="text-decoration:none;"
>
<span class="nav-item-icon">
        <i class="{{ $icon }}"></i>
    </span>
    <span>{{ $label }}</span>
    @if ($badge)
        <span class="nav-badge">{{ $badge }}</span>
    @endif
</a>
