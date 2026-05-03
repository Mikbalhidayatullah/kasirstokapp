@props([
    'label',
    'value',
    'hint' => null,
    'tone' => 'default',
])

@php
    $toneClasses = match ($tone) {
        'success' => 'from-emerald-500/10 to-emerald-100',
        'warning' => 'from-amber-500/10 to-amber-100',
        'dark' => 'from-slate-900 to-slate-800 text-white',
        default => 'from-white to-slate-50',
    };
@endphp

<div class="surface-card bg-gradient-to-br {{ $toneClasses }} p-5">
    <p class="text-xs font-semibold uppercase tracking-[0.25em] {{ $tone === 'dark' ? 'text-white/65' : 'text-slate-500' }}">{{ $label }}</p>
    <p class="mt-4 text-3xl font-extrabold {{ $tone === 'dark' ? 'text-white' : 'text-slate-950' }}">{{ $value }}</p>
    @if ($hint)
        <p class="mt-3 text-sm {{ $tone === 'dark' ? 'text-white/70' : 'text-slate-500' }}">{{ $hint }}</p>
    @endif
</div>
