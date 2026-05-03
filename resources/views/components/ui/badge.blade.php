@props(['tone' => 'neutral'])

@php
    $classes = match ($tone) {
        'success' => 'bg-emerald-100 text-emerald-700',
        'warning' => 'bg-amber-100 text-amber-700',
        'danger' => 'bg-rose-100 text-rose-700',
        'dark' => 'bg-slate-900 text-white',
        default => 'bg-slate-100 text-slate-700',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] {$classes}"]) }}>
    {{ $slot }}
</span>
