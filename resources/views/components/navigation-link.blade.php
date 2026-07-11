@props(['href', 'active' => false])

<a
    href="{{ $href }}"
    {{ $attributes->class([
        'flex items-center justify-between rounded-lg px-4 py-3 text-sm font-semibold transition',
        'bg-slate-950 text-white shadow-lg shadow-slate-900/20' => $active,
        'text-slate-600 hover:bg-slate-100 hover:text-slate-950' => ! $active,
    ]) }}
>
    <span>{{ $slot }}</span>
    @if ($active)
        <span class="rounded-full bg-white/15 px-2 py-1 text-[10px] uppercase tracking-[0.24em]">Aktif</span>
    @endif
</a>
