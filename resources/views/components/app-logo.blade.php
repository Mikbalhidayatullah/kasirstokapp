@props(['variant' => 'default'])

@php
    $isLight = $variant === 'light';
    $settings = $appSettings ?? \App\Models\AppSetting::DEFAULTS;
    $name = $settings['app_name'] ?? 'Vape Stock POS';
    $tagline = $settings['app_tagline'] ?? 'Vape Retail';
    $logoPath = $settings['logo_path'] ?? '';
    $initials = collect(explode(' ', $name))
        ->filter()
        ->map(fn (string $word) => mb_substr($word, 0, 1))
        ->take(2)
        ->join('');
@endphp

<div class="flex items-center gap-3">
    <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-lg text-lg font-extrabold text-white shadow-lg shadow-slate-900/20" style="background-color: var(--theme-primary, #020617);">
        @if ($logoPath)
            <img src="{{ asset($logoPath) }}" alt="{{ $name }}" class="h-full w-full object-cover">
        @else
            {{ $initials ?: 'VS' }}
        @endif
    </div>
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.3em] {{ $isLight ? 'text-emerald-200' : 'text-emerald-700' }}">{{ $tagline }}</p>
        <h1 class="text-lg font-extrabold {{ $isLight ? 'text-[#fff0e5]' : 'text-slate-950' }}">{{ $name }}</h1>
    </div>
</div>
