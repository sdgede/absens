@props([
    'icon',
    'label',
    'routes' => [],
])

@php
    $aktif = collect($routes)->contains(fn($r) => request()->routeIs($r));
@endphp

<div x-data="{ open: {{ $aktif ? 'true' : 'false' }} }" class="nav-group">

    {{-- Trigger --}}
    <button
        @click="open = !open"
        class="nav-item-link nav-group-trigger"
        :class="{ 'active': {{ $aktif ? 'true' : 'false' }} || open }"
    >
        <span class="nav-item-icon">
            <i class="{{ $icon }}"></i>
        </span>
        <span>{{ $label }}</span>
        <span class="nav-group-chevron" :class="{ 'rotated': open }">
            <i class="fa-solid fa-chevron-right"></i>
        </span>
    </button>

    {{-- Dropdown items --}}
    <div
        class="nav-group-items"
        x-show="open"
        x-collapse
        x-cloak
    >
        {{ $slot }}
    </div>

</div>
