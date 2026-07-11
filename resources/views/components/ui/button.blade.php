@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
])

@php
    $base = 'inline-flex items-center justify-center rounded-lg font-semibold transition focus:outline-none focus:ring-4';

    $sizes = [
        'sm' => 'px-3 py-2 text-xs uppercase tracking-[0.18em]',
        'md' => 'px-4 py-3 text-sm',
    ];

    $variants = [
        'primary' => 'bg-slate-950 text-white hover:bg-slate-800 focus:ring-slate-200',
        'secondary' => 'bg-amber-400 text-slate-950 hover:bg-amber-300 focus:ring-amber-100',
        'ghost' => 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 focus:ring-slate-100',
        'danger' => 'bg-rose-500 text-white hover:bg-rose-400 focus:ring-rose-100',
    ];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "{$base} {$sizes[$size]} {$variants[$variant]}"]) }}>
    {{ $slot }}
</button>
