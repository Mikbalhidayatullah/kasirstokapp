@props([
    'title' => 'Dashboard',
    'heading' => null,
    'description' => null,
])

@php
    $user = auth()->user();
    $navigationLinks = [
        [
            'label' => 'Ringkasan',
            'href' => route('dashboard'),
            'active' => request()->routeIs('dashboard'),
        ],
    ];

    if ($user->canManageStock()) {
        $navigationLinks = [
            ...$navigationLinks,
            [
                'label' => 'Kategori',
                'href' => route('stock.categories.index'),
                'active' => request()->routeIs('stock.categories.*'),
            ],
            [
                'label' => 'Produk',
                'href' => route('stock.products.index'),
                'active' => request()->routeIs('stock.products.*'),
            ],
            [
                'label' => 'Mutasi Stok',
                'href' => route('stock.movements.index'),
                'active' => request()->routeIs('stock.movements.*'),
            ],
        ];
    }

    if ($user->canHandleCashier()) {
        $navigationLinks = [
            ...$navigationLinks,
            [
                'label' => 'Kasir',
                'href' => route('cashier.index'),
                'active' => request()->routeIs('cashier.*'),
            ],
            [
                'label' => 'Riwayat Penjualan',
                'href' => route('sales.index'),
                'active' => request()->routeIs('sales.*'),
            ],
            [
                'label' => 'Laporan Penjualan',
                'href' => route('reports.sales.index'),
                'active' => request()->routeIs('reports.sales.*'),
            ],
        ];
    }

    $activeNavigation = collect($navigationLinks)->firstWhere('active', true);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
        <div class="grid gap-5 sm:grid-cols-[280px_minmax(0,1fr)]">
            <aside class="surface-card p-5 sm:sticky sm:top-5 sm:h-[calc(100vh-2.5rem)]">
                <div class="flex h-full flex-col gap-5">
                    <x-app-logo />

                    <div class="surface-dark p-4">
                        <p class="text-xs uppercase tracking-[0.28em] text-white/60">Akun aktif</p>
                        <h2 class="mt-3 text-xl font-bold">{{ $user->name }}</h2>
                        <p class="mt-1 text-sm text-white/70">{{ $user->role->label() }}</p>
                    </div>

                    <button
                        type="button"
                        class="flex items-center justify-between rounded-3xl border border-amber-200 bg-amber-50/80 px-4 py-3 text-left sm:hidden"
                        data-mobile-nav-toggle
                        aria-expanded="false"
                        aria-controls="sidebar-navigation"
                    >
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-700">Menu Navigasi</p>
                            <p class="mt-1 text-sm font-bold text-slate-950">{{ $activeNavigation['label'] ?? 'Pilih Menu' }}</p>
                            <p class="mt-1 text-xs text-slate-600">Ketuk untuk menampilkan semua menu.</p>
                        </div>
                        <span class="rounded-2xl bg-amber-400 px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-950">
                            Menu
                        </span>
                    </button>

                    <nav id="sidebar-navigation" class="grid gap-2" data-mobile-nav>
                        @foreach ($navigationLinks as $link)
                            <x-navigation-link :href="$link['href']" :active="$link['active']">
                                {{ $link['label'] }}
                            </x-navigation-link>
                        @endforeach
                    </nav>

                    <div class="mt-auto rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm font-semibold text-slate-700">Fokus hari ini</p>
                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            Pantau stok tipis, percepat checkout, dan jaga histori transaksi tetap rapi.
                        </p>
                    </div>
                </div>
            </aside>

            <div class="space-y-5">
                <header class="surface-card flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Operasional Toko</p>
                        <h1 class="mt-2 text-3xl font-extrabold">{{ $heading ?? $title }}</h1>
                        @if ($description)
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">{{ $description }}</p>
                        @endif
                    </div>

                    <div class="flex items-center gap-3">
                        @isset($actions)
                            {{ $actions }}
                        @endisset

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-ui.button variant="ghost" type="submit">Logout</x-ui.button>
                        </form>
                    </div>
                </header>

                @if (session('status'))
                    <div class="surface-card flex items-start justify-between gap-4 border border-emerald-100 bg-emerald-50/80 p-4 text-sm text-emerald-800">
                        <p>{{ session('status') }}</p>
                        <button type="button" data-dismiss-parent class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Tutup</button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="surface-card flex items-start justify-between gap-4 border border-rose-100 bg-rose-50/80 p-4 text-sm text-rose-800">
                        <p>{{ session('error') }}</p>
                        <button type="button" data-dismiss-parent class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-700">Tutup</button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="surface-card border border-amber-100 bg-amber-50/80 p-4 text-sm text-amber-900">
                        <p class="font-semibold">Beberapa input masih perlu diperbaiki:</p>
                        <ul class="mt-2 space-y-1 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <main class="space-y-5">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
