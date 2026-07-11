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
                'label' => 'Membership',
                'href' => route('members.index'),
                'active' => request()->routeIs('members.*'),
            ],
            [
                'label' => 'Promo & Poin',
                'href' => route('promotions.index'),
                'active' => request()->routeIs('promotions.*') || request()->routeIs('point-rewards.*'),
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

    if ($user->canManageUsers()) {
        $navigationLinks = [
            ...$navigationLinks,
            [
                'label' => 'Manajemen User',
                'href' => route('users.index'),
                'active' => request()->routeIs('users.*'),
            ],
            [
                'label' => 'Pengaturan',
                'href' => route('settings.edit'),
                'active' => request()->routeIs('settings.*'),
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
    <title>{{ $title }} | {{ $appSettings['app_name'] ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    style="--theme-primary: {{ $appSettings['theme_primary'] ?? '#020617' }}; --theme-accent: {{ $appSettings['theme_accent'] ?? '#f59e0b' }}; --theme-background: {{ $appSettings['theme_background'] ?? '#f8fafc' }};"
>
    <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
        <div class="grid gap-5 sm:block">
            <aside class="surface-card sticky top-0 z-40 -mx-4 max-h-screen overflow-hidden rounded-none border-x-0 p-4 sm:fixed sm:left-6 sm:top-5 sm:mx-0 sm:h-[calc(100vh-2.5rem)] sm:w-[280px] sm:max-h-[calc(100vh-2.5rem)] sm:rounded-lg sm:border-x sm:p-5 lg:left-8 2xl:left-[calc((100vw-80rem)/2+2rem)]">
                <div class="flex h-full min-h-0 flex-col gap-5">
                    <div class="flex items-center justify-between gap-4">
                        <x-app-logo />

                        <button
                            type="button"
                            class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-900 shadow-sm sm:hidden"
                            data-mobile-nav-toggle
                            aria-expanded="false"
                            aria-controls="mobile-sidebar-panel"
                            aria-label="Buka menu navigasi"
                        >
                            <span class="grid gap-1.5">
                                <span class="block h-0.5 w-5 rounded-full bg-current"></span>
                                <span class="block h-0.5 w-5 rounded-full bg-current"></span>
                                <span class="block h-0.5 w-5 rounded-full bg-current"></span>
                            </span>
                        </button>
                    </div>

                    <div id="mobile-sidebar-panel" class="flex max-h-[calc(100vh-5.5rem)] min-h-0 flex-1 flex-col gap-5 overflow-y-auto pr-1 sm:max-h-none sm:overflow-hidden sm:pr-0" data-mobile-nav-panel>
                        <div class="surface-dark p-4">
                            <p class="text-xs uppercase tracking-[0.28em] text-white/60">Akun aktif</p>
                            <h2 class="mt-3 text-xl font-bold">{{ $user->name }}</h2>
                            <p class="mt-1 text-sm text-white/70">{{ $user->role->label() }}</p>
                        </div>

                        <nav id="sidebar-navigation" class="grid min-h-0 flex-1 gap-2 overflow-y-auto pr-1">
                            @foreach ($navigationLinks as $link)
                                <x-navigation-link :href="$link['href']" :active="$link['active']">
                                    {{ $link['label'] }}
                                </x-navigation-link>
                            @endforeach
                        </nav>

                    </div>
                </div>
            </aside>

            <div class="space-y-5 sm:ml-[300px]">
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
