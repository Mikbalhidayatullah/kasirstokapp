@props([
    'title' => 'Dashboard',
    'heading' => null,
    'description' => null,
])

@php
    $user = auth()->user();
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
        <div class="grid gap-5 lg:grid-cols-[280px_minmax(0,1fr)]">
            <aside class="surface-card p-5 lg:sticky lg:top-5 lg:h-[calc(100vh-2.5rem)]">
                <div class="flex h-full flex-col gap-5">
                    <x-app-logo />

                    <div class="surface-dark p-4">
                        <p class="text-xs uppercase tracking-[0.28em] text-white/60">Akun aktif</p>
                        <h2 class="mt-3 text-xl font-bold">{{ $user->name }}</h2>
                        <p class="mt-1 text-sm text-white/70">{{ $user->role->label() }}</p>
                    </div>

                    <nav class="grid gap-2">
                        <x-navigation-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            Ringkasan
                        </x-navigation-link>

                        @if ($user->canManageStock())
                            <x-navigation-link :href="route('stock.categories.index')" :active="request()->routeIs('stock.categories.*')">
                                Kategori
                            </x-navigation-link>
                            <x-navigation-link :href="route('stock.products.index')" :active="request()->routeIs('stock.products.*')">
                                Produk
                            </x-navigation-link>
                            <x-navigation-link :href="route('stock.movements.index')" :active="request()->routeIs('stock.movements.*')">
                                Mutasi Stok
                            </x-navigation-link>
                        @endif

                        @if ($user->canHandleCashier())
                            <x-navigation-link :href="route('cashier.index')" :active="request()->routeIs('cashier.*')">
                                Kasir
                            </x-navigation-link>
                            <x-navigation-link :href="route('sales.index')" :active="request()->routeIs('sales.*')">
                                Riwayat Penjualan
                            </x-navigation-link>
                        @endif
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
